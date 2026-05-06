const DEFAULTS = {
    version: 'v4',
    count: 5,
    uppercase: false,
    hyphens: true,
    braces: false,
};

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

export default () => ({
    version: DEFAULTS.version,
    count: DEFAULTS.count,
    uppercase: DEFAULTS.uppercase,
    hyphens: DEFAULTS.hyphens,
    braces: DEFAULTS.braces,
    uuids: [],
    copied: false,
    copiedIndex: null,
    url: window.location.href,

    init() {
        this.initFromUrl();

        ['version', 'count'].forEach((prop) => {
            this.$watch(prop, () => {
                this.updateUrl();
                this.generate();
            });
        });

        ['uppercase', 'hyphens', 'braces'].forEach((prop) => {
            this.$watch(prop, () => this.updateUrl());
        });

        this.updateUrl();
        this.generate();
    },

    generate() {
        const n = Math.max(1, Math.min(MAX_COUNT, parseInt(this.count, 10) || 1));
        const fn = this.version === 'v7' ? uuidV7 : uuidV4;
        this.uuids = Array.from({ length: n }, fn);
        this.copied = false;
        this.copiedIndex = null;
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

    async copyOne(index) {
        const u = this.uuids[index];
        if (!u) return;
        await navigator.clipboard.writeText(this.format(u));
        this.copiedIndex = index;
        setTimeout(() => {
            if (this.copiedIndex === index) this.copiedIndex = null;
        }, 1500);
    },

    async copyAll() {
        if (!this.uuids.length) return;
        await navigator.clipboard.writeText(this.formattedAll);
        this.copied = true;
        setTimeout(() => (this.copied = false), 1500);
    },

    download() {
        if (!this.uuids.length) return;
        const blob = new Blob([this.formattedAll + '\n'], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `uuids-${this.version}-${this.uuids.length}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);

        if (params.has('v')) {
            const v = params.get('v').toLowerCase();
            if (v === 'v4' || v === 'v7') this.version = v;
        }

        if (params.has('count')) {
            const n = parseInt(params.get('count'), 10);
            if (Number.isFinite(n) && n >= 1 && n <= MAX_COUNT) this.count = n;
        }

        const flag = (key, prop) => {
            if (!params.has(key)) return;
            const v = params.get(key);
            if (v === '1') this[prop] = true;
            if (v === '0') this[prop] = false;
        };

        flag('upper', 'uppercase');
        flag('hyphens', 'hyphens');
        flag('braces', 'braces');
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        if (this.version !== DEFAULTS.version) params.set('v', this.version);
        else params.delete('v');

        if (parseInt(this.count, 10) !== DEFAULTS.count) params.set('count', this.count);
        else params.delete('count');

        const setFlag = (key, val, def) => {
            if (val === def) params.delete(key);
            else params.set(key, val ? '1' : '0');
        };

        setFlag('upper', this.uppercase, DEFAULTS.uppercase);
        setFlag('hyphens', this.hyphens, DEFAULTS.hyphens);
        setFlag('braces', this.braces, DEFAULTS.braces);

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
