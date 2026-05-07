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
            const parsed = JSON.parse(text);
            this.error = null;
            const indent = this.indent === 'tab' ? '\t' : Number(this.indent);
            this.output =
                this.mode === 'minified'
                    ? JSON.stringify(parsed)
                    : JSON.stringify(parsed, null, indent);
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
