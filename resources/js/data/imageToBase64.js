const MAX_FILE_BYTES = 10 * 1024 * 1024;

// Escape a filename before dropping it into the generated <img alt="…"> so a
// name containing quotes or angle brackets can't break the copied HTML snippet.
const escapeAttr = (s) =>
    String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

export default () => ({
    file: null,
    dataUri: '',
    fileName: '',
    fileType: '',
    fileSize: 0,
    error: '',
    busy: false,

    onFileSelected(event) {
        const file = event.target.files?.[0];
        this.error = '';
        this.dataUri = '';

        if (!file) return;

        if (!file.type.startsWith('image/')) {
            this.error = 'Please choose an image file (PNG, JPEG, GIF, SVG, or WebP).';
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
        this.fileType = file.type;
        this.fileSize = file.size;
        this.busy = true;

        const reader = new FileReader();
        reader.onload = () => {
            this.dataUri = String(reader.result || '');
            this.busy = false;
        };
        reader.onerror = () => {
            this.error = 'Could not read that image.';
            this.busy = false;
        };
        reader.readAsDataURL(file);
    },

    clearFile() {
        this.file = null;
        this.dataUri = '';
        this.fileName = '';
        this.fileType = '';
        this.fileSize = 0;
        this.error = '';
        if (this.$refs.fileInput) this.$refs.fileInput.value = '';
    },

    get imgTag() {
        return this.dataUri ? `<img src="${this.dataUri}" alt="${escapeAttr(this.fileName || 'image')}">` : '';
    },

    get cssBackground() {
        return this.dataUri ? `background-image: url("${this.dataUri}");` : '';
    },

    get encodedSize() {
        return this.dataUri.length;
    },

    formatBytes(n) {
        if (n < 1024) return `${n} B`;
        if (n < 1024 * 1024) return `${(n / 1024).toFixed(1)} KB`;
        return `${(n / 1024 / 1024).toFixed(2)} MB`;
    },
});
