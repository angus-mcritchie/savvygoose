export default () => ({
    direction: 'encode',
    variant: 'component',
    input: '',
    error: '',
    copied: false,

    init() {
        this.initFromUrl();

        ['direction', 'variant', 'input'].forEach((prop) => {
            this.$watch(prop, () => this.updateUrl());
        });
    },

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

    async copy() {
        if (!this.output) return;
        await navigator.clipboard.writeText(this.output);
        this.copied = true;
        setTimeout(() => (this.copied = false), 1500);
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('dir') && ['encode', 'decode'].includes(params.get('dir'))) {
            this.direction = params.get('dir');
        }
        if (params.has('variant') && ['component', 'uri'].includes(params.get('variant'))) {
            this.variant = params.get('variant');
        }
        if (params.has('text')) {
            this.input = params.get('text');
        }
    },

    updateUrl() {
        const params = new URLSearchParams(window.location.search);

        if (this.direction !== 'encode') params.set('dir', this.direction); else params.delete('dir');
        if (this.variant !== 'component') params.set('variant', this.variant); else params.delete('variant');
        if (this.input) params.set('text', this.input); else params.delete('text');

        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        window.history.replaceState({}, '', newUrl);
    },
});
