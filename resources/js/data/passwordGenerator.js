const DEFAULTS = {
    length: 20,
    lower: true,
    upper: true,
    digits: true,
    symbols: true,
    excludeSimilar: false,
    excludeAmbiguous: false,
};

const CHARSETS = {
    lower: 'abcdefghijklmnopqrstuvwxyz',
    upper: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    digits: '0123456789',
    symbols: '!@#$%^&*()-_=+[]{}|;:,.<>/?',
};

const SIMILAR_CHARS = '1lI0Oo';
const AMBIGUOUS_CHARS = '{}[]()/\\\'"`~,;:.<>';

const strip = (str, banned) => {
    const set = new Set(banned);
    return Array.from(str).filter((c) => !set.has(c)).join('');
};

const randomInt = (max) => {
    const limit = Math.floor(0x100000000 / max) * max;
    const buf = new Uint32Array(1);
    let n;
    do {
        crypto.getRandomValues(buf);
        n = buf[0];
    } while (n >= limit);
    return n % max;
};

export default () => ({
    length: DEFAULTS.length,
    lower: DEFAULTS.lower,
    upper: DEFAULTS.upper,
    digits: DEFAULTS.digits,
    symbols: DEFAULTS.symbols,
    excludeSimilar: DEFAULTS.excludeSimilar,
    excludeAmbiguous: DEFAULTS.excludeAmbiguous,
    password: '',
    copied: false,
    url: window.location.href,

    init() {
        this.initFromUrl();

        ['length', 'lower', 'upper', 'digits', 'symbols', 'excludeSimilar', 'excludeAmbiguous'].forEach((prop) => {
            this.$watch(prop, () => {
                this.updateUrl();
                this.generate();
            });
        });

        this.updateUrl();
        this.generate();
    },

    get charset() {
        let chars = '';
        if (this.lower) chars += CHARSETS.lower;
        if (this.upper) chars += CHARSETS.upper;
        if (this.digits) chars += CHARSETS.digits;
        if (this.symbols) chars += CHARSETS.symbols;
        if (this.excludeSimilar) chars = strip(chars, SIMILAR_CHARS);
        if (this.excludeAmbiguous) chars = strip(chars, AMBIGUOUS_CHARS);
        return chars;
    },

    get hasCharset() {
        return this.charset.length > 0;
    },

    get entropy() {
        if (!this.hasCharset || !this.length) return 0;
        return Math.round(this.length * Math.log2(this.charset.length));
    },

    get strength() {
        const bits = this.entropy;
        if (bits < 28) return { label: 'Very weak', score: 1, tone: 'bg-red-500' };
        if (bits < 40) return { label: 'Weak', score: 2, tone: 'bg-orange-500' };
        if (bits < 60) return { label: 'Reasonable', score: 3, tone: 'bg-amber-500' };
        if (bits < 80) return { label: 'Strong', score: 4, tone: 'bg-lime-500' };
        return { label: 'Very strong', score: 5, tone: 'bg-emerald-500' };
    },

    generate() {
        if (!this.hasCharset) {
            this.password = '';
            return;
        }

        const len = Math.max(4, Math.min(128, parseInt(this.length, 10) || 0));
        const chars = this.charset;
        let out = '';

        const required = [];
        if (this.lower) required.push(strip(CHARSETS.lower, this.excludeSimilar ? SIMILAR_CHARS : ''));
        if (this.upper) required.push(strip(CHARSETS.upper, this.excludeSimilar ? SIMILAR_CHARS : ''));
        if (this.digits) required.push(strip(CHARSETS.digits, this.excludeSimilar ? SIMILAR_CHARS : ''));
        if (this.symbols) required.push(strip(strip(CHARSETS.symbols, this.excludeAmbiguous ? AMBIGUOUS_CHARS : ''), ''));

        const picked = required
            .filter((g) => g.length > 0 && len > out.length)
            .map((g) => g[randomInt(g.length)]);

        const remaining = len - picked.length;
        for (let i = 0; i < remaining; i++) {
            out += chars[randomInt(chars.length)];
        }
        out += picked.join('');

        const arr = Array.from(out);
        for (let i = arr.length - 1; i > 0; i--) {
            const j = randomInt(i + 1);
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }

        this.password = arr.join('').slice(0, len);
        this.copied = false;
    },

    async copy() {
        if (!this.password) return;
        await navigator.clipboard.writeText(this.password);
        this.copied = true;
        setTimeout(() => (this.copied = false), 1500);
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);

        if (params.has('len')) {
            const n = parseInt(params.get('len'), 10);
            if (Number.isFinite(n) && n >= 4 && n <= 128) this.length = n;
        }

        const flag = (key, prop) => {
            if (!params.has(key)) return;
            const v = params.get(key);
            if (v === '1') this[prop] = true;
            if (v === '0') this[prop] = false;
        };

        flag('lower', 'lower');
        flag('upper', 'upper');
        flag('digits', 'digits');
        flag('symbols', 'symbols');
        flag('noSim', 'excludeSimilar');
        flag('noAmb', 'excludeAmbiguous');
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        const setFlag = (key, val, def) => {
            if (val === def) params.delete(key);
            else params.set(key, val ? '1' : '0');
        };

        if (parseInt(this.length, 10) !== DEFAULTS.length) {
            params.set('len', this.length);
        } else {
            params.delete('len');
        }

        setFlag('lower', this.lower, DEFAULTS.lower);
        setFlag('upper', this.upper, DEFAULTS.upper);
        setFlag('digits', this.digits, DEFAULTS.digits);
        setFlag('symbols', this.symbols, DEFAULTS.symbols);
        setFlag('noSim', this.excludeSimilar, DEFAULTS.excludeSimilar);
        setFlag('noAmb', this.excludeAmbiguous, DEFAULTS.excludeAmbiguous);

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
