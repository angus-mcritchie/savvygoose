import Code128Generator from "code-128-encoder";
import "../../css/barcode-generator.css";
import Printd from "printd";

export default () => ({
    label: null,
    value: null,
    code: null,
    print: null,
    url: window.location.href,

    init() {
        this.$watch('value', () => this.code = (new Code128Generator()).encode(this.value));

        this.initFromUrl();

        this.$watch('label', () => this.updateUrl());
        this.$watch('value', () => this.updateUrl());
        this.$watch('print', () => this.updateUrl());

        this.updateUrl();
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);

        if (params.has('label')) {
            this.label = params.get('label');
        }

        if (params.has('value')) {
            this.value = params.get('value');
        }
        if (params.has('print')) {
            this.print = params.get('print') === 'true';
        }

        if (this.print) {
            this.$nextTick(() => this.printBarcode());
        }
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        if (this.label) {
            params.set('label', this.label);
        }

        if (this.value) {
            params.set('value', this.value);
        }

        if (this.print) {
            params.set('print', true);
        }

        this.url = `${window.location.origin}${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
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
        (new Printd()).print(this.$refs.barcodeCanvas, [this.$refs.stylesheet.href]);
    }
})
