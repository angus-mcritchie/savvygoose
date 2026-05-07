const DEFAULTS = {
    canvasWidth: 512,
    canvasHeight: 512,
    imageWidth: 512,
    imageHeight: 512,
    imageX: 0,
    imageY: 0,
    imageRotation: 0,
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
const MAX_DIM = 4096;
const MIN_DIM = 1;

const deg2rad = (d) => (d * Math.PI) / 180;
const normalizeAngle = (a) => (((a + 180) % 360) + 360) % 360 - 180;

export default () => ({
    canvasWidth: DEFAULTS.canvasWidth,
    canvasHeight: DEFAULTS.canvasHeight,
    imageWidth: DEFAULTS.imageWidth,
    imageHeight: DEFAULTS.imageHeight,
    imageX: DEFAULTS.imageX,
    imageY: DEFAULTS.imageY,
    imageRotation: DEFAULTS.imageRotation,
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
    previewBytes: 0,
    displayScale: 1,
    formats: FORMATS,
    sizePresets: SIZE_PRESETS,
    ratioPresets: RATIO_PRESETS,
    url: window.location.href,
    _previewToken: 0,
    _ratioGuard: false,
    _drag: null,
    _resizeObserver: null,

    init() {
        this.initFromUrl();

        const renderProps = [
            'canvasWidth', 'canvasHeight',
            'imageWidth', 'imageHeight',
            'imageX', 'imageY', 'imageRotation',
            'format', 'quality', 'bg', 'transparent',
        ];
        renderProps.forEach((prop) => {
            this.$watch(prop, () => {
                this.updateUrl();
                this.renderPreview();
            });
        });

        this.$watch('imageWidth', (val) => {
            if (!this.locked || this._ratioGuard || !this.sourceRatio) return;
            this._ratioGuard = true;
            this.imageHeight = Math.max(MIN_DIM, Math.round(val / this.sourceRatio));
            this.$nextTick(() => (this._ratioGuard = false));
        });

        this.$watch('imageHeight', (val) => {
            if (!this.locked || this._ratioGuard || !this.sourceRatio) return;
            this._ratioGuard = true;
            this.imageWidth = Math.max(MIN_DIM, Math.round(val * this.sourceRatio));
            this.$nextTick(() => (this._ratioGuard = false));
        });

        this.$watch('canvasWidth', () => this.$nextTick(() => this.recomputeDisplayScale()));
        this.$watch('canvasHeight', () => this.$nextTick(() => this.recomputeDisplayScale()));

        this.$nextTick(() => {
            if (this.$refs.preview && window.ResizeObserver) {
                this._resizeObserver = new ResizeObserver(() => this.recomputeDisplayScale());
                this._resizeObserver.observe(this.$refs.preview);
            }
            this.recomputeDisplayScale();
            this.renderPreview();
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

    get imageScreenLeft() {
        return (this.canvasWidth / 2 + this.imageX) * this.displayScale;
    },
    get imageScreenTop() {
        return (this.canvasHeight / 2 + this.imageY) * this.displayScale;
    },
    get imageScreenWidth() {
        return this.imageWidth * this.displayScale;
    },
    get imageScreenHeight() {
        return this.imageHeight * this.displayScale;
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
            this.imageX = 0;
            this.imageY = 0;
            this.imageRotation = 0;
            if (isFirstLoad && canvasWasDefault) {
                this.canvasWidth = this.sourceWidth;
                this.canvasHeight = this.sourceHeight;
            }
            this.$nextTick(() => {
                this._ratioGuard = false;
                this.recomputeDisplayScale();
                this.renderPreview();
            });
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
        this.previewBytes = 0;
        this.error = null;
        if (this.$refs.preview) {
            const ctx = this.$refs.preview.getContext('2d');
            ctx.clearRect(0, 0, this.$refs.preview.width, this.$refs.preview.height);
        }
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

        if (!this.source) return;

        ctx.save();
        ctx.translate(cw / 2 + this.imageX, ch / 2 + this.imageY);
        if (this.imageRotation) ctx.rotate(deg2rad(this.imageRotation));
        ctx.drawImage(this.source, -iw / 2, -ih / 2, iw, ih);
        ctx.restore();
    },

    async renderPreview() {
        const canvas = this.$refs.preview;
        if (!canvas) return;

        const cw = Math.max(MIN_DIM, Math.min(MAX_DIM, parseInt(this.canvasWidth, 10) || MIN_DIM));
        const ch = Math.max(MIN_DIM, Math.min(MAX_DIM, parseInt(this.canvasHeight, 10) || MIN_DIM));

        if (!this.source) {
            canvas.width = cw;
            canvas.height = ch;
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, cw, ch);
            this.previewBytes = 0;
            this.recomputeDisplayScale();
            return;
        }

        const iw = Math.max(MIN_DIM, parseInt(this.imageWidth, 10) || MIN_DIM);
        const ih = Math.max(MIN_DIM, parseInt(this.imageHeight, 10) || MIN_DIM);
        const token = ++this._previewToken;

        this.drawTo(canvas, cw, ch, iw, ih);
        this.recomputeDisplayScale();

        const blob = await this.canvasBlob(canvas);
        if (token !== this._previewToken) return;
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
        const cw = Math.max(MIN_DIM, parseInt(this.canvasWidth, 10));
        const ch = Math.max(MIN_DIM, parseInt(this.canvasHeight, 10));
        const iw = Math.max(MIN_DIM, parseInt(this.imageWidth, 10));
        const ih = Math.max(MIN_DIM, parseInt(this.imageHeight, 10));
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

    recomputeDisplayScale() {
        const canvas = this.$refs.preview;
        if (!canvas) return;
        const rect = canvas.getBoundingClientRect();
        if (!rect.width || !this.canvasWidth) return;
        this.displayScale = rect.width / this.canvasWidth;
    },

    toggleLock() {
        this.locked = !this.locked;
        if (this.locked && this.source) {
            this._ratioGuard = true;
            this.imageHeight = Math.max(MIN_DIM, Math.round(this.imageWidth / this.sourceRatio));
            this.$nextTick(() => (this._ratioGuard = false));
        }
    },

    matchSource() {
        if (!this.source) return;
        this._ratioGuard = true;
        this.imageWidth = this.sourceWidth;
        this.imageHeight = this.sourceHeight;
        this.imageX = 0;
        this.imageY = 0;
        this.imageRotation = 0;
        this.$nextTick(() => (this._ratioGuard = false));
    },

    fitImageToCanvas() {
        if (!this.source) return;
        const scale = Math.min(
            this.canvasWidth / this.sourceWidth,
            this.canvasHeight / this.sourceHeight,
        );
        this._ratioGuard = true;
        this.imageWidth = Math.max(MIN_DIM, Math.round(this.sourceWidth * scale));
        this.imageHeight = Math.max(MIN_DIM, Math.round(this.sourceHeight * scale));
        this.imageX = 0;
        this.imageY = 0;
        this.imageRotation = 0;
        this.$nextTick(() => (this._ratioGuard = false));
    },

    centerImage() {
        this.imageX = 0;
        this.imageY = 0;
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
        const w = Math.max(MIN_DIM, parseInt(this.canvasWidth, 10) || rw);
        this.canvasHeight = Math.max(MIN_DIM, Math.min(MAX_DIM, Math.round((w * rh) / rw)));
    },

    pointerToCanvas(event) {
        const rect = this.$refs.preview.getBoundingClientRect();
        return {
            x: (event.clientX - rect.left) / this.displayScale,
            y: (event.clientY - rect.top) / this.displayScale,
        };
    },

    startDrag(event) {
        if (!this.source) return;
        event.preventDefault();
        this._drag = {
            type: 'drag',
            startX: event.clientX,
            startY: event.clientY,
            origImageX: this.imageX,
            origImageY: this.imageY,
        };
    },

    startScale(event, corner) {
        if (!this.source) return;
        event.preventDefault();
        event.stopPropagation();

        const sign = {
            nw: { x: -1, y: -1 },
            ne: { x: 1, y: -1 },
            sw: { x: -1, y: 1 },
            se: { x: 1, y: 1 },
        }[corner];

        const rad = deg2rad(this.imageRotation);
        const cosR = Math.cos(rad);
        const sinR = Math.sin(rad);

        // Anchor = opposite corner in canvas coords (held fixed during scale).
        const localAx = -sign.x * this.imageWidth / 2;
        const localAy = -sign.y * this.imageHeight / 2;
        const anchorX = this.canvasWidth / 2 + this.imageX + cosR * localAx - sinR * localAy;
        const anchorY = this.canvasHeight / 2 + this.imageY + sinR * localAx + cosR * localAy;

        this._drag = {
            type: 'scale',
            sign,
            cosR,
            sinR,
            anchorX,
            anchorY,
            startW: this.imageWidth,
            startH: this.imageHeight,
        };
    },

    startRotate(event) {
        if (!this.source) return;
        event.preventDefault();
        event.stopPropagation();

        const rect = this.$refs.preview.getBoundingClientRect();
        const cx = rect.left + (this.canvasWidth / 2 + this.imageX) * this.displayScale;
        const cy = rect.top + (this.canvasHeight / 2 + this.imageY) * this.displayScale;

        const startAngle = Math.atan2(event.clientY - cy, event.clientX - cx) * 180 / Math.PI;

        this._drag = {
            type: 'rotate',
            cx,
            cy,
            startAngle,
            origRotation: this.imageRotation,
        };
    },

    onPointerMove(event) {
        if (!this._drag) return;
        const d = this._drag;

        if (d.type === 'drag') {
            const dx = (event.clientX - d.startX) / this.displayScale;
            const dy = (event.clientY - d.startY) / this.displayScale;
            this.imageX = Math.round(d.origImageX + dx);
            this.imageY = Math.round(d.origImageY + dy);
            return;
        }

        if (d.type === 'scale') {
            const p = this.pointerToCanvas(event);
            const vx = p.x - d.anchorX;
            const vy = p.y - d.anchorY;

            // Project pointer-from-anchor into image-local axes (inverse rotation).
            const localX = d.cosR * vx + d.sinR * vy;
            const localY = -d.sinR * vx + d.cosR * vy;

            let newW = Math.max(MIN_DIM, Math.abs(localX));
            let newH = Math.max(MIN_DIM, Math.abs(localY));

            if (this.locked) {
                const ratio = d.startW / d.startH;
                if (newW / newH > ratio) newH = newW / ratio;
                else newW = newH * ratio;
            }

            // Place center so the anchor (opposite corner) stays put.
            const localAx = -d.sign.x * newW / 2;
            const localAy = -d.sign.y * newH / 2;
            const rotAx = d.cosR * localAx - d.sinR * localAy;
            const rotAy = d.sinR * localAx + d.cosR * localAy;
            const newCenterX = d.anchorX - rotAx;
            const newCenterY = d.anchorY - rotAy;

            this._ratioGuard = true;
            this.imageWidth = Math.round(newW);
            this.imageHeight = Math.round(newH);
            this.imageX = Math.round(newCenterX - this.canvasWidth / 2);
            this.imageY = Math.round(newCenterY - this.canvasHeight / 2);
            this.$nextTick(() => (this._ratioGuard = false));
            return;
        }

        if (d.type === 'rotate') {
            const angle = Math.atan2(event.clientY - d.cy, event.clientX - d.cx) * 180 / Math.PI;
            let next = d.origRotation + (angle - d.startAngle);
            if (event.shiftKey) next = Math.round(next / 15) * 15;
            this.imageRotation = Math.round(normalizeAngle(next) * 10) / 10;
        }
    },

    onPointerUp() {
        this._drag = null;
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

        intParam('cw', 'canvasWidth', MIN_DIM, MAX_DIM);
        intParam('ch', 'canvasHeight', MIN_DIM, MAX_DIM);
        intParam('iw', 'imageWidth', MIN_DIM, MAX_DIM);
        intParam('ih', 'imageHeight', MIN_DIM, MAX_DIM);
        intParam('x', 'imageX', -MAX_DIM, MAX_DIM);
        intParam('y', 'imageY', -MAX_DIM, MAX_DIM);
        intParam('q', 'quality', 1, 100);

        if (params.has('r')) {
            const n = parseFloat(params.get('r'));
            if (Number.isFinite(n)) this.imageRotation = normalizeAngle(n);
        }

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
        setOrDelete('x', parseInt(this.imageX, 10), DEFAULTS.imageX);
        setOrDelete('y', parseInt(this.imageY, 10), DEFAULTS.imageY);

        if (this.imageRotation !== DEFAULTS.imageRotation) {
            params.set('r', String(this.imageRotation));
        } else {
            params.delete('r');
        }

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
