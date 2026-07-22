import { withUrlState } from '../lib/urlState';

const MAX_FILE_BYTES = 25 * 1024 * 1024;
const MAX_URL_INPUT = 3000;

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
    let bytes;
    try {
        bytes = base64ToBytes(normalised);
    } catch (e) {
        const err = new Error('invalid-base64');
        err.code = 'invalid-base64';
        throw err;
    }
    try {
        // fatal:true so binary data (an image/PDF blob) throws instead of
        // silently decoding to U+FFFD replacement-character mojibake.
        return new TextDecoder('utf-8', { fatal: true }).decode(bytes);
    } catch (e) {
        const err = new Error('binary');
        err.code = 'binary';
        throw err;
    }
}

const schema = {
    mode: { type: 'enum', values: ['text', 'file'], default: 'text' },
    direction: { type: 'enum', values: ['encode', 'decode'], default: 'encode', alias: 'dir' },
    urlSafe: { type: 'boolean', default: false, alias: 'urlsafe' },
    input: {
        type: 'string',
        alias: 'text',
        maxLength: MAX_URL_INPUT,
        serialize: (value, state) => {
            if (state.mode !== 'text') return { skip: true };
            if (value == null || value === '') return { skip: true };
            if (value.length > MAX_URL_INPUT) return { skip: true, tooLong: true };
            return { value };
        },
    },
};

export default withUrlState(schema, () => ({
    error: '',
    binaryDecode: false,
    file: null,
    fileResult: '',
    fileError: '',
    fileBusy: false,

    get output() {
        this.error = '';
        this.binaryDecode = false;
        if (!this.input) return '';
        try {
            return this.direction === 'encode'
                ? encodeText(this.input, this.urlSafe)
                : decodeText(this.input);
        } catch (e) {
            if (this.direction === 'encode') {
                this.error = 'Could not encode this text.';
            } else if (e.code === 'binary') {
                this.error = 'This Base64 decodes to binary data, not text. Use "Download as file" to save it.';
                this.binaryDecode = true;
            } else {
                this.error = 'Input is not valid Base64.';
            }
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

    downloadDecoded() {
        if (this.direction !== 'decode' || !this.input) return;
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
}));
