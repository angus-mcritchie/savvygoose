import SparkMD5 from 'spark-md5';
import { withUrlState } from '../lib/urlState';

const MAX_FILE_BYTES = 100 * 1024 * 1024;
const FILE_CHUNK = 2 * 1024 * 1024;
const MAX_URL_INPUT = 5000;
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

const schema = {
    mode: { type: 'enum', values: ['text', 'file'], default: 'text' },
    text: {
        type: 'string',
        maxLength: MAX_URL_INPUT,
        serialize: (value, state) => {
            if (state.mode !== 'text') return { skip: true };
            if (!value) return { skip: true };
            if (value.length > MAX_URL_INPUT) return { skip: true, tooLong: true };
            return { value };
        },
    },
};

export default withUrlState(schema, () => ({
    file: null,
    fileError: '',
    progress: 0,
    busy: false,
    hashes: { md5: '', sha1: '', sha256: '', sha512: '' },
    algos: ALGOS,
    textBusyToken: 0,

    init() {
        this.$watch('text', () => this.scheduleTextHash());
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
}));
