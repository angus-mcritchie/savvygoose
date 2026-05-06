const DEFAULTS = {
    mode: 'pretty',
    indent: '2',
};

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

export default () => ({
    input: '',
    mode: DEFAULTS.mode,
    indent: DEFAULTS.indent,
    output: '',
    error: null,
    copied: false,
    url: window.location.href,
    urlTooLong: false,

    init() {
        this.initFromUrl();
        ['input', 'mode', 'indent'].forEach((prop) => {
            this.$watch(prop, () => {
                this.compute();
                this.updateUrl();
            });
        });
        this.compute();
        this.updateUrl();
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

    async copyOutput() {
        if (!this.output) return;
        await navigator.clipboard.writeText(this.output);
        this.copied = true;
        setTimeout(() => (this.copied = false), 1500);
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

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('input')) {
            this.input = params.get('input');
        }
        if (params.has('mode') && ['pretty', 'minified'].includes(params.get('mode'))) {
            this.mode = params.get('mode');
        }
        if (params.has('indent') && ['2', '4', 'tab'].includes(params.get('indent'))) {
            this.indent = params.get('indent');
        }
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        if (this.input && this.input.length <= MAX_URL_INPUT) {
            params.set('input', this.input);
            this.urlTooLong = false;
        } else {
            params.delete('input');
            this.urlTooLong = this.input.length > MAX_URL_INPUT;
        }

        if (this.mode !== DEFAULTS.mode) {
            params.set('mode', this.mode);
        } else {
            params.delete('mode');
        }

        if (this.indent !== DEFAULTS.indent) {
            params.set('indent', this.indent);
        } else {
            params.delete('indent');
        }

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
