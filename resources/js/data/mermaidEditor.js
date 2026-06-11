import { withUrlState } from '../lib/urlState';
import LZString from 'lz-string';

// Cap the compressed source length so a runaway diagram can't blow the URL
// out. Roughly an 8k-character query string, well inside what browsers and
// link unfurlers tolerate.
const MAX_URL_CODE = 8000;

const DEFAULT_DIAGRAM = `flowchart TD
    A[Start] --> B{Is it working?}
    B -- Yes --> C[Ship it]
    B -- No --> D[Debug]
    D --> B`;

// 'auto' follows the site's light/dark mode; the rest map straight to Mermaid
// built-in themes.
const THEMES = ['auto', 'default', 'neutral', 'dark', 'forest', 'base'];

// URL-safe base64 of a UTF-8 string, matching the encoding mermaid.live's
// editor uses for its `#base64:` state payload.
function toUrlSafeBase64(str) {
    const bytes = new TextEncoder().encode(str);
    let binary = '';
    for (let i = 0; i < bytes.length; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

const TEMPLATES = [
    {
        label: 'Flowchart',
        value: DEFAULT_DIAGRAM,
    },
    {
        label: 'Sequence diagram',
        value: `sequenceDiagram
    participant Browser
    participant Server
    Browser->>Server: GET /diagram
    Server-->>Browser: 200 OK (SVG)
    Browser->>Browser: Render locally`,
    },
    {
        label: 'Class diagram',
        value: `classDiagram
    class Animal {
        +String name
        +int age
        +makeSound()
    }
    class Dog {
        +fetch()
    }
    Animal <|-- Dog`,
    },
    {
        label: 'State diagram',
        value: `stateDiagram-v2
    [*] --> Idle
    Idle --> Loading: fetch
    Loading --> Ready: success
    Loading --> Error: failure
    Error --> Idle: retry
    Ready --> [*]`,
    },
    {
        label: 'Entity relationship',
        value: `erDiagram
    CUSTOMER ||--o{ ORDER : places
    ORDER ||--|{ LINE_ITEM : contains
    CUSTOMER {
        string name
        string email
    }
    ORDER {
        int id
        date placed_at
    }`,
    },
    {
        label: 'Gantt chart',
        value: `gantt
    title Project plan
    dateFormat YYYY-MM-DD
    section Design
    Wireframes      :a1, 2026-01-01, 7d
    Visual design   :after a1, 5d
    section Build
    Frontend        :2026-01-13, 10d
    Backend         :2026-01-13, 12d`,
    },
    {
        label: 'Pie chart',
        value: `pie title Time spent
    "Coding" : 45
    "Meetings" : 25
    "Code review" : 20
    "Coffee" : 10`,
    },
    {
        label: 'Mindmap',
        value: `mindmap
  root((Savvy Goose))
    Text
      Character counter
      Case converter
    Data
      JSON formatter
      Hash generator
    Diagrams
      Mermaid editor`,
    },
    {
        label: 'Git graph',
        value: `gitGraph
    commit
    branch develop
    checkout develop
    commit
    commit
    checkout main
    merge develop
    commit`,
    },
];

const schema = {
    code: {
        type: 'string',
        alias: 'c',
        // Matches the factory seed so an untouched default isn't stamped into
        // the URL on first load. updateUrl skips a value equal to its default.
        default: DEFAULT_DIAGRAM,
        parse: (raw) => {
            try {
                const out = LZString.decompressFromEncodedURIComponent(raw);
                return out || undefined;
            } catch {
                return undefined;
            }
        },
        serialize: (value) => {
            if (!value) return { skip: true };
            const packed = LZString.compressToEncodedURIComponent(value);
            if (packed.length > MAX_URL_CODE) return { skip: true, tooLong: true };
            return { value: packed };
        },
    },
    theme: { type: 'enum', values: THEMES, default: 'auto', alias: 't' },
};

export default withUrlState(schema, () => ({
    code: DEFAULT_DIAGRAM,
    error: null,
    rendering: false,
    hasDiagram: false,
    templates: TEMPLATES,
    // The Mermaid theme actually in use once 'auto' is resolved against the
    // site's mode. Drives the preview background so the diagram stays legible.
    resolvedTheme: 'default',
    isFullscreen: false,
    showEditor: true,

    // Lazy-loaded libraries and instance handles. Underscored so withUrlState
    // doesn't try to wire URL watchers onto them.
    _mermaid: null,
    _panZoomLib: null,
    _panZoom: null,
    _renderId: 0,
    _debounce: null,
    _themeObserver: null,
    _lastSvg: '',
    _onFullscreen: null,

    async init() {
        // mermaid is large, so it (and svg-pan-zoom) are split into their own
        // chunks and only fetched when this tool actually loads, keeping the
        // site-wide app bundle small.
        const [mermaidMod, panZoomMod] = await Promise.all([
            import('mermaid'),
            import('svg-pan-zoom'),
        ]);
        this._mermaid = mermaidMod.default;
        this._panZoomLib = panZoomMod.default || panZoomMod;

        this.configureMermaid();

        this.$watch('code', () => this.scheduleRender());
        this.$watch('theme', () => {
            // Debounced like the code watcher so a code edit followed by a
            // theme switch collapses into one render instead of racing.
            this.configureMermaid();
            this.scheduleRender();
        });

        // When the theme is left on 'auto', re-render if the site toggles
        // between light and dark. The class lives on <html>, which survives
        // wire:navigate, so the observer is torn down in destroy().
        this._themeObserver = new MutationObserver(() => {
            if (this.theme === 'auto' && this.$el?.isConnected) {
                this.configureMermaid();
                this.scheduleRender();
            }
        });
        this._themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });

        // Track entering/exiting fullscreen (including via the Esc key) and
        // re-fit the diagram once the preview has resized to fill the screen.
        this._onFullscreen = () => {
            this.isFullscreen = document.fullscreenElement === this.$refs.workspace;
            this.refitPanZoom();
        };
        document.addEventListener('fullscreenchange', this._onFullscreen);

        this.renderDiagram();
    },

    destroy() {
        clearTimeout(this._debounce);
        this.teardownPanZoom();
        this._themeObserver?.disconnect();
        if (this._onFullscreen) {
            document.removeEventListener('fullscreenchange', this._onFullscreen);
        }
    },

    toggleFullscreen() {
        if (document.fullscreenElement) {
            document.exitFullscreen?.();
        } else {
            this.$refs.workspace?.requestFullscreen?.().catch(() => {});
        }
    },

    toggleEditor() {
        this.showEditor = !this.showEditor;
        // The preview width just changed; refit so the diagram stays framed.
        this.refitPanZoom();
    },

    refitPanZoom() {
        this.$nextTick(() => {
            if (!this._panZoom) return;
            this._panZoom.resize();
            this._panZoom.fit();
            this._panZoom.center();
        });
    },

    isSiteDark() {
        return document.documentElement.classList.contains('dark');
    },

    configureMermaid() {
        // securityLevel 'strict' sanitises arbitrary pasted input; htmlLabels
        // false keeps labels as native SVG <text> so PNG export rasterises
        // cleanly (foreignObject labels blank the canvas in several browsers).
        const resolved = this.theme === 'auto'
            ? (this.isSiteDark() ? 'dark' : 'default')
            : this.theme;
        this.resolvedTheme = resolved;
        this._mermaid.initialize({
            startOnLoad: false,
            securityLevel: 'strict',
            theme: resolved,
            // Top-level htmlLabels:false is required as well as the flowchart
            // one: edge labels honour the top-level flag, and any remaining
            // <foreignObject> taints the canvas during PNG export.
            htmlLabels: false,
            flowchart: { htmlLabels: false, useMaxWidth: true },
            er: { useMaxWidth: true },
        });
    },

    scheduleRender() {
        clearTimeout(this._debounce);
        this._debounce = setTimeout(() => this.renderDiagram(), 300);
    },

    async renderDiagram() {
        if (!this._mermaid) return;

        const source = (this.code || '').trim();
        if (!source) {
            this.error = null;
            this.hasDiagram = false;
            this._lastSvg = '';
            this.teardownPanZoom();
            if (this.$refs.preview) this.$refs.preview.innerHTML = '';
            return;
        }

        this.rendering = true;
        const myId = ++this._renderId;
        const id = `mermaid-render-${myId}`;

        try {
            const { svg, bindFunctions } = await this._mermaid.render(id, source);
            // A newer render started while this one was awaiting; drop this
            // result so it can't overwrite the fresher diagram.
            if (myId !== this._renderId) return;

            this.error = null;
            // Keep mermaid's pristine output for exports. svg-pan-zoom mutates
            // the live SVG (strips the viewBox, wraps content in a transformed
            // group), so exporting the live node would bake in the pan/zoom.
            this._lastSvg = svg;
            this.teardownPanZoom();

            this.$refs.preview.innerHTML = svg;
            const svgEl = this.$refs.preview.querySelector('svg');
            if (svgEl) {
                svgEl.removeAttribute('height');
                svgEl.style.maxWidth = '100%';
                svgEl.style.width = '100%';
                svgEl.style.height = '100%';
                bindFunctions?.(this.$refs.preview);
                this.hasDiagram = true;
                this.$nextTick(() => this.setupPanZoom(svgEl));
            } else {
                this.hasDiagram = false;
            }
        } catch (e) {
            // mermaid can leave an orphan node behind on a failed parse; clean
            // it up so the page doesn't accumulate stray diagrams.
            document.getElementById(id)?.remove();
            document.getElementById('d' + id)?.remove();
            // Only let the latest render report its error, so a stale failure
            // can't clobber a newer successful render.
            if (myId === this._renderId) this.error = e && e.message ? e.message : String(e);
        } finally {
            if (myId === this._renderId) this.rendering = false;
        }
    },

    setupPanZoom(svgEl) {
        try {
            this._panZoom = this._panZoomLib(svgEl, {
                zoomEnabled: true,
                panEnabled: true,
                controlIconsEnabled: false,
                fit: true,
                center: true,
                minZoom: 0.2,
                maxZoom: 20,
            });
        } catch {
            this._panZoom = null;
        }
    },

    teardownPanZoom() {
        if (this._panZoom) {
            try {
                this._panZoom.destroy();
            } catch {
                /* already gone */
            }
            this._panZoom = null;
        }
    },

    zoomIn() {
        this._panZoom?.zoomBy(1.2);
    },

    zoomOut() {
        this._panZoom?.zoomBy(0.8);
    },

    resetView() {
        if (!this._panZoom) return;
        this._panZoom.resetZoom();
        this._panZoom.center();
        this._panZoom.fit();
        this._panZoom.center();
    },

    loadTemplate(value) {
        if (value) this.code = value;
    },

    openInMermaidLive() {
        const source = (this.code || '').trim();
        if (!source) return;
        // mermaid.live deserialises this state shape: `code` plus a `mermaid`
        // config (itself a JSON string). 'auto' has no meaning there, so pass
        // the resolved theme.
        const theme = this.resolvedTheme === 'auto' ? 'default' : this.resolvedTheme;
        const state = {
            code: this.code,
            mermaid: JSON.stringify({ theme }, null, 2),
            autoSync: true,
            updateDiagram: true,
        };
        const url = `https://mermaid.live/edit#base64:${toUrlSafeBase64(JSON.stringify(state))}`;
        window.open(url, '_blank', 'noopener');
    },

    clear() {
        this.code = '';
    },

    // Exports use the pristine SVG mermaid returned, not the live preview node,
    // which svg-pan-zoom has mutated (viewBox stripped, transform baked in).
    currentSvg() {
        return this._lastSvg || '';
    },

    downloadSvg() {
        const svg = this.currentSvg();
        if (!svg) return;
        const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
        this.$download(blob, 'diagram.svg');
    },

    async downloadPng() {
        if (!this._lastSvg) return;

        const svgEl = new DOMParser()
            .parseFromString(this._lastSvg, 'image/svg+xml')
            .documentElement;
        const clone = svgEl.cloneNode(true);

        let width;
        let height;
        const vb = svgEl.viewBox?.baseVal;
        if (vb && vb.width && vb.height) {
            width = vb.width;
            height = vb.height;
        } else {
            const rect = this.$refs.preview?.querySelector('svg')?.getBoundingClientRect();
            width = rect?.width || 800;
            height = rect?.height || 600;
        }

        // Pin explicit pixel dimensions on the clone; the live SVG uses 100%.
        clone.setAttribute('width', width);
        clone.setAttribute('height', height);
        clone.style.width = '';
        clone.style.height = '';
        clone.style.maxWidth = '';

        const xml = new XMLSerializer().serializeToString(clone);
        const svgBlob = new Blob(['<?xml version="1.0" encoding="UTF-8"?>\n' + xml], {
            type: 'image/svg+xml;charset=utf-8',
        });
        const url = URL.createObjectURL(svgBlob);
        const scale = 2;

        try {
            const img = new Image();
            await new Promise((resolve, reject) => {
                img.onload = resolve;
                img.onerror = () => reject(new Error('render failed'));
                img.src = url;
            });

            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(width * scale));
            canvas.height = Math.max(1, Math.round(height * scale));
            const ctx = canvas.getContext('2d');
            // The dark theme draws light text; everything else expects a light
            // backdrop, so fill white to keep exported PNGs legible.
            if (this.resolvedTheme !== 'dark') {
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }
            ctx.setTransform(scale, 0, 0, scale, 0, 0);
            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob((blob) => {
                if (blob) this.$download(blob, 'diagram.png');
            }, 'image/png');
        } catch (e) {
            this.error = 'Could not export PNG: ' + (e && e.message ? e.message : e);
        } finally {
            URL.revokeObjectURL(url);
        }
    },
}));
