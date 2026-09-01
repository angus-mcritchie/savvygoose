import { marked } from 'marked';
import { markedHighlight } from 'marked-highlight';
import hljs from 'highlight.js/lib/common';
import TurndownService from 'turndown';
import DOMPurify from 'dompurify';
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

// Pull every blockquote out of a marked token tree. `token.text` is the quote
// with one level of `>` markers already stripped, which is what someone pasting
// an LLM answer wants: the draft it wrapped in a quote, minus the wrapper.
function collectQuotes(tokens, out = []) {
    for (const token of tokens) {
        if (token.type === 'blockquote') {
            const markdown = token.text.trim();
            if (markdown) out.push(markdown);
            // A quote nested inside this one is part of it, not a sibling.
            continue;
        }
        if (token.type === 'list') {
            for (const item of token.items) collectQuotes(item.tokens ?? [], out);
        }
    }

    return out;
}

const schema = {
    direction: { type: 'enum', values: ['md-to-html', 'html-to-md'], default: 'md-to-html', alias: 'dir' },
    input: { type: 'string', maxLength: 3000 },
};

export default withUrlState(schema, () => ({
    init() {
        this.focusInput();
    },

    focusInput() {
        // Someone arriving on a shared ?input= link came to read the result, and
        // on touch devices stealing focus just throws up the keyboard.
        if (this.input) return;
        if (window.matchMedia?.('(pointer: coarse)').matches) return;

        this.$nextTick(() => {
            // preventScroll keeps the heading in view on short screens.
            this.$refs.input?.focus({ preventScroll: true });
        });
    },

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
            const html = this.direction === 'md-to-html'
                ? this.output
                : marked.parse(this.output);
            // The preview is injected via x-html, and `input` is read from the
            // shareable URL — sanitize so a crafted ?input= link can't run JS.
            return DOMPurify.sanitize(html);
        } catch (e) {
            return '';
        }
    },

    // Whichever side is holding Markdown right now.
    get markdownSource() {
        return this.direction === 'md-to-html' ? this.input : this.output;
    },

    get quotes() {
        const source = this.markdownSource;
        if (!source) return [];

        try {
            return collectQuotes(marked.lexer(source)).map((markdown, index) => ({
                key: `md-quote-${index}`,
                label: `Quote ${index + 1}`,
                markdown,
                html: DOMPurify.sanitize(marked.parse(markdown)),
            }));
        } catch (e) {
            return [];
        }
    },

    // Make a quote the whole document, so download, share and the HTML output
    // all follow it too.
    extractQuote(markdown) {
        this.direction = 'md-to-html';
        this.input = markdown;
        window.scrollTo({ top: 0, behavior: 'smooth' });
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

    copyPreview() {
        return this.$copyRich(this.preview, 'md-preview');
    },
}));
