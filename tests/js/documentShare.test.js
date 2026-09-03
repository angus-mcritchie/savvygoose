/**
 * DOMPurify binds to a real window at import time, so the rendering half of this
 * file needs a DOM. The link half stubs `window` over the top, which is fine:
 * DOMPurify already holds its own reference to the jsdom one.
 *
 * @vitest-environment jsdom
 */
import { beforeEach, describe, it, expect, vi } from 'vitest';
import { withUrlState } from '../../resources/js/lib/urlState';
import { documentUrl, converterUrl, DOCUMENT_FIELD } from '../../resources/js/lib/markdownLinks';
import { renderMarkdown, firstHeading } from '../../resources/js/lib/markdown';

function stubWindow(href = 'https://savvygoose.com/markdown-converter') {
    const url = new URL(href);
    vi.stubGlobal('window', {
        location: { search: url.search, origin: url.origin, pathname: url.pathname, href: url.href },
        history: { replaceState: vi.fn() },
    });
}

// The receiving half: the Document Viewer's schema, read the way withUrlState
// reads it. Deliberately reconstructed here rather than imported from the
// component, so the test still fails if the two ends stop agreeing.
function readDocumentFrom(url) {
    const target = new URL(url);
    stubWindow(url);

    const scope = withUrlState({ doc: { ...DOCUMENT_FIELD, alias: 'd' } }, () => ({}))();
    scope.$watch = vi.fn();
    scope.init();

    expect(target.pathname).toBe('/document-viewer');

    return scope.doc;
}

const shortDoc = '# Hello\n\nA **short** document.';
const longDoc = ('# Quarterly notes\n\n' + 'Some prose about how the quarter went. '.repeat(150)).trim();

beforeEach(() => {
    vi.unstubAllGlobals();
});

describe('document share links', () => {
    it('round-trips a short document in the clear', () => {
        stubWindow();
        const url = documentUrl(shortDoc);

        expect(new URL(url).searchParams.get('d')).toBe(shortDoc);
        expect(readDocumentFrom(url)).toBe(shortDoc);
    });

    it('round-trips a document that only fits compressed', () => {
        stubWindow();
        expect(longDoc.length).toBeGreaterThan(DOCUMENT_FIELD.maxLength);

        const url = documentUrl(longDoc);
        const params = new URL(url).searchParams;

        expect(params.get('d')).toBeNull();
        expect(params.get('d.z')).toBeTruthy();
        expect(readDocumentFrom(url)).toBe(longDoc);
    });

    it('returns an empty string when the document cannot fit even compressed', () => {
        stubWindow();
        // Random-ish and so incompressible; far past the 8k packed budget.
        let seed = 1;
        let huge = '';
        for (let i = 0; i < 40000; i++) {
            seed = (seed * 1103515245 + 12345) % 2147483648;
            huge += 'abcdefghijklmnopqrstuvwxyz0123456789'[seed % 36];
        }

        expect(documentUrl(huge)).toBe('');
        expect(documentUrl('')).toBe('');
    });

    it('sends the document back to the converter under the key that tool reads', () => {
        stubWindow();
        const url = new URL(converterUrl(shortDoc));

        expect(url.pathname).toBe('/markdown-converter');
        expect(url.searchParams.get('input')).toBe(shortDoc);
    });
});

describe('rendering a shared document', () => {
    it('strips scripting out of markup that arrived in a URL', () => {
        const html = renderMarkdown('# Hi\n\n<img src=x onerror="alert(1)">\n\n<script>alert(2)<\/script>');

        expect(html).toContain('<h1');
        expect(html).not.toContain('onerror');
        expect(html).not.toContain('alert(2)');
    });

    it('highlights a code block exactly once', () => {
        const html = renderMarkdown('```js\nconst a = 1;\n```');

        expect(html).toContain('hljs language-js');
        // A second markedHighlight registration would escape the first pass's
        // own markup, which shows up as &lt;span in the output.
        expect(html).not.toContain('&lt;span');
    });

    it('takes the document title from its first heading, as plain text', () => {
        expect(firstHeading('# A **bold** title\n\nBody.')).toBe('A bold title');
        expect(firstHeading('Body with no heading at all.')).toBe('');
        expect(firstHeading('')).toBe('');
    });
});
