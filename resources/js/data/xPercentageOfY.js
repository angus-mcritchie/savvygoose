const formatter = new Intl.NumberFormat(undefined, {
    style: 'percent',
    maximumFractionDigits: 2,
});

const isSet = (v) => v !== null && v !== '' && !Number.isNaN(v);

export default () => ({
    x: null,
    y: null,

    getResult() {
        // "x is what percent of y" is undefined when y is 0; x may be 0.
        if (isSet(this.x) && isSet(this.y) && Number(this.y) !== 0) {
            return formatter.format(this.x / this.y);
        }
        return '--';
    },
})
