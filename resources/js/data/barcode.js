import Code128Generator from "code-128-encoder";
import "../../css/barcode-generator.css";
import { Printd } from "printd";
import { withUrlState } from "../lib/urlState";

const positiveFloat = (raw) => {
    const n = parseFloat(raw);
    return Number.isFinite(n) && n > 0 ? n : undefined;
};

// Walk the parent document's stylesheets and return the CSS text of the sheet
// that defines the barcode styles. Needed because Vite serves CSS as a JS
// module in dev, so handing Printd the link href would yield JS, not CSS.
const collectBarcodeCss = () => {
    for (const sheet of document.styleSheets) {
        let rules;
        try { rules = [...sheet.cssRules]; } catch { continue; }
        if (rules.some((r) => r.cssText && r.cssText.includes('data-barcode'))) {
            return rules.map((r) => r.cssText).join('\n');
        }
    }
    return '';
};

const schema = {
    label: { type: 'string' },
    value: { type: 'string' },
    print: { type: 'boolean', default: false },
    width: { type: 'number', default: 4, parse: positiveFloat },
    height: { type: 'number', default: 1.5, parse: positiveFloat },
    labelSize: { type: 'number', default: 0.3, parse: positiveFloat },
    codeSize: { type: 'number', default: 0.7, parse: positiveFloat },
    valueSize: { type: 'number', default: 0.25, parse: positiveFloat },
    showLabel: { type: 'boolean', default: true },
    showValue: { type: 'boolean', default: true },
};

export default withUrlState(schema, () => ({
    label: null,
    value: null,
    code: null,

    init() {
        this.$watch('value', () => this.code = (new Code128Generator()).encode(this.value));
        if (this.print) {
            this.$nextTick(() => this.printBarcode());
        }
    },

    canvasStyle() {
        const safe = (val, def) => (Number.isFinite(val) && val > 0 ? val : def);
        return {
            '--barcode-width': `${safe(this.width, 4)}cm`,
            '--barcode-height': `${safe(this.height, 1.5)}cm`,
            '--barcode-label-size': `${safe(this.labelSize, 0.3)}cm`,
            '--barcode-code-size': `${safe(this.codeSize, 0.7)}cm`,
            '--barcode-value-size': `${safe(this.valueSize, 0.25)}cm`,
        };
    },

    getLabel() {
        return this.label || 'my label';
    },

    getCode() {
        return this.code || 'ÌvalueÈÎ';
    },

    getValue() {
        return this.value || 'value';
    },

    printBarcode() {
        const text = this.getCode();
        (new Printd()).print(
            this.$refs.barcodeCanvas,
            [collectBarcodeCss()],
            [],
            async ({ iframe, launchPrint }) => {
                const idoc = iframe.contentDocument;
                if (idoc?.fonts) {
                    try { await idoc.fonts.load(`1em "Libre Barcode 128"`, text); } catch {}
                    try { await idoc.fonts.ready; } catch {}
                }
                launchPrint();
            },
        );
    }
}));
