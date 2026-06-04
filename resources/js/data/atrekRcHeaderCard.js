import JsBarcode from 'jsbarcode';
import { Printd } from 'printd';
import interRegular from '../../fonts/inter-400.woff2?url';
import interBold from '../../fonts/inter-700.woff2?url';
import { withUrlState } from '../lib/urlState';

const interRegularUrl = new URL(interRegular, document.baseURI).href;
const interBoldUrl = new URL(interBold, document.baseURI).href;

const printCss = `
@page { size: 94mm 12mm; margin: 0; }
@font-face {
    font-family: "Inter";
    font-style: normal;
    font-weight: 400;
    font-display: block;
    src: url("${interRegularUrl}") format("woff2");
}
@font-face {
    font-family: "Inter";
    font-style: normal;
    font-weight: 700;
    font-display: block;
    src: url("${interBoldUrl}") format("woff2");
}
html, body { margin: 0; padding: 0; }
body { width: 94mm; height: 12mm; background: white; }
.sticker {
    width: 94mm;
    height: 12mm;
    background: white;
    color: black;
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    padding: 1mm 3mm;
    gap: 3mm;
    font-family: "Inter", system-ui, sans-serif;
    box-sizing: border-box;
}
.sticker .left { min-width: 0; }
.sticker .left .sku { font-weight: 700; font-size: 4mm; line-height: 1; }
.sticker .left .name {
    font-weight: 400;
    font-size: 2.5mm;
    margin-top: 0.7mm;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.sticker .right { display: flex; flex-direction: column; align-items: center; gap: 0.3mm; margin-top: 1mm; }
.sticker .right svg { display: block; height: 5mm; width: auto; }
.sticker .right .ean { font-size: 1.8mm; letter-spacing: 0.05em; text-align: center; }
`;

const schema = {
    sku: { type: 'string', default: '1234' },
    name: { type: 'string', alias: 'n', default: 'Example Product Title Only 2Pcs' },
    ean: { type: 'string', default: '1234567890123' },
};

export default withUrlState(schema, () => ({
    error: '',

    init() {
        this.$watch('ean', () => this.render());
        this.$nextTick(() => this.render());
    },

    render() {
        const svg = this.$refs.barcodeSvg;
        if (!svg) return;
        try {
            JsBarcode(svg, this.ean, {
                format: 'CODE128',
                width: 2,
                height: 40,
                margin: 0,
                displayValue: false,
            });
            this.error = '';
        } catch (e) {
            this.error = (e && e.message) || 'Invalid EAN-13';
        }
    },

    print() {
        if (this.error) return;
        (new Printd()).print(
            this.$refs.sticker,
            [printCss],
            [],
            async ({ iframe, launchPrint }) => {
                const idoc = iframe.contentDocument;
                if (idoc?.fonts) {
                    try { await idoc.fonts.load('400 1em "Inter"'); } catch {}
                    try { await idoc.fonts.load('700 1em "Inter"'); } catch {}
                    try { await idoc.fonts.ready; } catch {}
                }
                launchPrint();
            },
        );
    },
}));
