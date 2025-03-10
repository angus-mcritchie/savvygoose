import Code128Generator from "code-128-encoder";
import "../../css/barcode-generator.css";

export default () => ({
    label: null,
    value: null,
    code: null,

    init() {
        this.$watch('value', () => this.code = (new Code128Generator()).encode(this.value));
    },

    getLabel() {
        return this.label || 'my label';
    },

    getCode() {
        return this.code || 'ÌvalueÈÎ';
    },

    getValue() {
        return this.value || 'value';
    }
})
