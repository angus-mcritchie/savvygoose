import { diffLines, diffWordsWithSpace } from 'diff';
import { withUrlState } from '../lib/urlState';

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

const serializeIfShortEnough = (value, state) => {
    if (!value) return { skip: true };
    const total = (state.original?.length || 0) + (state.modified?.length || 0);
    if (total > MAX_URL_INPUT) return { skip: true, tooLong: true };
    return { value };
};

const schema = {
    original: { type: 'string', alias: 'a', serialize: serializeIfShortEnough },
    modified: { type: 'string', alias: 'b', serialize: serializeIfShortEnough },
    mode: { type: 'enum', values: ['side-by-side', 'unified', 'word'], default: 'side-by-side' },
    ignoreWhitespace: { type: 'boolean', default: false, alias: 'iw' },
};

export default withUrlState(schema, () => ({
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
}));
