const MAX_FILE_BYTES = 25 * 1024 * 1024;

function bytesToBase64(bytes) {
    let binary = '';
    const chunk = 0x8000;
    for (let i = 0; i < bytes.length; i += chunk) {
        binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunk));
    }
    return btoa(binary);
}

function base64ToBytes(b64) {
    const cleaned = b64.replace(/\s+/g, '');
    const binary = atob(cleaned);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
    return bytes;
}

function encodeText(str, urlSafe) {
    const bytes = new TextEncoder().encode(str);
    const b64 = bytesToBase64(bytes);
    if (!urlSafe) return b64;
    return b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function decodeText(b64) {
    let normalised = b64.replace(/-/g, '+').replace(/_/g, '/');
    const pad = normalised.length % 4;
    if (pad) normalised += '='.repeat(4 - pad);
    const bytes = base64ToBytes(normalised);
    return new TextDecoder().decode(bytes);
}

export default () => ({
    mode: 'text',
    direction: 'encode',
    urlSafe: false,
    input: '',
    error: '',
    copied: false,
    file: null,
    fileResult: '',
    fileError: '',
    fileBusy: false,

    init() {
        this.initFromUrl();

        ['direction', 'input', 'urlSafe', 'mode'].forEach((prop) => {
            this.$watch(prop, () => this.updateUrl());
        });
    },

    get output() {
        this.error = '';
        if (!this.input) return '';
        try {
            return this.direction === 'encode'
                ? encodeText(this.input, this.urlSafe)
                : decodeText(this.input);
        } catch (e) {
            this.error = this.direction === 'decode'
                ? 'Input is not valid Base64.'
                : 'Could not encode this text.';
            return '';
        }
    },

    get inputLabel() {
        return this.direction === 'encode' ? 'Plain text' : 'Base64';
    },
    get outputLabel() {
        return this.direction === 'encode' ? 'Base64' : 'Plain text';
    },
    get inputPlaceholder() {
        return this.direction === 'encode'
            ? 'Type or paste text to encode'
            : 'Paste a Base64 string to decode';
    },

    swap() {
        if (!this.output) return;
        const next = this.output;
        this.direction = this.direction === 'encode' ? 'decode' : 'encode';
        this.input = next;
    },

    clear() {
        this.input = '';
    },

    async copy() {
        if (!this.output) return;
        await navigator.clipboard.writeText(this.output);
        this.copied = true;
        setTimeout(() => (this.copied = false), 1500);
    },

    onFileSelected(event) {
        const file = event.target.files?.[0];
        this.fileError = '';
        this.fileResult = '';

        if (!file) return;

        if (file.size > MAX_FILE_BYTES) {
            this.fileError = `File is larger than ${MAX_FILE_BYTES / 1024 / 1024} MB.`;
            event.target.value = '';
            return;
        }

        this.file = { name: file.name, size: file.size, type: file.type };
        this.fileBusy = true;

        const reader = new FileReader();
        reader.onload = () => {
            try {
                const bytes = new Uint8Array(reader.result);
                this.fileResult = bytesToBase64(bytes);
            } catch (e) {
                this.fileError = 'Could not encode this file.';
            }
            this.fileBusy = false;
        };
        reader.onerror = () => {
            this.fileError = 'Could not read this file.';
            this.fileBusy = false;
        };
        reader.readAsArrayBuffer(file);
    },

    clearFile() {
        this.file = null;
        this.fileResult = '';
        this.fileError = '';
        if (this.$refs.fileInput) this.$refs.fileInput.value = '';
    },

    async copyFileResult() {
        if (!this.fileResult) return;
        await navigator.clipboard.writeText(this.fileResult);
        this.copied = true;
        setTimeout(() => (this.copied = false), 1500);
    },

    downloadDecoded() {
        if (this.direction !== 'decode' || !this.output) return;
        try {
            let normalised = this.input.replace(/\s+/g, '').replace(/-/g, '+').replace(/_/g, '/');
            const pad = normalised.length % 4;
            if (pad) normalised += '='.repeat(4 - pad);
            const bytes = base64ToBytes(normalised);
            const blob = new Blob([bytes], { type: 'application/octet-stream' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'decoded.bin';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        } catch (e) {
            this.error = 'Input is not valid Base64.';
        }
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('mode') && ['text', 'file'].includes(params.get('mode'))) {
            this.mode = params.get('mode');
        }
        if (params.has('dir') && ['encode', 'decode'].includes(params.get('dir'))) {
            this.direction = params.get('dir');
        }
        if (params.get('urlsafe') === '1') {
            this.urlSafe = true;
        }
        if (params.has('text')) {
            this.input = params.get('text');
        }
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        if (this.mode !== 'text') params.set('mode', this.mode); else params.delete('mode');
        if (this.direction !== 'encode') params.set('dir', this.direction); else params.delete('dir');
        if (this.urlSafe) params.set('urlsafe', '1'); else params.delete('urlsafe');
        if (this.mode === 'text' && this.input) {
            params.set('text', this.input);
        } else {
            params.delete('text');
        }

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        window.history.replaceState({}, '', newUrl);
    },
});
