const formatter = new Intl.NumberFormat(undefined, {
    maximumFractionDigits: 2,
});

const isSet = (v) => v !== null && v !== '' && !Number.isNaN(v);

export default () => ({
    x: null,
    y: null,

    getAddResult() {
        if (isSet(this.x) && isSet(this.y)) {
            return formatter.format(this.x + this.x * (this.y / 100));
        }
        return '--';
    },

    getSubtractResult() {
        if (isSet(this.x) && isSet(this.y)) {
            return formatter.format(this.x - this.x * (this.y / 100));
        }
        return '--';
    },
})
