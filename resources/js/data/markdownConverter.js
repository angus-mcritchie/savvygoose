import TurndownService from 'turndown';
import { withUrlState } from '../lib/urlState';
import { marked, renderMarkdown } from '../lib/markdown';
import { documentUrl as buildDocumentUrl, CONVERTER_INPUT_FIELD } from '../lib/markdownLinks';

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
    // Shared with the document viewer, which has to accept whatever link the
    // "Share as a document" field here hands out.
    input: CONVERTER_INPUT_FIELD,
};

export default withUrlState(schema, () => ({
    documentUrl: '',
    documentTooLong: false,

    init() {
        this.focusInput();
    },

    // withUrlState calls this after it has written its own params, which is also
    // exactly when the document link needs rebuilding.
    updateUrl() {
        const markdown = this.markdownSource;
        this.documentUrl = buildDocumentUrl(markdown);
        this.documentTooLong = markdown !== '' && this.documentUrl === '';
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
        // The preview is injected via x-html, and `input` is read from the
        // shareable URL, so it goes through renderMarkdown's sanitizer either
        // way. In html-to-md the round trip through Markdown is what strips the
        // pasted HTML back to what the conversion actually kept.
        return this.direction === 'md-to-html'
            ? renderMarkdown(this.input)
            : renderMarkdown(this.output);
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
                html: renderMarkdown(markdown),
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
