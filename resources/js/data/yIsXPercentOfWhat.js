const formatter = new Intl.NumberFormat(undefined, {
    maximumFractionDigits: 2,
});

const isSet = (v) => v !== null && v !== '' && !Number.isNaN(v);

export default () => ({
    x: null,
    y: null,

    getResult() {
        // "y is x percent of what" divides by x, so x of 0 is undefined; y may be 0.
        if (isSet(this.x) && isSet(this.y) && Number(this.x) !== 0) {
            return formatter.format(this.y / (this.x / 100));
        }
        return '--';
    },
})
