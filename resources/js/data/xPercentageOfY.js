
export default () => ({
    x: null,
    y: null,

    getResult() {
        if (this.x && this.y) {
            return Number((this.x / this.y) * 100).toFixed(2) + '%';
        }
        return '--';
    },
})
