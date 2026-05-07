const DEFAULTS = {
    pattern: '',
    flags: 'g',
    test: '',
    replacement: '',
    replaceMode: false,
};

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

export default () => ({
    pattern: DEFAULTS.pattern,
    flags: DEFAULTS.flags,
    test: DEFAULTS.test,
    replacement: DEFAULTS.replacement,
    replaceMode: DEFAULTS.replaceMode,
    error: null,
    url: window.location.href,
    urlTooLong: false,
    allFlags: ALL_FLAGS,
    flagDescriptions: {
        g: 'global, match all occurrences',
        i: 'ignore case',
        m: 'multiline (^ and $ match line boundaries)',
        s: 'dotall (. matches newlines)',
        u: 'unicode',
        y: 'sticky (match at lastIndex only)',
    },

    init() {
        this.initFromUrl();
        ['pattern', 'flags', 'test', 'replacement', 'replaceMode'].forEach((p) =>
            this.$watch(p, () => this.updateUrl()),
        );
        this.updateUrl();
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

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('p')) this.pattern = params.get('p');
        if (params.has('f')) {
            const f = params.get('f').split('').filter((c) => ALL_FLAGS.includes(c)).join('');
            this.flags = f;
        }
        if (params.has('t')) this.test = params.get('t');
        if (params.has('r')) {
            this.replacement = params.get('r');
            this.replaceMode = true;
        }
        if (params.has('rm')) this.replaceMode = params.get('rm') === '1';
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        const totalLen = (this.pattern?.length || 0) + (this.test?.length || 0) + (this.replacement?.length || 0);

        if (this.pattern) params.set('p', this.pattern);
        else params.delete('p');

        if (this.flags !== DEFAULTS.flags) params.set('f', this.flags);
        else params.delete('f');

        if (totalLen <= MAX_URL_INPUT) {
            if (this.test) params.set('t', this.test);
            else params.delete('t');
            if (this.replaceMode && this.replacement) params.set('r', this.replacement);
            else params.delete('r');
            this.urlTooLong = false;
        } else {
            params.delete('t');
            params.delete('r');
            this.urlTooLong = true;
        }

        if (this.replaceMode !== DEFAULTS.replaceMode) params.set('rm', this.replaceMode ? '1' : '0');
        else params.delete('rm');

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
