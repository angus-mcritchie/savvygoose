import Code128Generator from "code-128-encoder";
// The barcode print styles are loaded on the barcode view itself (via a
// Vite::asset <link>) and injected into the Printd iframe — no need to also
// bundle them into the global app.js entry, which would link them on every page.
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

// Code 128 only covers ASCII (code points 0-127). Anything outside that range
// can't be encoded and would otherwise produce a corrupt, unscannable barcode.
const hasUnsupportedChars = (text) => [...text].some((ch) => ch.codePointAt(0) > 127);

export default withUrlState(schema, () => ({
    label: null,
    value: null,
    code: null,
    error: '',

    init() {
        // Compute the initial code BEFORE wiring the watcher: withUrlState sets
        // `value` from the query string during its own init(), which runs before
        // this userInit(), so a watcher alone never fires for a restored value.
        this.encode();
        this.$watch('value', () => this.encode());
        if (this.print && !this.error) {
            this.$nextTick(() => this.printBarcode());
        }
    },

    encode() {
        const text = this.value || '';
        if (hasUnsupportedChars(text)) {
            this.code = null;
            this.error = 'Code 128 supports ASCII only (letters, digits, and common symbols). Remove accented or non-Latin characters to generate a scannable barcode.';
            return;
        }
        this.error = '';
        this.code = text === '' ? null : (new Code128Generator()).encode(text);
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
        if (this.error) return '';
        return this.code || 'ÌvalueÈÎ';
    },

    getValue() {
        return this.value || 'value';
    },

    printBarcode() {
        if (this.error || !this.code) return;
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
