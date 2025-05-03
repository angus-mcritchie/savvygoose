
export default () => ({
    text: null,

    getCharacterCount() {
        if (this.text) {
            return this.text.length;
        }
        return 0;
    },
    getWordCount() {
        if (this.text) {
            return this.text.trim().split(/\s+/).length;
        }
        return 0;
    },
    getLineCount() {
        if (this.text) {
            return this.text.split('\n').length;
        }
        return 0;
    }
})
