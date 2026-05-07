const DEFAULTS = {
    width: 256,
    height: 256,
    fit: 'contain',
    format: 'image/png',
    quality: 92,
    bg: '#ffffff',
    locked: false,
};

const SIZE_PRESETS = [
    { label: '256²', w: 256, h: 256 },
    { label: '512²', w: 512, h: 512 },
    { label: '1024²', w: 1024, h: 1024 },
    { label: '1200×630', w: 1200, h: 630 },
    { label: '1920×1080', w: 1920, h: 1080 },
    { label: '1280×720', w: 1280, h: 720 },
];

const RATIO_PRESETS = [
    { label: '1:1', w: 1, h: 1 },
    { label: '4:3', w: 4, h: 3 },
    { label: '3:2', w: 3, h: 2 },
    { label: '16:9', w: 16, h: 9 },
    { label: '21:9', w: 21, h: 9 },
    { label: '3:4', w: 3, h: 4 },
    { label: '9:16', w: 9, h: 16 },
];

const FORMATS = {
    'image/png': { label: 'PNG', ext: 'png', supportsQuality: false },
    'image/jpeg': { label: 'JPEG', ext: 'jpg', supportsQuality: true },
    'image/webp': { label: 'WebP', ext: 'webp', supportsQuality: true },
};

const MAX_FILE_BYTES = 20 * 1024 * 1024;

const computeRect = (srcW, srcH, dstW, dstH, fit) => {
    if (fit === 'stretch') {
        return { x: 0, y: 0, w: dstW, h: dstH };
    }
    const srcRatio = srcW / srcH;
    const dstRatio = dstW / dstH;
    if (fit === 'cover') {
        if (srcRatio > dstRatio) {
            const h = dstH;
            const w = h * srcRatio;
            return { x: (dstW - w) / 2, y: 0, w, h };
        }
        const w = dstW;
        const h = w / srcRatio;
        return { x: 0, y: (dstH - h) / 2, w, h };
    }
    // contain
    if (srcRatio > dstRatio) {
        const w = dstW;
        const h = w / srcRatio;
        return { x: 0, y: (dstH - h) / 2, w, h };
    }
    const h = dstH;
    const w = h * srcRatio;
    return { x: (dstW - w) / 2, y: 0, w, h };
};

export default () => ({
    width: DEFAULTS.width,
    height: DEFAULTS.height,
    fit: DEFAULTS.fit,
    format: DEFAULTS.format,
    quality: DEFAULTS.quality,
    bg: DEFAULTS.bg,
    locked: DEFAULTS.locked,
    source: null,
    sourceName: '',
    sourceWidth: 0,
    sourceHeight: 0,
    sourceBytes: 0,
    sourceRatio: 1,
    error: null,
    dragging: false,
    previewUrl: '',
    previewBytes: 0,
    formats: FORMATS,
    sizePresets: SIZE_PRESETS,
    ratioPresets: RATIO_PRESETS,
    url: window.location.href,
    _previewToken: 0,
    _ratioGuard: false,

    init() {
        this.initFromUrl();

        ['width', 'height', 'fit', 'format', 'quality', 'bg'].forEach((prop) => {
            this.$watch(prop, () => {
                this.updateUrl();
                this.renderPreview();
            });
        });

        this.$watch('width', (val) => {
            if (!this.locked || this._ratioGuard || !this.sourceRatio) return;
            this._ratioGuard = true;
            this.height = Math.max(1, Math.round(val / this.sourceRatio));
            this.$nextTick(() => (this._ratioGuard = false));
        });

        this.$watch('height', (val) => {
            if (!this.locked || this._ratioGuard || !this.sourceRatio) return;
            this._ratioGuard = true;
            this.width = Math.max(1, Math.round(val * this.sourceRatio));
            this.$nextTick(() => (this._ratioGuard = false));
        });

        this.updateUrl();
    },

    get supportsQuality() {
        return FORMATS[this.format]?.supportsQuality ?? false;
    },

    get supportsTransparency() {
        return this.format === 'image/png' || this.format === 'image/webp';
    },

    get baseName() {
        if (!this.sourceName) return 'image';
        return this.sourceName.replace(/\.[^.]+$/, '') || 'image';
    },

    onDrop(event) {
        this.dragging = false;
        const file = event.dataTransfer?.files?.[0];
        if (file) this.loadFile(file);
    },

    onPick(event) {
        const file = event.target.files?.[0];
        if (file) this.loadFile(file);
        event.target.value = '';
    },

    onPaste(event) {
        const item = Array.from(event.clipboardData?.items ?? [])
            .find((i) => i.type.startsWith('image/'));
        if (!item) return;
        const file = item.getAsFile();
        if (file) this.loadFile(file);
    },

    async loadFile(file) {
        this.error = null;

        if (!file.type.startsWith('image/')) {
            this.error = 'That file is not an image.';
            return;
        }

        if (file.size > MAX_FILE_BYTES) {
            this.error = 'Image is larger than 20 MB.';
            return;
        }

        try {
            const url = URL.createObjectURL(file);
            const img = await this.loadImage(url);
            URL.revokeObjectURL(url);

            this.source = img;
            this.sourceName = file.name;
            this.sourceBytes = file.size;
            this.sourceWidth = img.naturalWidth;
            this.sourceHeight = img.naturalHeight;
            this.sourceRatio = img.naturalWidth / img.naturalHeight;

            this.renderPreview();
        } catch {
            this.error = 'Could not decode that image.';
        }
    },

    loadImage(src) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = src;
        });
    },

    clear() {
        this.source = null;
        this.sourceName = '';
        this.sourceBytes = 0;
        this.sourceWidth = 0;
        this.sourceHeight = 0;
        this.sourceRatio = 1;
        this.previewUrl = '';
        this.previewBytes = 0;
        this.error = null;
    },

    drawTo(canvas, w, h) {
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';

        // Fill background whenever the image won't fully cover the canvas
        // (contain may letterbox) or the format can't carry transparency.
        const willCoverCanvas = this.fit === 'cover' || this.fit === 'stretch';
        if (!this.supportsTransparency || !willCoverCanvas) {
            ctx.fillStyle = this.bg;
            ctx.fillRect(0, 0, w, h);
        }

        const rect = computeRect(this.sourceWidth, this.sourceHeight, w, h, this.fit);
        ctx.drawImage(this.source, rect.x, rect.y, rect.w, rect.h);
    },

    async renderPreview() {
        if (!this.source) {
            this.previewUrl = '';
            this.previewBytes = 0;
            return;
        }

        const w = Math.max(1, Math.min(4096, parseInt(this.width, 10) || 1));
        const h = Math.max(1, Math.min(4096, parseInt(this.height, 10) || 1));
        const token = ++this._previewToken;

        const canvas = document.createElement('canvas');
        this.drawTo(canvas, w, h);

        const blob = await this.canvasBlob(canvas);
        if (token !== this._previewToken) return;

        if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
        this.previewUrl = URL.createObjectURL(blob);
        this.previewBytes = blob.size;
    },

    canvasBlob(canvas) {
        return new Promise((resolve) => {
            const args = [resolve, this.format];
            if (this.supportsQuality) args.push(this.quality / 100);
            canvas.toBlob(...args);
        });
    },

    async download() {
        if (!this.source) return;
        const w = Math.max(1, parseInt(this.width, 10));
        const h = Math.max(1, parseInt(this.height, 10));
        const canvas = document.createElement('canvas');
        this.drawTo(canvas, w, h);
        const blob = await this.canvasBlob(canvas);
        const ext = FORMATS[this.format].ext;
        this.triggerDownload(blob, `${this.baseName}-${w}x${h}.${ext}`);
    },

    triggerDownload(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    },

    toggleLock() {
        this.locked = !this.locked;
        if (this.locked && this.source) {
            this._ratioGuard = true;
            this.height = Math.max(1, Math.round(this.width / this.sourceRatio));
            this.$nextTick(() => (this._ratioGuard = false));
        }
    },

    matchSource() {
        if (!this.source) return;
        this._ratioGuard = true;
        this.width = this.sourceWidth;
        this.height = this.sourceHeight;
        this.$nextTick(() => (this._ratioGuard = false));
    },

    applySize(w, h) {
        this._ratioGuard = true;
        this.width = w;
        this.height = h;
        this.$nextTick(() => (this._ratioGuard = false));
    },

    applyRatio(rw, rh) {
        this._ratioGuard = true;
        const w = Math.max(1, parseInt(this.width, 10) || rw);
        this.height = Math.max(1, Math.round((w * rh) / rw));
        this.$nextTick(() => (this._ratioGuard = false));
    },

    formatBytes(n) {
        if (!n) return '0 B';
        if (n < 1024) return `${n} B`;
        if (n < 1024 * 1024) return `${(n / 1024).toFixed(1)} KB`;
        return `${(n / 1024 / 1024).toFixed(2)} MB`;
    },

    get sizeDelta() {
        if (!this.sourceBytes || !this.previewBytes) return null;
        const diff = this.previewBytes - this.sourceBytes;
        const pct = (diff / this.sourceBytes) * 100;
        const sign = diff > 0 ? '+' : diff < 0 ? '−' : '';
        const tone = diff > 0
            ? 'text-amber-600 dark:text-amber-400'
            : diff < 0
                ? 'text-emerald-600 dark:text-emerald-400'
                : 'opacity-60';
        return {
            label: `${sign}${this.formatBytes(Math.abs(diff))} (${sign}${Math.abs(pct).toFixed(1)}%)`,
            tone,
        };
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);

        const intParam = (key, prop, min, max) => {
            if (!params.has(key)) return;
            const n = parseInt(params.get(key), 10);
            if (Number.isFinite(n) && n >= min && n <= max) this[prop] = n;
        };

        intParam('w', 'width', 1, 4096);
        intParam('h', 'height', 1, 4096);
        intParam('q', 'quality', 1, 100);

        if (params.has('fit')) {
            const v = params.get('fit');
            if (['contain', 'cover', 'stretch'].includes(v)) this.fit = v;
        }

        if (params.has('fmt')) {
            const v = 'image/' + params.get('fmt').toLowerCase().replace('jpg', 'jpeg');
            if (FORMATS[v]) this.format = v;
        }

        if (params.has('bg') && /^#?[0-9a-fA-F]{6}$/.test(params.get('bg'))) {
            this.bg = '#' + params.get('bg').replace('#', '');
        }
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        const setOrDelete = (key, val, def) => {
            if (val === def) params.delete(key);
            else params.set(key, val);
        };

        setOrDelete('w', parseInt(this.width, 10), DEFAULTS.width);
        setOrDelete('h', parseInt(this.height, 10), DEFAULTS.height);
        setOrDelete('fit', this.fit, DEFAULTS.fit);

        const fmtShort = this.format.replace('image/', '').replace('jpeg', 'jpg');
        const defShort = DEFAULTS.format.replace('image/', '').replace('jpeg', 'jpg');
        setOrDelete('fmt', fmtShort, defShort);

        if (this.supportsQuality) {
            setOrDelete('q', parseInt(this.quality, 10), DEFAULTS.quality);
        } else {
            params.delete('q');
        }

        if (this.bg.toLowerCase() !== DEFAULTS.bg) {
            params.set('bg', this.bg.replace('#', ''));
        } else {
            params.delete('bg');
        }

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
