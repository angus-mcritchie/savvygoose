const formatter = new Intl.NumberFormat(undefined, {
    style: 'percent',
    maximumFractionDigits: 2,
});

export default () => ({
    x: null,
    y: null,

    getResult() {
        if (this.x && this.y) {
            return formatter.format(this.x / this.y);
        }
        return '--';
    },
})
