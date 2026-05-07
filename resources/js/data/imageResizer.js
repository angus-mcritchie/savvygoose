const DEFAULTS = {
    canvasWidth: 512,
    canvasHeight: 512,
    imageWidth: 512,
    imageHeight: 512,
    format: 'image/png',
    quality: 92,
    bg: '#ffffff',
    transparent: true,
    locked: true,
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

export default () => ({
    canvasWidth: DEFAULTS.canvasWidth,
    canvasHeight: DEFAULTS.canvasHeight,
    imageWidth: DEFAULTS.imageWidth,
    imageHeight: DEFAULTS.imageHeight,
    format: DEFAULTS.format,
    quality: DEFAULTS.quality,
    bg: DEFAULTS.bg,
    transparent: DEFAULTS.transparent,
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

        ['canvasWidth', 'canvasHeight', 'imageWidth', 'imageHeight', 'format', 'quality', 'bg', 'transparent'].forEach((prop) => {
            this.$watch(prop, () => {
                this.updateUrl();
                this.renderPreview();
            });
        });

        this.$watch('imageWidth', (val) => {
            if (this.sourceWidth && val > this.sourceWidth) {
                this.imageWidth = this.sourceWidth;
                return;
            }
            if (!this.locked || this._ratioGuard || !this.sourceRatio) return;
            this._ratioGuard = true;
            this.imageHeight = Math.max(1, Math.round(val / this.sourceRatio));
            this.$nextTick(() => (this._ratioGuard = false));
        });

        this.$watch('imageHeight', (val) => {
            if (this.sourceHeight && val > this.sourceHeight) {
                this.imageHeight = this.sourceHeight;
                return;
            }
            if (!this.locked || this._ratioGuard || !this.sourceRatio) return;
            this._ratioGuard = true;
            this.imageWidth = Math.max(1, Math.round(val * this.sourceRatio));
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

    get isCanvasTransparent() {
        return this.transparent && this.supportsTransparency;
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

            const isFirstLoad = !this.source;
            const canvasWasDefault = this.canvasWidth === DEFAULTS.canvasWidth
                && this.canvasHeight === DEFAULTS.canvasHeight;

            this.source = img;
            this.sourceName = file.name;
            this.sourceBytes = file.size;
            this.sourceWidth = img.naturalWidth;
            this.sourceHeight = img.naturalHeight;
            this.sourceRatio = img.naturalWidth / img.naturalHeight;

            this._ratioGuard = true;
            this.imageWidth = this.sourceWidth;
            this.imageHeight = this.sourceHeight;
            if (isFirstLoad && canvasWasDefault) {
                this.canvasWidth = this.sourceWidth;
                this.canvasHeight = this.sourceHeight;
            }
            this.$nextTick(() => (this._ratioGuard = false));

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

    drawTo(canvas, cw, ch, iw, ih) {
        canvas.width = cw;
        canvas.height = ch;
        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';

        if (!this.isCanvasTransparent) {
            ctx.fillStyle = this.bg;
            ctx.fillRect(0, 0, cw, ch);
        }

        const x = (cw - iw) / 2;
        const y = (ch - ih) / 2;
        ctx.drawImage(this.source, x, y, iw, ih);
    },

    async renderPreview() {
        if (!this.source) {
            this.previewUrl = '';
            this.previewBytes = 0;
            return;
        }

        const cw = Math.max(1, Math.min(4096, parseInt(this.canvasWidth, 10) || 1));
        const ch = Math.max(1, Math.min(4096, parseInt(this.canvasHeight, 10) || 1));
        const iw = Math.max(1, parseInt(this.imageWidth, 10) || 1);
        const ih = Math.max(1, parseInt(this.imageHeight, 10) || 1);
        const token = ++this._previewToken;

        const canvas = document.createElement('canvas');
        this.drawTo(canvas, cw, ch, iw, ih);

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
        const cw = Math.max(1, parseInt(this.canvasWidth, 10));
        const ch = Math.max(1, parseInt(this.canvasHeight, 10));
        const iw = Math.max(1, parseInt(this.imageWidth, 10));
        const ih = Math.max(1, parseInt(this.imageHeight, 10));
        const canvas = document.createElement('canvas');
        this.drawTo(canvas, cw, ch, iw, ih);
        const blob = await this.canvasBlob(canvas);
        const ext = FORMATS[this.format].ext;
        this.triggerDownload(blob, `${this.baseName}-${cw}x${ch}.${ext}`);
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
            this.imageHeight = Math.max(1, Math.round(this.imageWidth / this.sourceRatio));
            this.$nextTick(() => (this._ratioGuard = false));
        }
    },

    matchSource() {
        if (!this.source) return;
        this._ratioGuard = true;
        this.imageWidth = this.sourceWidth;
        this.imageHeight = this.sourceHeight;
        this.$nextTick(() => (this._ratioGuard = false));
    },

    fitImageToCanvas() {
        if (!this.source) return;
        const scale = Math.min(
            this.canvasWidth / this.sourceWidth,
            this.canvasHeight / this.sourceHeight,
            1,
        );
        this._ratioGuard = true;
        this.imageWidth = Math.max(1, Math.round(this.sourceWidth * scale));
        this.imageHeight = Math.max(1, Math.round(this.sourceHeight * scale));
        this.$nextTick(() => (this._ratioGuard = false));
    },

    canvasMatchSource() {
        if (!this.source) return;
        this.canvasWidth = this.sourceWidth;
        this.canvasHeight = this.sourceHeight;
    },

    applyCanvasSize(w, h) {
        this.canvasWidth = w;
        this.canvasHeight = h;
    },

    applyCanvasRatio(rw, rh) {
        const w = Math.max(1, parseInt(this.canvasWidth, 10) || rw);
        this.canvasHeight = Math.max(1, Math.min(4096, Math.round((w * rh) / rw)));
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

        intParam('cw', 'canvasWidth', 1, 4096);
        intParam('ch', 'canvasHeight', 1, 4096);
        intParam('iw', 'imageWidth', 1, 4096);
        intParam('ih', 'imageHeight', 1, 4096);
        intParam('q', 'quality', 1, 100);

        if (params.has('fmt')) {
            const v = 'image/' + params.get('fmt').toLowerCase().replace('jpg', 'jpeg');
            if (FORMATS[v]) this.format = v;
        }

        if (params.has('bg') && /^#?[0-9a-fA-F]{6}$/.test(params.get('bg'))) {
            this.bg = '#' + params.get('bg').replace('#', '');
        }

        if (params.get('fill') === '1') this.transparent = false;
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        const setOrDelete = (key, val, def) => {
            if (val === def) params.delete(key);
            else params.set(key, val);
        };

        setOrDelete('cw', parseInt(this.canvasWidth, 10), DEFAULTS.canvasWidth);
        setOrDelete('ch', parseInt(this.canvasHeight, 10), DEFAULTS.canvasHeight);
        setOrDelete('iw', parseInt(this.imageWidth, 10), DEFAULTS.imageWidth);
        setOrDelete('ih', parseInt(this.imageHeight, 10), DEFAULTS.imageHeight);

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

        if (!this.transparent) params.set('fill', '1');
        else params.delete('fill');

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
