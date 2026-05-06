import { marked } from 'marked';
import { markedHighlight } from 'marked-highlight';
import hljs from 'highlight.js/lib/common';
import TurndownService from 'turndown';

marked.use(
    markedHighlight({
        emptyLangClass: 'hljs',
        langPrefix: 'hljs language-',
        highlight(code, lang) {
            const language = hljs.getLanguage(lang) ? lang : 'plaintext';
            return hljs.highlight(code, { language }).value;
        },
    }),
);
marked.setOptions({ gfm: true, breaks: false });

const turndown = new TurndownService({
    headingStyle: 'atx',
    codeBlockStyle: 'fenced',
    bulletListMarker: '-',
    emDelimiter: '_',
});

export default () => ({
    direction: 'md-to-html',
    input: '',
    copiedInput: false,
    copiedOutput: false,

    get output() {
        if (!this.input) return '';
        try {
            return this.direction === 'md-to-html'
                ? marked.parse(this.input)
                : turndown.turndown(this.input);
        } catch (e) {
            return '';
        }
    },

    get preview() {
        if (!this.input) return '';
        try {
            return this.direction === 'md-to-html'
                ? this.output
                : marked.parse(this.output);
        } catch (e) {
            return '';
        }
    },

    get inputLabel() {
        return this.direction === 'md-to-html' ? 'Markdown' : 'HTML';
    },
    get outputLabel() {
        return this.direction === 'md-to-html' ? 'HTML' : 'Markdown';
    },
    get inputPlaceholder() {
        return this.direction === 'md-to-html'
            ? '# Hello world\n\nType **markdown** here.'
            : '<h1>Hello world</h1>\n<p>Paste <strong>HTML</strong> here.</p>';
    },

    swap() {
        const swapped = this.output;
        this.direction = this.direction === 'md-to-html' ? 'html-to-md' : 'md-to-html';
        this.input = swapped;
    },

    clear() {
        this.input = '';
    },

    async copyInput() {
        if (!this.input) return;
        await navigator.clipboard.writeText(this.input);
        this.copiedInput = true;
        setTimeout(() => (this.copiedInput = false), 1500);
    },
    async copyOutput() {
        if (!this.output) return;
        await navigator.clipboard.writeText(this.output);
        this.copiedOutput = true;
        setTimeout(() => (this.copiedOutput = false), 1500);
    },
});
