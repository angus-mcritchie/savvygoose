import { withUrlState } from '../lib/urlState';

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

const schema = {
    length: { type: 'integer', default: 20, min: 4, max: 128, alias: 'len' },
    lower: { type: 'boolean', default: true },
    upper: { type: 'boolean', default: true },
    digits: { type: 'boolean', default: true },
    symbols: { type: 'boolean', default: true },
    excludeSimilar: { type: 'boolean', default: false, alias: 'noSim' },
    excludeAmbiguous: { type: 'boolean', default: false, alias: 'noAmb' },
};

export default withUrlState(schema, () => ({
    password: '',

    init() {
        ['length', 'lower', 'upper', 'digits', 'symbols', 'excludeSimilar', 'excludeAmbiguous'].forEach((prop) => {
            this.$watch(prop, () => this.generate());
        });
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
    },
}));
