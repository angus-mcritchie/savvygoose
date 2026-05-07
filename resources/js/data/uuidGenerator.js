import { withUrlState } from '../lib/urlState';

const MAX_COUNT = 1000;

const toHex = (bytes) => Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');

const formatHex = (h) =>
    `${h.slice(0, 8)}-${h.slice(8, 12)}-${h.slice(12, 16)}-${h.slice(16, 20)}-${h.slice(20)}`;

const uuidV4 = () => {
    const bytes = new Uint8Array(16);
    crypto.getRandomValues(bytes);
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    return formatHex(toHex(bytes));
};

const uuidV7 = () => {
    const bytes = new Uint8Array(16);
    crypto.getRandomValues(bytes);
    const ts = Date.now();
    bytes[0] = (ts / 2 ** 40) & 0xff;
    bytes[1] = (ts / 2 ** 32) & 0xff;
    bytes[2] = (ts >>> 24) & 0xff;
    bytes[3] = (ts >>> 16) & 0xff;
    bytes[4] = (ts >>> 8) & 0xff;
    bytes[5] = ts & 0xff;
    bytes[6] = (bytes[6] & 0x0f) | 0x70;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    return formatHex(toHex(bytes));
};

const schema = {
    version: { type: 'enum', values: ['v4', 'v7'], default: 'v4', alias: 'v' },
    count: { type: 'integer', default: 5, min: 1, max: MAX_COUNT },
    uppercase: { type: 'boolean', default: false, alias: 'upper' },
    hyphens: { type: 'boolean', default: true },
    braces: { type: 'boolean', default: false },
};

export default withUrlState(schema, () => ({
    uuids: [],

    init() {
        ['version', 'count'].forEach((prop) => {
            this.$watch(prop, () => this.generate());
        });
        this.generate();
    },

    generate() {
        const n = Math.max(1, Math.min(MAX_COUNT, parseInt(this.count, 10) || 1));
        const fn = this.version === 'v7' ? uuidV7 : uuidV4;
        this.uuids = Array.from({ length: n }, fn);
    },

    format(uuid) {
        let s = this.uppercase ? uuid.toUpperCase() : uuid;
        if (!this.hyphens) s = s.replace(/-/g, '');
        if (this.braces) s = `{${s}}`;
        return s;
    },

    get formattedAll() {
        return this.uuids.map((u) => this.format(u)).join('\n');
    },
}));
