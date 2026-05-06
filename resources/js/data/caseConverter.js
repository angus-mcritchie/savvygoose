const MAX_URL_INPUT = 3000;

function tokenize(text) {
    return text
        .replace(/([a-z\d])([A-Z])/g, '$1 $2')
        .replace(/([A-Z]+)([A-Z][a-z])/g, '$1 $2')
        .split(/[\s_\-./]+/)
        .filter(Boolean)
        .map((t) => t.toLowerCase());
}

const cap = (s) => (s ? s[0].toUpperCase() + s.slice(1) : s);

export const CONVERTERS = [
    {
        key: 'camel',
        label: 'camelCase',
        description: 'Lowercase first word, capitalize the rest, no separators.',
        convert: (text) => {
            const tokens = tokenize(text);
            if (tokens.length === 0) return '';
            return tokens[0] + tokens.slice(1).map(cap).join('');
        },
    },
    {
        key: 'pascal',
        label: 'PascalCase',
        description: 'Capitalize every word, no separators.',
        convert: (text) => tokenize(text).map(cap).join(''),
    },
    {
        key: 'snake',
        label: 'snake_case',
        description: 'Lowercase words joined with underscores.',
        convert: (text) => tokenize(text).join('_'),
    },
    {
        key: 'kebab',
        label: 'kebab-case',
        description: 'Lowercase words joined with hyphens.',
        convert: (text) => tokenize(text).join('-'),
    },
    {
        key: 'constant',
        label: 'CONSTANT_CASE',
        description: 'Uppercase words joined with underscores.',
        convert: (text) => tokenize(text).map((t) => t.toUpperCase()).join('_'),
    },
    {
        key: 'dot',
        label: 'dot.case',
        description: 'Lowercase words joined with dots.',
        convert: (text) => tokenize(text).join('.'),
    },
    {
        key: 'title',
        label: 'Title Case',
        description: 'Each word capitalized, separated by spaces.',
        convert: (text) => tokenize(text).map(cap).join(' '),
    },
    {
        key: 'sentence',
        label: 'Sentence case',
        description: 'First letter capitalized, the rest lowercase.',
        convert: (text) => {
            const lower = text.toLowerCase();
            return lower.replace(/(^\s*\w|[.!?]\s+\w)/g, (m) => m.toUpperCase());
        },
    },
    {
        key: 'lower',
        label: 'lowercase',
        description: 'All characters lowercase.',
        convert: (text) => text.toLowerCase(),
    },
    {
        key: 'upper',
        label: 'UPPERCASE',
        description: 'All characters uppercase.',
        convert: (text) => text.toUpperCase(),
    },
];

export default () => ({
    text: '',
    copiedKey: null,
    converters: CONVERTERS,
    url: window.location.href,
    urlTooLong: false,

    init() {
        this.initFromUrl();
        this.$watch('text', () => this.updateUrl());
        this.updateUrl();
    },

    convert(key) {
        const c = CONVERTERS.find((c) => c.key === key);
        return c ? c.convert(this.text) : '';
    },

    async copy(key) {
        const value = this.convert(key);
        if (!value) return;
        await navigator.clipboard.writeText(value);
        this.copiedKey = key;
        setTimeout(() => {
            if (this.copiedKey === key) this.copiedKey = null;
        }, 1500);
    },

    clear() {
        this.text = '';
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('text')) this.text = params.get('text');
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
        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
