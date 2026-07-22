const formatter = new Intl.NumberFormat(undefined, {
    maximumFractionDigits: 2,
});

const isSet = (v) => v !== null && v !== '' && !Number.isNaN(v);

export default () => ({
    x: null,
    y: null,

    getResult() {
        if (isSet(this.x) && isSet(this.y)) {
            return formatter.format(this.y * (this.x / 100));
        }
        return '--';
    },
})
