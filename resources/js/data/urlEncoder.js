import { withUrlState } from '../lib/urlState';

const MAX_URL_INPUT = 3000;

const schema = {
    direction: { type: 'enum', values: ['encode', 'decode'], default: 'encode', alias: 'dir' },
    variant: { type: 'enum', values: ['component', 'uri'], default: 'component' },
    input: { type: 'string', alias: 'text', maxLength: MAX_URL_INPUT },
};

export default withUrlState(schema, () => ({
    error: '',

    get output() {
        this.error = '';
        if (!this.input) return '';
        try {
            if (this.direction === 'encode') {
                return this.variant === 'component'
                    ? encodeURIComponent(this.input)
                    : encodeURI(this.input);
            }
            return this.variant === 'component'
                ? decodeURIComponent(this.input)
                : decodeURI(this.input);
        } catch (e) {
            this.error = 'Input contains an invalid percent-encoded sequence.';
            return '';
        }
    },

    get inputLabel() {
        return this.direction === 'encode' ? 'Plain text' : 'Encoded text';
    },
    get outputLabel() {
        return this.direction === 'encode' ? 'Encoded text' : 'Plain text';
    },
    get inputPlaceholder() {
        return this.direction === 'encode'
            ? 'hello world & friends?'
            : 'hello%20world%20%26%20friends%3F';
    },

    swap() {
        if (!this.output) return;
        const next = this.output;
        this.direction = this.direction === 'encode' ? 'decode' : 'encode';
        this.input = next;
    },

    clear() {
        this.input = '';
    },
}));
