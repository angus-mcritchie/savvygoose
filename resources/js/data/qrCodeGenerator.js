import QRCode from 'qrcode';
import { withUrlState } from '../lib/urlState';
import {
    EYE_SHAPE_KEYS,
    MODULE_SHAPE_KEYS,
    QR_PRESETS,
    buildQrGeometry,
    clearAreaFor,
    qrToCanvas,
    qrToSvg,
} from '../lib/qrRenderer';

const DEFAULTS = {
    size: 256,
    ec: 'M',
    fg: '#000000',
    bg: '#ffffff',
    margin: 4,
    mod: 'square',
    eye: 'square',
    logoSize: 20,
    logoClearance: true,
};

const EC_LEVELS = ['L', 'M', 'Q', 'H'];
const MAX_LOGO_BYTES = 2 * 1024 * 1024;
const MAX_PREVIEW_PIXELS = 4096;
// Below roughly two device pixels per module a raster export stops decoding,
// so exports grow past the requested size rather than ship something unusable.
const MIN_EXPORT_MODULE_PX = 2;
// The finder patterns fill the outer 7 modules of three corners; a centred
// logo must stay inside what is left.
const FINDER_MODULES = 7;
const LOGO_PAD_RATIO = 0.12;
// Widest logo, as a percentage of the code, that still decoded across symbol
// versions 1 to 40 at each error-correction level. Measured against zbar; the
// covered modules have to be rebuilt from redundancy, and level L has almost
// none to spare.
const LOGO_LIMITS = { L: 8, M: 16, Q: 16, H: 22 };
const RENDER_DEBOUNCE_MS = 90;

// Heroicons solid 24x24, recolored at use-time via {color} placeholder.
const PRESET_ICONS = {
    url: {
        label: 'URL',
        path: '<path d="M19.902 4.098a3.75 3.75 0 0 0-5.304 0l-4.5 4.5a3.75 3.75 0 0 0 1.035 6.037.75.75 0 0 1-.646 1.353 5.25 5.25 0 0 1-1.449-8.45l4.5-4.5a5.25 5.25 0 1 1 7.424 7.424l-1.757 1.757a.75.75 0 1 1-1.06-1.06l1.757-1.757a3.75 3.75 0 0 0 0-5.304Zm-7.389 4.267a.75.75 0 0 1 1-.353 5.25 5.25 0 0 1 1.449 8.45l-4.5 4.5a5.25 5.25 0 1 1-7.424-7.424l1.757-1.757a.75.75 0 1 1 1.06 1.06l-1.757 1.757a3.75 3.75 0 1 0 5.304 5.304l4.5-4.5a3.75 3.75 0 0 0-1.035-6.037.75.75 0 0 1-.354-1Z"/>',
    },
    wifi: {
        label: 'Wi-Fi',
        path: '<path d="M1.371 8.143c5.858-5.857 15.356-5.857 21.213 0a.75.75 0 0 1 0 1.061l-.53.53a.75.75 0 0 1-1.06 0c-4.98-4.979-13.053-4.979-18.032 0a.75.75 0 0 1-1.06 0l-.53-.53a.75.75 0 0 1 0-1.06ZM4.555 11.328c4.098-4.098 10.749-4.098 14.847 0a.75.75 0 0 1 0 1.06l-.53.53a.75.75 0 0 1-1.06 0 8.25 8.25 0 0 0-11.667 0 .75.75 0 0 1-1.06 0l-.53-.53a.75.75 0 0 1 0-1.06ZM7.738 14.512a6 6 0 0 1 8.484 0 .75.75 0 0 1 0 1.06l-.53.53a.75.75 0 0 1-1.061 0 3.75 3.75 0 0 0-5.303 0 .75.75 0 0 1-1.06 0l-.53-.53a.75.75 0 0 1 0-1.06ZM12 17.625a1.5 1.5 0 0 0-1.06.44.75.75 0 0 1-1.061-1.06 3 3 0 0 1 4.242 0 .75.75 0 0 1-1.06 1.06A1.5 1.5 0 0 0 12 17.625Z"/>',
    },
    contact: {
        label: 'Contact',
        path: '<path d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z"/>',
    },
    email: {
        label: 'Email',
        path: '<path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z"/><path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z"/>',
    },
    phone: {
        label: 'Phone',
        path: '<path d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z"/>',
    },
    location: {
        label: 'Location',
        path: '<path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>',
    },
};

// Rendered once and shared by every instance: a fixed sample symbol drawn in
// each preset, used as the swatch art in the theme picker.
let thumbnailCache = null;

function presetThumbnails() {
    if (thumbnailCache) return thumbnailCache;

    const sample = QRCode.create('savvygoose.com', { errorCorrectionLevel: 'M' });
    thumbnailCache = {};

    for (const [key, preset] of Object.entries(QR_PRESETS)) {
        const geo = buildQrGeometry(sample.modules.data, sample.modules.size, { ...preset, margin: 1 });
        thumbnailCache[key] = qrToSvg(geo, { fg: 'currentColor', bg: null });
    }

    return thumbnailCache;
}

const schema = {
    text: { type: 'string' },
    mod: { type: 'enum', values: MODULE_SHAPE_KEYS, default: DEFAULTS.mod },
    eye: { type: 'enum', values: EYE_SHAPE_KEYS, default: DEFAULTS.eye },
    size: { type: 'integer', default: DEFAULTS.size, min: 64, max: 2048 },
    ec: {
        type: 'enum',
        values: EC_LEVELS,
        default: DEFAULTS.ec,
        parse: (raw) => {
            const v = raw.toUpperCase();
            return EC_LEVELS.includes(v) ? v : undefined;
        },
    },
    fg: { type: 'color', default: DEFAULTS.fg },
    bg: { type: 'color', default: DEFAULTS.bg },
    margin: { type: 'integer', default: DEFAULTS.margin, min: 0, max: 16 },
};

export default withUrlState(schema, () => ({
    logo: null,
    logoSize: DEFAULTS.logoSize,
    logoClearance: DEFAULTS.logoClearance,
    logoError: null,
    activePreset: null,
    presets: PRESET_ICONS,
    themes: QR_PRESETS,
    themeThumbs: presetThumbnails(),
    renderToken: 0,
    renderTimer: null,
    contrastWarning: false,
    capacityError: '',
    renderError: '',
    downloadError: '',
    logoClamped: false,
    exportSize: 0,

    init() {
        [
            'text', 'size', 'ec', 'fg', 'bg', 'margin', 'mod', 'eye',
            'logo', 'logoSize', 'logoClearance',
        ].forEach((prop) => {
            this.$watch(prop, () => this.scheduleRender());
        });

        this.$watch('fg', () => {
            if (this.activePreset) this.applyPreset(this.activePreset);
        });

        // Dropping to a weaker error-correction level leaves less redundancy
        // to rebuild whatever the logo covers, so the logo has to give way.
        // Applied whether or not a logo is set, to keep the field's value and
        // its max in step.
        this.$watch('ec', () => {
            if (this.logoSize > this.maxLogoSize) this.logoSize = this.maxLogoSize;
        });

        this.$nextTick(() => this.render());
    },

    // wire:navigate tears components down mid-flight; a queued render would
    // otherwise fire against a detached canvas.
    destroy() {
        clearTimeout(this.renderTimer);
    },

    // Building path data for a dense symbol is not free, so typing coalesces
    // into one render instead of one per keystroke.
    scheduleRender() {
        clearTimeout(this.renderTimer);
        this.renderTimer = setTimeout(() => this.render(), RENDER_DEBOUNCE_MS);
    },

    applyTheme(key) {
        const preset = QR_PRESETS[key];
        if (!preset) return;

        this.mod = preset.module;
        this.eye = preset.eye;
    },

    // How big a logo the current error-correction level can carry.
    get maxLogoSize() {
        return LOGO_LIMITS[this.ec] ?? LOGO_LIMITS.M;
    },

    // Which named preset the current shape pair adds up to, if any.
    get activeTheme() {
        const match = Object.entries(QR_PRESETS)
            .find(([, preset]) => preset.module === this.mod && preset.eye === this.eye);

        return match ? match[0] : null;
    },

    onLogoSelected(event) {
        const file = event.target.files?.[0];
        this.logoError = null;

        if (!file) return;

        if (!file.type.startsWith('image/')) {
            this.logoError = 'That file is not an image.';
            event.target.value = '';
            return;
        }

        if (file.size > MAX_LOGO_BYTES) {
            this.logoError = 'Image is larger than 2 MB.';
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            this.activePreset = null;
            this.logo = reader.result;
        };
        reader.readAsDataURL(file);
    },

    applyPreset(key) {
        const preset = PRESET_ICONS[key];
        if (!preset) return;

        const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="${this.fg}">${preset.path}</svg>`;
        this.activePreset = key;
        this.logo = `data:image/svg+xml;base64,${btoa(svg)}`;
        if (this.$refs.logoInput) this.$refs.logoInput.value = '';
        this.logoError = null;
    },

    clearLogo() {
        this.logo = null;
        this.logoError = null;
        this.activePreset = null;
        if (this.$refs.logoInput) this.$refs.logoInput.value = '';
    },

    loadLogoImage() {
        if (!this.logo) return Promise.resolve(null);
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = this.logo;
        });
    },

    // Where the logo sits, in module units relative to the symbol itself.
    //
    // The size is a share of the symbol rather than of the padded canvas, so
    // widening the quiet zone no longer grows the logo. Two ceilings apply: the
    // error-correction budget, and the square the three finder patterns leave
    // free. Redundancy can rebuild covered data modules; nothing rebuilds a
    // finder.
    logoBox(img, count) {
        // An image with no intrinsic size would make every measurement below
        // NaN, so bail rather than punch a hole in the code for nothing.
        if (!img?.width || !img?.height) return null;

        const requested = Math.max(5, Math.min(40, parseInt(this.logoSize, 10) || DEFAULTS.logoSize));
        const padScale = this.logoClearance ? 1 + 2 * LOGO_PAD_RATIO : 1;
        const safe = Math.max(0, count - 2 * FINDER_MODULES) / padScale;
        const allowed = Math.min(requested, this.maxLogoSize);
        const wanted = count * requested / 100;
        const target = Math.min(count * allowed / 100, safe);

        if (target <= 0) return null;

        const scale = Math.min(target / img.width, target / img.height);
        const w = img.width * scale;
        const h = img.height * scale;

        return {
            x: (count - w) / 2,
            y: (count - h) / 2,
            w,
            h,
            pad: target * LOGO_PAD_RATIO,
            clamped: target < wanted - 1e-9,
        };
    },

    // Throws when the text exceeds the capacity of the chosen error-correction
    // level. Callers treat only this as the capacity error.
    symbol() {
        return QRCode.create(this.text, { errorCorrectionLevel: this.ec });
    },

    // Resolving the logo first lets the renderer drop the modules underneath
    // it, which is what stops a knockout from slicing modules in half.
    async compose(qr) {
        const count = qr.modules.size;
        const img = await this.loadLogoImage();
        const box = img ? this.logoBox(img, count) : null;

        const geo = buildQrGeometry(qr.modules.data, count, {
            module: this.mod,
            eye: this.eye,
            margin: parseInt(this.margin, 10) || 0,
            clear: box && this.logoClearance ? clearAreaFor(box, box.pad) : null,
        });

        return { geo, img, box };
    },

    drawLogoOnCanvas(canvas, img, geo, box) {
        if (!img || !box) return;

        // Module units to canvas pixels, including the quiet zone offset.
        const s = canvas.width / geo.total;
        const ctx = canvas.getContext('2d');

        ctx.drawImage(img, (geo.margin + box.x) * s, (geo.margin + box.y) * s, box.w * s, box.h * s);
    },

    displaySize() {
        return parseInt(this.size, 10) || DEFAULTS.size;
    },

    // Exports honour the requested size, except that a dense symbol is never
    // squeezed below the point where it stops decoding.
    exportPixels(geo) {
        return Math.max(this.displaySize(), geo.total * MIN_EXPORT_MODULE_PX);
    },

    paintOptions(width) {
        return { width, fg: this.fg, bg: this.bg };
    },

    // The preview is laid out at `size` CSS pixels, so on a HiDPI screen a
    // canvas of exactly that many device pixels gets upscaled and goes soft.
    // Render at device resolution instead, rounded so every module covers a
    // whole number of pixels and the edges stay hard.
    previewPixels(geo) {
        const dpr = Math.min(window.devicePixelRatio || 1, 3);
        const ideal = Math.round((this.displaySize() * dpr) / geo.total);
        const ceiling = Math.floor(MAX_PREVIEW_PIXELS / geo.total);

        return Math.max(1, Math.min(ideal, ceiling)) * geo.total;
    },

    blankCanvas(canvas) {
        canvas.width = this.displaySize();
        canvas.height = canvas.width;
        canvas.style.width = `${this.displaySize()}px`;
        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
    },

    async render() {
        const canvas = this.$refs.canvas;
        if (!canvas) return;

        const token = ++this.renderToken;
        this.downloadError = '';

        if (!this.text) {
            this.blankCanvas(canvas);
            this.contrastWarning = false;
            this.capacityError = '';
            this.renderError = '';
            this.logoClamped = false;
            this.exportSize = 0;
            return;
        }

        let qr;
        try {
            qr = this.symbol();
        } catch {
            this.capacityError = 'This is too much data for a QR code at this error-correction level. Shorten the text, or lower the error correction.';
            this.renderError = '';
            this.contrastWarning = false;
            this.logoClamped = false;
            this.exportSize = 0;
            this.blankCanvas(canvas);
            return;
        }

        this.capacityError = '';

        try {
            const { geo, img, box } = await this.compose(qr);
            if (token !== this.renderToken) return;

            // Compose offscreen so the visible canvas never flashes a
            // half-drawn symbol.
            const off = document.createElement('canvas');
            qrToCanvas(off, geo, this.paintOptions(this.previewPixels(geo)));
            this.drawLogoOnCanvas(off, img, geo, box);

            canvas.width = off.width;
            canvas.height = off.height;
            canvas.style.width = `${this.displaySize()}px`;
            canvas.getContext('2d').drawImage(off, 0, 0);

            this.contrastWarning = this.computeContrast(this.fg, this.bg) < 3;
            this.logoClamped = box?.clamped ?? false;
            this.exportSize = this.exportPixels(geo);
            this.renderError = '';
        } catch (err) {
            if (token !== this.renderToken) return;
            console.error('QR render failed:', err);
            this.renderError = 'Your browser could not draw this QR code. The SVG download should still work.';
            this.contrastWarning = false;
            this.blankCanvas(canvas);
        }
    },

    async downloadPng() {
        if (!this.text || this.capacityError || this.renderError) return;

        this.downloadError = '';
        try {
            const { geo, img, box } = await this.compose(this.symbol());
            const canvas = qrToCanvas(document.createElement('canvas'), geo, this.paintOptions(this.exportPixels(geo)));
            this.drawLogoOnCanvas(canvas, img, geo, box);

            canvas.toBlob((blob) => {
                if (!blob) {
                    this.downloadError = 'Your browser could not produce a PNG. Try the SVG download.';
                    return;
                }
                this.triggerDownload(blob, 'qr-code.png');
            });
        } catch (err) {
            console.error('QR PNG download failed:', err);
            this.downloadError = 'Your browser could not produce a PNG. Try the SVG download.';
        }
    },

    async downloadSvg() {
        if (!this.text || this.capacityError) return;

        this.downloadError = '';
        try {
            const { geo, box } = await this.compose(this.symbol());
            const overlay = box
                ? `<image href="${this.logo}" x="${geo.margin + box.x}" y="${geo.margin + box.y}" width="${box.w}" height="${box.h}" preserveAspectRatio="xMidYMid meet"/>`
                : '';

            const svg = qrToSvg(geo, { ...this.paintOptions(this.displaySize()), overlay });
            this.triggerDownload(new Blob([svg], { type: 'image/svg+xml' }), 'qr-code.svg');
        } catch (err) {
            console.error('QR SVG download failed:', err);
            this.downloadError = 'Your browser could not produce an SVG.';
        }
    },

    triggerDownload(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    },

    computeContrast(fg, bg) {
        const lum = (hex) => {
            const c = hex.replace('#', '');
            const [r, g, b] = [0, 2, 4].map((i) => parseInt(c.slice(i, i + 2), 16) / 255);
            const adj = (v) => (v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4));
            return 0.2126 * adj(r) + 0.7152 * adj(g) + 0.0722 * adj(b);
        };
        const l1 = lum(fg);
        const l2 = lum(bg);
        const [light, dark] = l1 > l2 ? [l1, l2] : [l2, l1];
        return (light + 0.05) / (dark + 0.05);
    },
}));
