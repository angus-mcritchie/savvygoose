import { withUrlState } from '../lib/urlState';

const MAX_URL_INPUT = 3000;

const STOP_WORDS = new Set([
    'a', 'an', 'and', 'as', 'at', 'be', 'by', 'for', 'from',
    'in', 'is', 'it', 'of', 'on', 'or', 'the', 'to', 'with',
]);

const COMBINING_MARKS = /[̀-ͯ]/g;

function slugify(text, opts) {
    if (!text) return '';

    let result = text.normalize('NFKD').replace(COMBINING_MARKS, '');

    if (opts.lowercase) {
        result = result.toLowerCase();
    }

    const sep = opts.separator;
    const sepEsc = sep.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

    result = result.replace(/[^a-zA-Z0-9]+/g, sep);

    if (opts.stripStopWords) {
        const parts = result.split(new RegExp(sepEsc, 'g')).filter(Boolean);
        const filtered = parts.filter((p) => !STOP_WORDS.has(p.toLowerCase()));
        result = (filtered.length > 0 ? filtered : parts).join(sep);
    }

    const trimmer = new RegExp(`^(?:${sepEsc})+|(?:${sepEsc})+$`, 'g');
    result = result.replace(trimmer, '');

    if (opts.maxLength > 0 && result.length > opts.maxLength) {
        result = result.slice(0, opts.maxLength);
        result = result.replace(new RegExp(`(?:${sepEsc})+$`), '');
    }

    return result;
}

const schema = {
    text: { type: 'string', maxLength: MAX_URL_INPUT },
    separator: { type: 'enum', values: ['-', '_', '.'], default: '-', alias: 'sep' },
    maxLength: { type: 'integer', default: 0, min: 0, max: 200, alias: 'max' },
    lowercase: { type: 'boolean', default: true, alias: 'lc' },
    stripStopWords: { type: 'boolean', default: false, alias: 'sw' },
};

export default withUrlState(schema, () => ({
    get slug() {
        return slugify(this.text, {
            separator: this.separator || '-',
            maxLength: parseInt(this.maxLength, 10) || 0,
            lowercase: this.lowercase,
            stripStopWords: this.stripStopWords,
        });
    },

    clear() {
        this.text = '';
    },
}));
