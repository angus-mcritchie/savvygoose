
export default () => ({
    x: null,
    y: null,

    getResult() {
        if (this.x && this.y) {
            return Number(this.y * (this.x / 100)).toFixed(2);
        }
        return '--';
    },
})
