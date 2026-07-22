const MAX_FILE_BYTES = 10 * 1024 * 1024;
const PREVIEW_SIZES = [16, 32, 48, 180, 512];
const ICO_SIZES = [16, 32, 48];

// Assemble a multi-image .ico from PNG-encoded entries. Modern browsers and
// operating systems accept PNG-compressed icon directory entries.
function buildIco(images) {
    const headerSize = 6 + images.length * 16;
    let offset = headerSize;
    const entries = images.map((im) => {
        const entry = { ...im, offset };
        offset += im.data.length;
        return entry;
    });

    const buf = new Uint8Array(offset);
    const view = new DataView(buf.buffer);
    view.setUint16(0, 0, true); // reserved
    view.setUint16(2, 1, true); // type: icon
    view.setUint16(4, images.length, true);

    let p = 6;
    for (const im of entries) {
        buf[p] = im.size >= 256 ? 0 : im.size; // width (0 => 256)
        buf[p + 1] = im.size >= 256 ? 0 : im.size; // height
        buf[p + 2] = 0; // palette size
        buf[p + 3] = 0; // reserved
        view.setUint16(p + 4, 1, true); // color planes
        view.setUint16(p + 6, 32, true); // bits per pixel
        view.setUint32(p + 8, im.data.length, true); // image byte length
        view.setUint32(p + 12, im.offset, true); // image offset
        p += 16;
    }
    for (const im of entries) buf.set(im.data, im.offset);
    return buf;
}

export default () => ({
    file: null,
    sourceImage: null,
    fileName: '',
    error: '',
    busy: false,
    ready: false,
    previews: {},
    previewSizes: PREVIEW_SIZES,
    _loadId: 0,

    onFileSelected(event) {
        const loadId = ++this._loadId;
        const file = event.target.files?.[0];
        this.file = null;
        this.sourceImage = null;
        this.fileName = '';
        this.error = '';
        this.busy = false;
        this.ready = false;
        this.previews = {};

        if (!file) return;

        if (!file.type.startsWith('image/')) {
            this.error = 'Please choose an image file. A square PNG or SVG works best.';
            event.target.value = '';
            return;
        }
        if (file.size > MAX_FILE_BYTES) {
            this.error = `Image is larger than ${MAX_FILE_BYTES / 1024 / 1024} MB.`;
            event.target.value = '';
            return;
        }

        this.file = { name: file.name, size: file.size };
        this.fileName = file.name;
        this.busy = true;
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            if (loadId !== this._loadId) {
                URL.revokeObjectURL(url);
                return;
            }
            this.sourceImage = img;
            const p = {};
            for (const s of PREVIEW_SIZES) p[s] = this.drawSize(s).toDataURL('image/png');
            this.previews = p;
            URL.revokeObjectURL(url);
            this.busy = false;
            this.ready = true;
        };
        img.onerror = () => {
            if (loadId !== this._loadId) {
                URL.revokeObjectURL(url);
                return;
            }
            this.error = 'Could not load that image.';
            this.busy = false;
            URL.revokeObjectURL(url);
        };
        img.src = url;
    },

    clearFile() {
        this._loadId++;
        this.file = null;
        this.sourceImage = null;
        this.fileName = '';
        this.error = '';
        this.busy = false;
        this.ready = false;
        this.previews = {};
        if (this.$refs.fileInput) this.$refs.fileInput.value = '';
    },

    drawSize(size) {
        const canvas = document.createElement('canvas');
        canvas.width = size;
        canvas.height = size;
        const ctx = canvas.getContext('2d');
        const img = this.sourceImage;
        // Contain the whole image, centered, so nothing is cropped.
        const scale = Math.min(size / img.width, size / img.height);
        const w = img.width * scale;
        const h = img.height * scale;
        ctx.drawImage(img, (size - w) / 2, (size - h) / 2, w, h);
        return canvas;
    },

    triggerDownload(blob, name) {
        if (!blob) {
            this.error = 'Could not generate the image. Try a different source image.';
            return;
        }
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = name;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    },

    downloadPng(size) {
        if (!this.ready) return;
        this.drawSize(size).toBlob((blob) => this.triggerDownload(blob, `favicon-${size}x${size}.png`), 'image/png');
    },

    async downloadIco() {
        if (!this.ready) return;
        try {
            const images = [];
            for (const s of ICO_SIZES) {
                const blob = await new Promise((res) => this.drawSize(s).toBlob(res, 'image/png'));
                if (!blob) throw new Error('encode failed');
                images.push({ size: s, data: new Uint8Array(await blob.arrayBuffer()) });
            }
            this.triggerDownload(new Blob([buildIco(images)], { type: 'image/x-icon' }), 'favicon.ico');
        } catch (e) {
            this.error = 'Could not build the .ico file.';
        }
    },

    get linkSnippet() {
        return [
            '<link rel="icon" href="/favicon.ico" sizes="any">',
            '<link rel="icon" type="image/png" href="/favicon-32x32.png">',
            '<link rel="apple-touch-icon" href="/favicon-180x180.png">',
        ].join('\n');
    },
});
