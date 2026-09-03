import { withUrlState } from '../lib/urlState';
import { renderMarkdown, firstHeading } from '../lib/markdown';
import { converterUrl, DOCUMENT_FIELD } from '../lib/markdownLinks';

const schema = {
    // Short key: this one is meant to be pasted into chat and email, and the
    // document itself is already spending most of the budget.
    doc: { ...DOCUMENT_FIELD, alias: 'd' },
};

export default withUrlState(schema, () => ({
    // What the server rendered into <title>, kept so an emptied document can
    // put it back.
    defaultTitle: '',

    init() {
        this.defaultTitle = document.title;
        this.$watch('doc', () => this.syncTitle());
        this.syncTitle();
    },

    get hasDocument() {
        return this.doc.trim() !== '';
    },

    get html() {
        return renderMarkdown(this.doc);
    },

    get title() {
        return firstHeading(this.doc);
    },

    get filename() {
        const slug = this.title
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 60);

        return `${slug || 'document'}.md`;
    },

    get editUrl() {
        // A hand-made link can carry more than the converter's own field accepts,
        // so fall back to the bare tool rather than to an empty href.
        return converterUrl(this.doc) || '/markdown-converter';
    },

    // The document only exists client-side, so the tab is the one piece of page
    // furniture that can carry its name. Reuse whatever suffix the server put in
    // <title> so the site name stays declared in one place.
    syncTitle() {
        const suffix = this.defaultTitle.split(' — ').pop();
        document.title = this.title ? `${this.title} — ${suffix}` : this.defaultTitle;
    },

    clear() {
        this.doc = '';
    },
}));
