import "./libs/print-this.js";

$(document).ready(function () {
    const elems = {

        input: {
            text: $("#input-text")
        },

        output: {
            characterCount: $("#output-character-count"),
            wordCount: $("#output-word-count"),
            lineCount: $("#output-line-count")
        }
    };
    
    elems.input.text.on('input', countInput);

    function countInput() {
        const text = elems.input.text.val();
        const lineCount = text ? text.split('\n').length : 0;
        const characterCount = text ? text.length - lineCount + 1 : 0;
        const wordCount = text.match(/\S+/g) === null ? 0 : text.match(/\S+/g).length;

        elems.output.characterCount.text(characterCount);
        elems.output.wordCount.text(wordCount);
        elems.output.lineCount.text(lineCount);
    }
});