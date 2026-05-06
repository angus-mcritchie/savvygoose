import SparkMD5 from 'spark-md5';

const MAX_FILE_BYTES = 100 * 1024 * 1024;
const FILE_CHUNK = 2 * 1024 * 1024;
const ALGOS = [
    { key: 'md5', label: 'MD5' },
    { key: 'sha1', label: 'SHA-1' },
    { key: 'sha256', label: 'SHA-256' },
    { key: 'sha512', label: 'SHA-512' },
];

function bytesToHex(buf) {
    const view = new Uint8Array(buf);
    let out = '';
    for (let i = 0; i < view.length; i++) {
        out += view[i].toString(16).padStart(2, '0');
    }
    return out;
}

async function subtleDigest(name, data) {
    const buf = await crypto.subtle.digest(name, data);
    return bytesToHex(buf);
}

async function hashText(text) {
    const bytes = new TextEncoder().encode(text);
    const md5 = SparkMD5.ArrayBuffer.hash(bytes.buffer);
    const [sha1, sha256, sha512] = await Promise.all([
        subtleDigest('SHA-1', bytes),
        subtleDigest('SHA-256', bytes),
        subtleDigest('SHA-512', bytes),
    ]);
    return { md5, sha1, sha256, sha512 };
}

function readFileChunk(file, start, end) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(reader.error);
        reader.readAsArrayBuffer(file.slice(start, end));
    });
}

async function hashFile(file, onProgress) {
    const md5 = new SparkMD5.ArrayBuffer();

    const chunks = [];
    for (let start = 0; start < file.size; start += FILE_CHUNK) {
        const end = Math.min(start + FILE_CHUNK, file.size);
        const buf = await readFileChunk(file, start, end);
        md5.append(buf);
        chunks.push(new Uint8Array(buf));
        onProgress?.(end / file.size);
    }

    const total = chunks.reduce((n, c) => n + c.byteLength, 0);
    const combined = new Uint8Array(total);
    let offset = 0;
    for (const c of chunks) {
        combined.set(c, offset);
        offset += c.byteLength;
    }

    const [sha1, sha256, sha512] = await Promise.all([
        subtleDigest('SHA-1', combined),
        subtleDigest('SHA-256', combined),
        subtleDigest('SHA-512', combined),
    ]);

    return { md5: md5.end(), sha1, sha256, sha512 };
}

export default () => ({
    mode: 'text',
    text: '',
    file: null,
    fileError: '',
    progress: 0,
    busy: false,
    hashes: { md5: '', sha1: '', sha256: '', sha512: '' },
    copied: '',
    algos: ALGOS,
    textBusyToken: 0,

    init() {
        this.initFromUrl();

        this.$watch('text', () => {
            this.updateUrl();
            this.scheduleTextHash();
        });
        this.$watch('mode', () => this.updateUrl());

        if (this.text) this.scheduleTextHash();
    },

    scheduleTextHash() {
        const token = ++this.textBusyToken;
        if (!this.text) {
            this.hashes = { md5: '', sha1: '', sha256: '', sha512: '' };
            return;
        }
        hashText(this.text).then((result) => {
            if (token !== this.textBusyToken) return;
            this.hashes = result;
        });
    },

    onFileSelected(event) {
        const file = event.target.files?.[0];
        this.fileError = '';
        this.hashes = { md5: '', sha1: '', sha256: '', sha512: '' };
        this.progress = 0;

        if (!file) return;

        if (file.size > MAX_FILE_BYTES) {
            this.fileError = `File is larger than ${MAX_FILE_BYTES / 1024 / 1024} MB.`;
            event.target.value = '';
            return;
        }

        this.file = { name: file.name, size: file.size };
        this.busy = true;

        hashFile(file, (p) => (this.progress = p))
            .then((result) => {
                this.hashes = result;
                this.busy = false;
                this.progress = 1;
            })
            .catch(() => {
                this.fileError = 'Could not hash this file.';
                this.busy = false;
            });
    },

    clearFile() {
        this.file = null;
        this.fileError = '';
        this.hashes = { md5: '', sha1: '', sha256: '', sha512: '' };
        this.progress = 0;
        if (this.$refs.fileInput) this.$refs.fileInput.value = '';
    },

    clearText() {
        this.text = '';
    },

    async copy(algo) {
        const value = this.hashes[algo];
        if (!value) return;
        await navigator.clipboard.writeText(value);
        this.copied = algo;
        setTimeout(() => {
            if (this.copied === algo) this.copied = '';
        }, 1500);
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('mode') && ['text', 'file'].includes(params.get('mode'))) {
            this.mode = params.get('mode');
        }
        if (params.has('text')) {
            this.text = params.get('text');
        }
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        if (this.mode !== 'text') params.set('mode', this.mode); else params.delete('mode');
        if (this.mode === 'text' && this.text) {
            params.set('text', this.text);
        } else {
            params.delete('text');
        }

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        window.history.replaceState({}, '', newUrl);
    },
});
