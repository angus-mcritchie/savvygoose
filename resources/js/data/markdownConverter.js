import { marked } from 'marked';
import { markedHighlight } from 'marked-highlight';
import hljs from 'highlight.js/lib/common';
import TurndownService from 'turndown';
import { withUrlState } from '../lib/urlState';

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

const schema = {
    direction: { type: 'enum', values: ['md-to-html', 'html-to-md'], default: 'md-to-html', alias: 'dir' },
    input: { type: 'string', maxLength: 3000 },
};

export default withUrlState(schema, () => ({
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

    async copyPreview() {
        const html = this.preview;
        if (!html) return;
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        const text = tmp.innerText || tmp.textContent || '';

        try {
            await navigator.clipboard.write([
                new ClipboardItem({
                    'text/html': new Blob([html], { type: 'text/html' }),
                    'text/plain': new Blob([text], { type: 'text/plain' }),
                }),
            ]);
        } catch {
            // Fallback for browsers without ClipboardItem support: select a
            // contenteditable node so execCommand('copy') captures rich text.
            const div = document.createElement('div');
            div.contentEditable = 'true';
            div.style.position = 'fixed';
            div.style.left = '-9999px';
            div.style.opacity = '0';
            div.innerHTML = html;
            document.body.appendChild(div);
            const range = document.createRange();
            range.selectNodeContents(div);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
            try { document.execCommand('copy'); } catch {}
            sel.removeAllRanges();
            document.body.removeChild(div);
        }

        this.$store.copy.flash('md-preview');
    },
}));
