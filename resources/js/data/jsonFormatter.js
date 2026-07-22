import { withUrlState } from '../lib/urlState';

const MAX_URL_INPUT = 3000;
const SAMPLE = JSON.stringify(
    {
        name: 'SavvyGoose',
        founded: 2025,
        free: true,
        tools: ['barcode', 'qr-code', 'json'],
        meta: { stars: 5, tags: ['utility', 'tools'] },
    },
    null,
    2,
);

// Re-serialize JSON while preserving number and string literals verbatim.
// JSON.parse + JSON.stringify silently rounds integers past 2^53 and huge
// exponents through a JS double (e.g. a Discord/Twitter snowflake id or 1e400),
// which is unacceptable for a formatter. The input is validated by JSON.parse
// first, so this tokenizer only ever runs over known-valid JSON.
function tokenizeJson(text) {
    const tokens = [];
    let i = 0;
    const n = text.length;
    while (i < n) {
        const c = text[i];
        if (c === ' ' || c === '\t' || c === '\n' || c === '\r') { i++; continue; }
        if (c === '{' || c === '}' || c === '[' || c === ']' || c === ':' || c === ',') {
            tokens.push({ t: c });
            i++;
            continue;
        }
        if (c === '"') {
            let j = i + 1;
            while (j < n) {
                if (text[j] === '\\') { j += 2; continue; }
                if (text[j] === '"') { j++; break; }
                j++;
            }
            tokens.push({ t: 'lit', v: text.slice(i, j) });
            i = j;
            continue;
        }
        // number or true/false/null — copy the literal run verbatim
        let j = i;
        while (j < n && ' \t\n\r{}[]:,"'.indexOf(text[j]) === -1) j++;
        tokens.push({ t: 'lit', v: text.slice(i, j) });
        i = j;
    }
    return tokens;
}

export function reformatJson(text, indentUnit) {
    const minified = indentUnit === '';
    const tokens = tokenizeJson(text);
    let out = '';
    let depth = 0;
    const newline = () => (minified ? '' : '\n' + indentUnit.repeat(depth));
    for (let k = 0; k < tokens.length; k++) {
        const tok = tokens[k];
        const next = tokens[k + 1];
        if (tok.t === '{' || tok.t === '[') {
            out += tok.t;
            if (next && (next.t === '}' || next.t === ']')) {
                out += next.t;
                k++;
            } else {
                depth++;
                out += newline();
            }
        } else if (tok.t === '}' || tok.t === ']') {
            depth--;
            out += newline() + tok.t;
        } else if (tok.t === ':') {
            out += minified ? ':' : ': ';
        } else if (tok.t === ',') {
            out += ',' + newline();
        } else {
            out += tok.v;
        }
    }
    return out;
}

function positionToLineCol(text, position) {
    let line = 1;
    let col = 1;
    const limit = Math.min(position, text.length);
    for (let i = 0; i < limit; i++) {
        if (text[i] === '\n') {
            line += 1;
            col = 1;
        } else {
            col += 1;
        }
    }
    return { line, col };
}

const schema = {
    input: { type: 'string', maxLength: MAX_URL_INPUT },
    mode: { type: 'enum', values: ['pretty', 'minified'], default: 'pretty' },
    indent: { type: 'enum', values: ['2', '4', 'tab'], default: '2' },
};

export default withUrlState(schema, () => ({
    output: '',
    error: null,

    init() {
        ['input', 'mode', 'indent'].forEach((prop) => {
            this.$watch(prop, () => this.compute());
        });
        this.compute();
    },

    compute() {
        const text = this.input;
        if (!text.trim()) {
            this.output = '';
            this.error = null;
            return;
        }

        try {
            JSON.parse(text); // validate + surface error positions below
            this.error = null;
            const indentUnit =
                this.mode === 'minified'
                    ? ''
                    : this.indent === 'tab'
                        ? '\t'
                        : ' '.repeat(Number(this.indent));
            this.output = reformatJson(text, indentUnit);
        } catch (e) {
            this.output = '';
            const message = e.message || 'Invalid JSON';
            const posMatch = message.match(/position (\d+)/i);
            if (posMatch) {
                const pos = parseInt(posMatch[1], 10);
                const { line, col } = positionToLineCol(text, pos);
                const snippet = text.split('\n')[line - 1] || '';
                this.error = { message, line, col, snippet };
            } else {
                this.error = { message };
            }
        }
    },

    loadSample() {
        this.input = SAMPLE;
    },

    clear() {
        this.input = '';
    },

    swapToOutput() {
        if (!this.output) return;
        this.input = this.output;
    },

    get inputBytes() {
        return new Blob([this.input]).size;
    },
    get outputBytes() {
        return new Blob([this.output]).size;
    },
    get savings() {
        if (!this.input || !this.output) return 0;
        const before = this.inputBytes;
        const after = this.outputBytes;
        if (before === 0) return 0;
        return Math.round(((before - after) / before) * 100);
    },
}));
