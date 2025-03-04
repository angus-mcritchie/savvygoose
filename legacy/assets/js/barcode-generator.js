import "./libs/print-this.js";

$(document).ready(function () {
    const elems = {
        /* settings: {
            pageWidth: $("#settings-page-width"),
            pageHeight: $("#settings-page-height"),
            displayLabel: $("#settings-display-label"),
            displayValue: $("#settings-display-value"),
        }, */

        generate: {
            label: $("#generate-label"),
            value: $("#generate-value")
        },

        output: {
            stickerContainer: $("#output-sticker-container"),
            //sticker: $("#output-sticker"),
            label: $("#output-label"),
            code: $("#output-code"),
            value: $("#output-value"),
            printBtn: $("#output-print-btn"),
        },
    };

    elems.generate.label.keyup(renderSticker);
    elems.generate.value.keyup(renderSticker);

    elems.output.printBtn.click(printSticker);

    function renderParams() {
        const { label, value } = elems.generate;

        // get label and value from url parameters
        const url = new URL(window.location.href);
        const labelParam = url.searchParams.get("label");
        const valueParam = url.searchParams.get("value");
        const printParam = url.searchParams.get("print");

        label.val(labelParam);
        value.val(valueParam);
        
        if (valueParam) renderSticker();
        if (valueParam && printParam) printSticker();
    }

    function renderSticker() {
        const encoder = new Code128Generator();
        const { label: labelInput, value: valueInput } = elems.generate;
        const { label: stickerLabel, code: stickerCode, value: stickerValue } = elems.output;

        const value = valueInput.val() ? valueInput.val() : 'value';
        const label = labelInput.val() ? labelInput.val() : 'my label';
        const code = encoder.encode(value);

        stickerCode.text(code);
        stickerLabel.text(label);
        stickerValue.text(value);
    }

    function printSticker() {
        const printThisSettings = {
            importCSS: false,
            loadCSS: `/assets/css/barcode-generator.css`,
        };

        elems.output.stickerContainer.printThis(printThisSettings);
    }

    function init() {
        renderParams();
    }

    init();
});
