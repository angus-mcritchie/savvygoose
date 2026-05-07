import { withUrlState } from '../lib/urlState';

const ALL_FLAGS = ['g', 'i', 'm', 's', 'u', 'y'];
const MAX_URL_INPUT = 2000;
const MAX_MATCHES = 1000;

function escapeHtml(s) {
    return s
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function findMatches(text, regex) {
    const out = [];
    if (!regex || !text) return out;

    const isGlobal = regex.flags.includes('g');
    const re = new RegExp(regex.source, regex.flags.includes('g') ? regex.flags : regex.flags + 'g');

    let m;
    while ((m = re.exec(text)) !== null) {
        out.push({
            match: m[0],
            index: m.index,
            length: m[0].length,
            groups: m.slice(1),
            named: m.groups ? { ...m.groups } : null,
        });
        if (m[0].length === 0) re.lastIndex += 1;
        if (out.length >= MAX_MATCHES) break;
        if (!isGlobal) break;
    }
    return out;
}

const totalInputTooLong = (state) => {
    const total = (state.pattern?.length || 0)
        + (state.test?.length || 0)
        + (state.replacement?.length || 0);
    return total > MAX_URL_INPUT;
};

const schema = {
    pattern: {
        type: 'string',
        alias: 'p',
        serialize: (value) => (value ? { value } : { skip: true }),
    },
    flags: {
        type: 'string',
        default: 'g',
        alias: 'f',
        parse: (raw) => raw.split('').filter((c) => ALL_FLAGS.includes(c)).join(''),
    },
    test: {
        type: 'string',
        alias: 't',
        serialize: (value, state) => {
            if (!value) return { skip: true };
            if (totalInputTooLong(state)) return { skip: true, tooLong: true };
            return { value };
        },
    },
    replacement: {
        type: 'string',
        alias: 'r',
        serialize: (value, state) => {
            if (!value || !state.replaceMode) return { skip: true };
            if (totalInputTooLong(state)) return { skip: true, tooLong: true };
            return { value };
        },
    },
    replaceMode: { type: 'boolean', default: false, alias: 'rm' },
};

export default withUrlState(schema, () => ({
    error: null,
    allFlags: ALL_FLAGS,
    flagDescriptions: {
        g: 'global, match all occurrences',
        i: 'ignore case',
        m: 'multiline (^ and $ match line boundaries)',
        s: 'dotall (. matches newlines)',
        u: 'unicode',
        y: 'sticky (match at lastIndex only)',
    },

    initFromUrl() {
        if (this.replacement) this.replaceMode = true;
    },

    get regex() {
        if (!this.pattern) {
            this.error = null;
            return null;
        }
        try {
            const re = new RegExp(this.pattern, this.flags);
            this.error = null;
            return re;
        } catch (e) {
            this.error = e.message;
            return null;
        }
    },

    get matches() {
        if (!this.regex) return [];
        return findMatches(this.test, this.regex);
    },

    get matchCount() {
        return this.matches.length;
    },

    get highlighted() {
        if (!this.regex || !this.test) return '';
        const matches = this.matches;
        if (matches.length === 0) return escapeHtml(this.test);

        const parts = [];
        let last = 0;
        for (const m of matches) {
            if (m.index > last) parts.push(escapeHtml(this.test.slice(last, m.index)));
            const display = m.match.length === 0 ? '∅' : escapeHtml(m.match);
            parts.push(`<mark class="rounded bg-amber-300/70 px-0.5 text-zinc-900 dark:bg-amber-400/70">${display}</mark>`);
            last = m.index + (m.length || 0);
        }
        if (last < this.test.length) parts.push(escapeHtml(this.test.slice(last)));
        return parts.join('');
    },

    get replaceResult() {
        if (!this.regex || !this.test) return '';
        try {
            return this.test.replace(this.regex, this.replacement);
        } catch {
            return '';
        }
    },

    toggleFlag(flag) {
        if (this.flags.includes(flag)) {
            this.flags = this.flags.replace(flag, '');
        } else {
            this.flags = this.flags + flag;
        }
    },

    hasFlag(flag) {
        return this.flags.includes(flag);
    },

    clear() {
        this.pattern = '';
        this.test = '';
        this.replacement = '';
    },
}));
