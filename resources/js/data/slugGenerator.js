const DEFAULTS = {
    separator: '-',
    maxLength: 0,
    lowercase: true,
    stripStopWords: false,
};

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

export default () => ({
    text: '',
    separator: DEFAULTS.separator,
    maxLength: DEFAULTS.maxLength,
    lowercase: DEFAULTS.lowercase,
    stripStopWords: DEFAULTS.stripStopWords,
    copied: false,
    url: window.location.href,
    urlTooLong: false,

    init() {
        this.initFromUrl();
        ['text', 'separator', 'maxLength', 'lowercase', 'stripStopWords'].forEach((p) =>
            this.$watch(p, () => this.updateUrl()),
        );
        this.updateUrl();
    },

    get slug() {
        return slugify(this.text, {
            separator: this.separator || '-',
            maxLength: parseInt(this.maxLength, 10) || 0,
            lowercase: this.lowercase,
            stripStopWords: this.stripStopWords,
        });
    },

    async copy() {
        if (!this.slug) return;
        await navigator.clipboard.writeText(this.slug);
        this.copied = true;
        setTimeout(() => (this.copied = false), 1500);
    },

    clear() {
        this.text = '';
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('text')) this.text = params.get('text');
        if (params.has('sep')) {
            const s = params.get('sep');
            if (['-', '_', '.'].includes(s)) this.separator = s;
        }
        if (params.has('max')) {
            const n = parseInt(params.get('max'), 10);
            if (Number.isFinite(n) && n >= 0 && n <= 200) this.maxLength = n;
        }
        if (params.has('lc')) this.lowercase = params.get('lc') === '1';
        if (params.has('sw')) this.stripStopWords = params.get('sw') === '1';
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        if (this.text && this.text.length <= MAX_URL_INPUT) {
            params.set('text', this.text);
            this.urlTooLong = false;
        } else {
            params.delete('text');
            this.urlTooLong = this.text.length > MAX_URL_INPUT;
        }

        if (this.separator !== DEFAULTS.separator) params.set('sep', this.separator);
        else params.delete('sep');

        const max = parseInt(this.maxLength, 10) || 0;
        if (max !== DEFAULTS.maxLength) params.set('max', String(max));
        else params.delete('max');

        if (this.lowercase !== DEFAULTS.lowercase) params.set('lc', this.lowercase ? '1' : '0');
        else params.delete('lc');

        if (this.stripStopWords !== DEFAULTS.stripStopWords) params.set('sw', this.stripStopWords ? '1' : '0');
        else params.delete('sw');

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
