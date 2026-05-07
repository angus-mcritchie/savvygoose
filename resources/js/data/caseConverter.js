import { withUrlState } from '../lib/urlState';

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

const schema = {
    text: { type: 'string', maxLength: 3000 },
};

export default withUrlState(schema, () => ({
    converters: CONVERTERS,

    convert(key) {
        const c = CONVERTERS.find((c) => c.key === key);
        return c ? c.convert(this.text) : '';
    },

    clear() {
        this.text = '';
    },
}));
