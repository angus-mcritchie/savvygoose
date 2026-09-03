// One configured `marked` for the whole bundle.
//
// `marked.use` mutates a module-level singleton, and app.js imports every tool's
// data file on every page, so a second tool calling it would register
// markedHighlight twice and walk each code token twice, escaping the first
// pass's output. Anything that renders Markdown imports this instead.

import { marked } from 'marked';
import { markedHighlight } from 'marked-highlight';
import hljs from 'highlight.js/lib/common';
import DOMPurify from 'dompurify';

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

export { marked };

// Markdown in, sanitized HTML out. Every caller feeds the result to x-html and
// every source is a URL someone else may have written, so sanitizing is not
// optional here.
export function renderMarkdown(markdown) {
    if (!markdown) return '';
    try {
        return DOMPurify.sanitize(marked.parse(markdown));
    } catch (e) {
        return '';
    }
}

// The document's own first heading, as plain text. Round-tripping through
// parseInline and stripping every tag is what turns `# A **bold** title` into
// something usable as a <title>, entities and all.
export function firstHeading(markdown) {
    if (!markdown) return '';
    try {
        const heading = marked.lexer(markdown).find((token) => token.type === 'heading');
        if (!heading) return '';

        return DOMPurify.sanitize(marked.parseInline(heading.text), { ALLOWED_TAGS: [] }).trim();
    } catch (e) {
        return '';
    }
}
