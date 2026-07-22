const formatter = new Intl.NumberFormat(undefined, {
    style: 'percent',
    maximumFractionDigits: 2,
    signDisplay: 'exceptZero',
});

const isSet = (v) => v !== null && v !== '' && !Number.isNaN(v);

export default () => ({
    x: null,
    y: null,

    getResult() {
        // Percent change divides by the starting value x, so x of 0 is undefined;
        // y may be 0 (a drop to zero is -100%).
        if (isSet(this.x) && isSet(this.y) && Number(this.x) !== 0) {
            return formatter.format((this.y - this.x) / this.x);
        }
        return '--';
    },
})
