import { diffLines, diffWordsWithSpace } from 'diff';

const DEFAULTS = {
    mode: 'side-by-side',
    ignoreWhitespace: false,
};

const MAX_URL_INPUT = 2500;

function splitLines(value) {
    const lines = value.split('\n');
    if (lines.length > 0 && lines[lines.length - 1] === '') lines.pop();
    return lines;
}

function buildSideBySide(parts) {
    const left = [];
    const right = [];
    let leftLine = 0;
    let rightLine = 0;
    let pendingRemoved = [];

    const flushPending = () => {
        for (const text of pendingRemoved) {
            leftLine += 1;
            left.push({ type: 'removed', text, num: leftLine });
            right.push({ type: 'empty', text: '', num: null });
        }
        pendingRemoved = [];
    };

    for (const part of parts) {
        const lines = splitLines(part.value);
        if (lines.length === 0) continue;

        if (part.removed) {
            pendingRemoved.push(...lines);
        } else if (part.added) {
            const removed = pendingRemoved;
            pendingRemoved = [];
            const pairs = Math.min(removed.length, lines.length);
            for (let i = 0; i < pairs; i++) {
                leftLine += 1;
                rightLine += 1;
                left.push({ type: 'removed', text: removed[i], num: leftLine });
                right.push({ type: 'added', text: lines[i], num: rightLine });
            }
            if (removed.length > lines.length) {
                for (let i = pairs; i < removed.length; i++) {
                    leftLine += 1;
                    left.push({ type: 'removed', text: removed[i], num: leftLine });
                    right.push({ type: 'empty', text: '', num: null });
                }
            } else if (lines.length > removed.length) {
                for (let i = pairs; i < lines.length; i++) {
                    rightLine += 1;
                    left.push({ type: 'empty', text: '', num: null });
                    right.push({ type: 'added', text: lines[i], num: rightLine });
                }
            }
        } else {
            flushPending();
            for (const text of lines) {
                leftLine += 1;
                rightLine += 1;
                left.push({ type: 'context', text, num: leftLine });
                right.push({ type: 'context', text, num: rightLine });
            }
        }
    }
    flushPending();
    return { left, right };
}

function buildUnified(parts) {
    const rows = [];
    let leftLine = 0;
    let rightLine = 0;

    for (const part of parts) {
        const lines = splitLines(part.value);
        for (const text of lines) {
            if (part.added) {
                rightLine += 1;
                rows.push({ type: 'added', text, leftNum: null, rightNum: rightLine });
            } else if (part.removed) {
                leftLine += 1;
                rows.push({ type: 'removed', text, leftNum: leftLine, rightNum: null });
            } else {
                leftLine += 1;
                rightLine += 1;
                rows.push({ type: 'context', text, leftNum: leftLine, rightNum: rightLine });
            }
        }
    }
    return rows;
}

function buildWordDiff(original, modified) {
    return diffWordsWithSpace(original, modified).map((part) => ({
        type: part.added ? 'added' : part.removed ? 'removed' : 'context',
        text: part.value,
    }));
}

export default () => ({
    original: '',
    modified: '',
    mode: DEFAULTS.mode,
    ignoreWhitespace: DEFAULTS.ignoreWhitespace,
    url: window.location.href,
    urlTooLong: false,

    init() {
        this.initFromUrl();
        ['original', 'modified', 'mode', 'ignoreWhitespace'].forEach((prop) => {
            this.$watch(prop, () => this.updateUrl());
        });
        this.updateUrl();
    },

    get parts() {
        if (!this.original && !this.modified) return [];
        return diffLines(this.original, this.modified, {
            ignoreWhitespace: this.ignoreWhitespace,
            newlineIsToken: false,
        });
    },

    get sideBySide() {
        return buildSideBySide(this.parts);
    },

    get unified() {
        return buildUnified(this.parts);
    },

    get wordDiff() {
        return buildWordDiff(this.original, this.modified);
    },

    get stats() {
        let added = 0;
        let removed = 0;
        let unchanged = 0;
        for (const part of this.parts) {
            const count = splitLines(part.value).length;
            if (part.added) added += count;
            else if (part.removed) removed += count;
            else unchanged += count;
        }
        return { added, removed, unchanged };
    },

    get hasContent() {
        return Boolean(this.original || this.modified);
    },

    get hasChanges() {
        return this.parts.some((p) => p.added || p.removed);
    },

    swap() {
        const tmp = this.original;
        this.original = this.modified;
        this.modified = tmp;
    },

    clear() {
        this.original = '';
        this.modified = '';
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('a')) this.original = params.get('a');
        if (params.has('b')) this.modified = params.get('b');
        if (params.has('mode') && ['side-by-side', 'unified', 'word'].includes(params.get('mode'))) {
            this.mode = params.get('mode');
        }
        if (params.has('iw')) this.ignoreWhitespace = params.get('iw') === '1';
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);
        const totalLength = (this.original?.length || 0) + (this.modified?.length || 0);

        if (totalLength <= MAX_URL_INPUT && totalLength > 0) {
            if (this.original) params.set('a', this.original);
            else params.delete('a');
            if (this.modified) params.set('b', this.modified);
            else params.delete('b');
            this.urlTooLong = false;
        } else {
            params.delete('a');
            params.delete('b');
            this.urlTooLong = totalLength > MAX_URL_INPUT;
        }

        if (this.mode !== DEFAULTS.mode) params.set('mode', this.mode);
        else params.delete('mode');

        if (this.ignoreWhitespace) params.set('iw', '1');
        else params.delete('iw');

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
