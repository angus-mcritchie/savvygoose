import { withUrlState } from '../lib/urlState';

const DEFAULTS = {
    canvasWidth: 512,
    canvasHeight: 512,
    imageWidth: 512,
    imageHeight: 512,
    imageX: 0,
    imageY: 0,
    imageRotation: 0,
    format: 'image/jpeg',
    quality: 80,
    bg: '#ffffff',
    transparent: false,
    locked: true,
    allowExceedCanvas: false,
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

const schema = {
    canvasWidth: { type: 'integer', alias: 'cw', default: DEFAULTS.canvasWidth, min: MIN_DIM, max: MAX_DIM },
    canvasHeight: { type: 'integer', alias: 'ch', default: DEFAULTS.canvasHeight, min: MIN_DIM, max: MAX_DIM },
    imageWidth: { type: 'integer', alias: 'iw', default: DEFAULTS.imageWidth, min: MIN_DIM, max: MAX_DIM },
    imageHeight: { type: 'integer', alias: 'ih', default: DEFAULTS.imageHeight, min: MIN_DIM, max: MAX_DIM },
    imageX: { type: 'integer', alias: 'x', default: DEFAULTS.imageX, min: -MAX_DIM, max: MAX_DIM },
    imageY: { type: 'integer', alias: 'y', default: DEFAULTS.imageY, min: -MAX_DIM, max: MAX_DIM },
    imageRotation: {
        type: 'number',
        alias: 'r',
        default: DEFAULTS.imageRotation,
        parse: (raw) => {
            const n = parseFloat(raw);
            return Number.isFinite(n) ? normalizeAngle(n) : undefined;
        },
    },
    format: {
        type: 'string',
        alias: 'fmt',
        default: DEFAULTS.format,
        parse: (raw) => {
            const v = 'image/' + raw.toLowerCase().replace('jpg', 'jpeg');
            return FORMATS[v] ? v : undefined;
        },
        serialize: (value) => ({ value: value.replace('image/', '').replace('jpeg', 'jpg') }),
    },
    quality: {
        type: 'integer',
        alias: 'q',
        default: DEFAULTS.quality,
        min: 1,
        max: 100,
        serialize: (value, state) => {
            if (!FORMATS[state.format]?.supportsQuality) return { skip: true };
            return { value: String(value) };
        },
    },
    bg: { type: 'color', default: DEFAULTS.bg },
    transparent: { type: 'boolean', alias: 'tr', default: DEFAULTS.transparent },
    allowExceedCanvas: { type: 'boolean', alias: 'over', default: DEFAULTS.allowExceedCanvas },
};

export default withUrlState(schema, () => ({
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
    allowExceedCanvas: DEFAULTS.allowExceedCanvas,
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
    spaceHeld: false,
    formats: FORMATS,
    sizePresets: SIZE_PRESETS,
    ratioPresets: RATIO_PRESETS,
    _previewToken: 0,
    _ratioGuard: false,
    _drag: null,
    _resizeObserver: null,

    init() {
        const renderProps = [
            'canvasWidth', 'canvasHeight',
            'imageWidth', 'imageHeight',
            'imageX', 'imageY', 'imageRotation',
            'format', 'quality', 'bg', 'transparent',
            'allowExceedCanvas',
        ];
        renderProps.forEach((prop) => {
            this.$watch(prop, () => this.renderPreview());
        });

        this.$watch('imageWidth', (val) => {
            if (this._ratioGuard || !this.sourceRatio) return;
            this._ratioGuard = true;
            if (this.locked) {
                this.imageHeight = Math.max(MIN_DIM, Math.round(val / this.sourceRatio));
            }
            this.clampImageToCanvas();
            this.$nextTick(() => (this._ratioGuard = false));
        });

        this.$watch('imageHeight', (val) => {
            if (this._ratioGuard || !this.sourceRatio) return;
            this._ratioGuard = true;
            if (this.locked) {
                this.imageWidth = Math.max(MIN_DIM, Math.round(val * this.sourceRatio));
            }
            this.clampImageToCanvas();
            this.$nextTick(() => (this._ratioGuard = false));
        });

        this.$watch('canvasWidth', () => {
            if (!this.allowExceedCanvas && this.source) this.fitImageToCanvas();
            else this.clampImageToCanvas();
            this.$nextTick(() => this.recomputeDisplayScale());
        });
        this.$watch('canvasHeight', () => {
            if (!this.allowExceedCanvas && this.source) this.fitImageToCanvas();
            else this.clampImageToCanvas();
            this.$nextTick(() => this.recomputeDisplayScale());
        });

        this.$watch('allowExceedCanvas', () => {
            if (!this.allowExceedCanvas) this.clampImageToCanvas();
        });

        this.$nextTick(() => {
            if (this.$refs.preview && window.ResizeObserver) {
                this._resizeObserver = new ResizeObserver(() => this.recomputeDisplayScale());
                this._resizeObserver.observe(this.$refs.preview);
            }
            this.recomputeDisplayScale();
            this.renderPreview();
        });
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

    get scaleLabel() {
        if (!this.sourceWidth || !this.sourceHeight) return '';
        const wPct = Math.round((this.imageWidth / this.sourceWidth) * 100);
        const hPct = Math.round((this.imageHeight / this.sourceHeight) * 100);
        if (wPct === hPct) return `${wPct}%`;
        return `W ${wPct}% · H ${hPct}%`;
    },

    get isUpscaled() {
        if (!this.sourceWidth || !this.sourceHeight) return false;
        return this.imageWidth > this.sourceWidth + 1
            || this.imageHeight > this.sourceHeight + 1;
    },

    get isStretched() {
        if (!this.sourceWidth || !this.sourceHeight || !this.imageHeight) return false;
        const srcAspect = this.sourceWidth / this.sourceHeight;
        const aspect = this.imageWidth / this.imageHeight;
        return Math.abs(aspect - srcAspect) / srcAspect > 0.01;
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
            if (isFirstLoad && canvasWasDefault) {
                this.canvasWidth = this.sourceWidth;
                this.canvasHeight = this.sourceHeight;
                this.imageWidth = this.sourceWidth;
                this.imageHeight = this.sourceHeight;
            } else {
                const scale = Math.min(
                    this.canvasWidth / this.sourceWidth,
                    this.canvasHeight / this.sourceHeight,
                );
                this.imageWidth = Math.max(MIN_DIM, Math.round(this.sourceWidth * scale));
                this.imageHeight = Math.max(MIN_DIM, Math.round(this.sourceHeight * scale));
            }
            this.imageX = 0;
            this.imageY = 0;
            this.imageRotation = 0;
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
        this.clampImageToCanvas();
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

    clampImageToCanvas() {
        if (this.allowExceedCanvas) return;
        if (!this.canvasWidth || !this.canvasHeight) return;
        if (!this.imageWidth || !this.imageHeight) return;
        const scale = Math.min(
            1,
            this.canvasWidth / this.imageWidth,
            this.canvasHeight / this.imageHeight,
        );
        if (scale >= 1) return;
        const wasGuarded = this._ratioGuard;
        if (!wasGuarded) this._ratioGuard = true;
        this.imageWidth = Math.max(MIN_DIM, Math.round(this.imageWidth * scale));
        this.imageHeight = Math.max(MIN_DIM, Math.round(this.imageHeight * scale));
        if (!wasGuarded) this.$nextTick(() => (this._ratioGuard = false));
    },

    resetImageSize() {
        if (!this.source) return;
        this._ratioGuard = true;
        this.imageWidth = this.sourceWidth;
        this.imageHeight = this.sourceHeight;
        this.clampImageToCanvas();
        this.$nextTick(() => (this._ratioGuard = false));
    },

    resetRotation() {
        this.imageRotation = 0;
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

        const centerX = this.canvasWidth / 2 + this.imageX;
        const centerY = this.canvasHeight / 2 + this.imageY;

        // Opposite corner in canvas coords (held fixed during normal scale).
        const localAx = -sign.x * this.imageWidth / 2;
        const localAy = -sign.y * this.imageHeight / 2;
        const anchorX = centerX + cosR * localAx - sinR * localAy;
        const anchorY = centerY + sinR * localAx + cosR * localAy;

        this._drag = {
            type: 'scale',
            sign,
            cosR,
            sinR,
            anchorX,
            anchorY,
            centerX,
            centerY,
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
            const fromCenter = event.altKey;

            const refX = fromCenter ? d.centerX : d.anchorX;
            const refY = fromCenter ? d.centerY : d.anchorY;
            const vx = p.x - refX;
            const vy = p.y - refY;

            // Project pointer-from-reference into image-local axes (inverse rotation).
            const localX = d.cosR * vx + d.sinR * vy;
            const localY = -d.sinR * vx + d.cosR * vy;

            const factor = fromCenter ? 2 : 1;
            let newW = Math.max(MIN_DIM, Math.abs(localX) * factor);
            let newH = Math.max(MIN_DIM, Math.abs(localY) * factor);

            if (this.locked) {
                const ratio = d.startW / d.startH;
                if (newW / newH > ratio) newH = newW / ratio;
                else newW = newH * ratio;
            }

            if (event.shiftKey && this.sourceWidth && this.sourceHeight) {
                const step = 0.25;
                const snap = (val, base) => Math.max(step, Math.round(val / base / step) * step) * base;
                newW = snap(newW, this.sourceWidth);
                if (this.locked) {
                    const ratio = d.startW / d.startH;
                    newH = newW / ratio;
                } else {
                    newH = snap(newH, this.sourceHeight);
                }
            }

            let newCenterX;
            let newCenterY;
            if (fromCenter) {
                newCenterX = d.centerX;
                newCenterY = d.centerY;
            } else {
                // Place center so the opposite corner stays put.
                const localAx = -d.sign.x * newW / 2;
                const localAy = -d.sign.y * newH / 2;
                const rotAx = d.cosR * localAx - d.sinR * localAy;
                const rotAy = d.sinR * localAx + d.cosR * localAy;
                newCenterX = d.anchorX - rotAx;
                newCenterY = d.anchorY - rotAy;
            }

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

    onKeyDown(event) {
        if (event.code !== 'Space') return;
        const t = event.target;
        if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.tagName === 'SELECT' || t.isContentEditable)) return;
        event.preventDefault();
        this.spaceHeld = true;
    },

    onKeyUp(event) {
        if (event.code === 'Space') this.spaceHeld = false;
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

}));
