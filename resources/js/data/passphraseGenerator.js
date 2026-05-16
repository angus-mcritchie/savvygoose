import { withUrlState } from '../lib/urlState';
import words from './effLargeWordlist';

const SEPARATORS = {
    space: ' ',
    dash: '-',
    underscore: '_',
    dot: '.',
    none: '',
};

const SYMBOLS = '!@#$%^&*?+=';

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

const pickWord = () => words[randomInt(words.length)];

const applyCase = (word, mode) => {
    if (mode === 'first') return word.charAt(0).toUpperCase() + word.slice(1);
    if (mode === 'all') return word.toUpperCase();
    if (mode === 'random') return randomInt(2) === 0 ? word.toUpperCase() : word;
    return word;
};

const schema = {
    words: { type: 'integer', default: 3, min: 2, max: 12, alias: 'w' },
    separator: { type: 'enum', values: ['space', 'dash', 'underscore', 'dot', 'none'], default: 'space', alias: 'sep' },
    capitalize: { type: 'enum', values: ['none', 'first', 'all', 'random'], default: 'none', alias: 'cap' },
    includeNumber: { type: 'boolean', default: false, alias: 'num' },
    includeSymbol: { type: 'boolean', default: false, alias: 'sym' },
};

export default withUrlState(schema, () => ({
    passphrase: '',
    wordlistSize: words.length,

    init() {
        ['words', 'separator', 'capitalize', 'includeNumber', 'includeSymbol'].forEach((prop) => {
            this.$watch(prop, () => this.generate());
        });
        this.generate();
    },

    get separatorChar() {
        return SEPARATORS[this.separator] ?? ' ';
    },

    get entropy() {
        const count = Math.max(2, Math.min(12, parseInt(this.words, 10) || 0));
        let bits = Math.log2(words.length) * count;
        if (this.includeNumber) bits += Math.log2(10);
        if (this.includeSymbol) bits += Math.log2(SYMBOLS.length);
        return Math.round(bits);
    },

    get strength() {
        const bits = this.entropy;
        if (bits < 28) return { label: 'Very weak', score: 1, tone: 'bg-red-500' };
        if (bits < 40) return { label: 'Weak', score: 2, tone: 'bg-orange-500' };
        if (bits < 60) return { label: 'Reasonable', score: 3, tone: 'bg-amber-500' };
        if (bits < 80) return { label: 'Strong', score: 4, tone: 'bg-lime-500' };
        return { label: 'Very strong', score: 5, tone: 'bg-emerald-500' };
    },

    get characterCount() {
        return this.passphrase.length;
    },

    generate() {
        const count = Math.max(2, Math.min(12, parseInt(this.words, 10) || 0));
        const picked = [];
        for (let i = 0; i < count; i++) {
            picked.push(applyCase(pickWord(), this.capitalize));
        }
        let out = picked.join(this.separatorChar);
        if (this.includeNumber) out += randomInt(10).toString();
        if (this.includeSymbol) out += SYMBOLS[randomInt(SYMBOLS.length)];
        this.passphrase = out;
    },
}));
