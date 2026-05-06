const formatter = new Intl.NumberFormat(undefined, {
    maximumFractionDigits: 2,
});

export default () => ({
    x: null,
    y: null,

    getResult() {
        if (this.x && this.y) {
            return formatter.format(this.y * (this.x / 100));
        }
        return '--';
    },
})
