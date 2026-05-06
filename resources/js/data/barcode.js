import Code128Generator from "code-128-encoder";
import "../../css/barcode-generator.css";
import Printd from "printd";

const DEFAULTS = {
    width: 4,
    height: 1.5,
    labelSize: 0.3,
    codeSize: 0.7,
    valueSize: 0.25,
    showLabel: true,
    showValue: true,
};

const NUMERIC_KEYS = ['width', 'height', 'labelSize', 'codeSize', 'valueSize'];
const BOOLEAN_KEYS = ['showLabel', 'showValue'];

export default () => ({
    label: null,
    value: null,
    code: null,
    print: null,
    width: DEFAULTS.width,
    height: DEFAULTS.height,
    labelSize: DEFAULTS.labelSize,
    codeSize: DEFAULTS.codeSize,
    valueSize: DEFAULTS.valueSize,
    showLabel: DEFAULTS.showLabel,
    showValue: DEFAULTS.showValue,
    url: window.location.href,

    init() {
        this.$watch('value', () => this.code = (new Code128Generator()).encode(this.value));

        this.initFromUrl();

        ['label', 'value', 'print', ...NUMERIC_KEYS, ...BOOLEAN_KEYS]
            .forEach((prop) => this.$watch(prop, () => this.updateUrl()));

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

        NUMERIC_KEYS.forEach((key) => {
            if (!params.has(key)) return;
            const parsed = parseFloat(params.get(key));
            if (Number.isFinite(parsed) && parsed > 0) {
                this[key] = parsed;
            }
        });

        BOOLEAN_KEYS.forEach((key) => {
            if (!params.has(key)) return;
            this[key] = params.get(key) !== 'false';
        });

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

        NUMERIC_KEYS.forEach((key) => {
            const value = this[key];
            if (Number.isFinite(value) && value > 0 && value !== DEFAULTS[key]) {
                params.set(key, value);
            } else {
                params.delete(key);
            }
        });

        BOOLEAN_KEYS.forEach((key) => {
            if (this[key] === DEFAULTS[key]) {
                params.delete(key);
            } else {
                params.set(key, this[key]);
            }
        });

        this.url = `${window.location.origin}${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
    },

    canvasStyle() {
        const safe = (val, def) => (Number.isFinite(val) && val > 0 ? val : def);
        return {
            '--barcode-width': `${safe(this.width, DEFAULTS.width)}cm`,
            '--barcode-height': `${safe(this.height, DEFAULTS.height)}cm`,
            '--barcode-label-size': `${safe(this.labelSize, DEFAULTS.labelSize)}cm`,
            '--barcode-code-size': `${safe(this.codeSize, DEFAULTS.codeSize)}cm`,
            '--barcode-value-size': `${safe(this.valueSize, DEFAULTS.valueSize)}cm`,
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
        (new Printd()).print(this.$refs.barcodeCanvas, [this.$refs.stylesheet.href]);
    }
})
