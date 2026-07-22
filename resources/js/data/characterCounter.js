import { withUrlState } from '../lib/urlState';

const WORDS_PER_MINUTE = 200;

const schema = {
    text: { type: 'string', maxLength: 3000 },
};

export default withUrlState(schema, () => ({
    get characterCount() {
        // Count Unicode code points (via the string iterator), not UTF-16 code
        // units, so an emoji like 😀 counts as one character as the copy promises.
        return [...this.text].length;
    },
    get characterCountNoSpaces() {
        return [...this.text.replace(/\s/g, '')].length;
    },
    get wordCount() {
        const trimmed = this.text.trim();
        if (!trimmed) return 0;
        return trimmed.split(/\s+/).length;
    },
    get sentenceCount() {
        const matches = this.text.match(/[^.!?]+[.!?]+/g);
        return matches ? matches.length : 0;
    },
    get lineCount() {
        if (!this.text) return 0;
        return this.text.split('\n').length;
    },
    get averageWordLength() {
        if (this.wordCount === 0) return '0';
        return (this.characterCountNoSpaces / this.wordCount).toFixed(1);
    },
    get readingTime() {
        if (this.wordCount === 0) return '0 min';
        const minutes = this.wordCount / WORDS_PER_MINUTE;
        if (minutes < 1) return '< 1 min';
        return `${Math.ceil(minutes)} min`;
    },

    clear() {
        this.text = '';
    },
}));
