const UNITS = {
    length: {
        label: 'Length',
        units: {
            mm: { label: 'Millimeter', symbol: 'mm', factor: 0.001 },
            cm: { label: 'Centimeter', symbol: 'cm', factor: 0.01 },
            m: { label: 'Meter', symbol: 'm', factor: 1 },
            km: { label: 'Kilometer', symbol: 'km', factor: 1000 },
            in: { label: 'Inch', symbol: 'in', factor: 0.0254 },
            ft: { label: 'Foot', symbol: 'ft', factor: 0.3048 },
            yd: { label: 'Yard', symbol: 'yd', factor: 0.9144 },
            mi: { label: 'Mile', symbol: 'mi', factor: 1609.344 },
            nmi: { label: 'Nautical mile', symbol: 'nmi', factor: 1852 },
        },
    },
    weight: {
        label: 'Weight',
        units: {
            mg: { label: 'Milligram', symbol: 'mg', factor: 0.001 },
            g: { label: 'Gram', symbol: 'g', factor: 1 },
            kg: { label: 'Kilogram', symbol: 'kg', factor: 1000 },
            t: { label: 'Tonne', symbol: 't', factor: 1_000_000 },
            oz: { label: 'Ounce', symbol: 'oz', factor: 28.349523125 },
            lb: { label: 'Pound', symbol: 'lb', factor: 453.59237 },
            st: { label: 'Stone', symbol: 'st', factor: 6350.29318 },
        },
    },
    temperature: {
        label: 'Temperature',
        special: true,
        units: {
            c: { label: 'Celsius', symbol: '°C' },
            f: { label: 'Fahrenheit', symbol: '°F' },
            k: { label: 'Kelvin', symbol: 'K' },
        },
    },
    data: {
        label: 'Data',
        units: {
            B: { label: 'Byte', symbol: 'B', factor: 1 },
            KB: { label: 'Kilobyte', symbol: 'KB', factor: 1e3 },
            MB: { label: 'Megabyte', symbol: 'MB', factor: 1e6 },
            GB: { label: 'Gigabyte', symbol: 'GB', factor: 1e9 },
            TB: { label: 'Terabyte', symbol: 'TB', factor: 1e12 },
            PB: { label: 'Petabyte', symbol: 'PB', factor: 1e15 },
            KiB: { label: 'Kibibyte', symbol: 'KiB', factor: 1024 },
            MiB: { label: 'Mebibyte', symbol: 'MiB', factor: 1024 ** 2 },
            GiB: { label: 'Gibibyte', symbol: 'GiB', factor: 1024 ** 3 },
            TiB: { label: 'Tebibyte', symbol: 'TiB', factor: 1024 ** 4 },
            PiB: { label: 'Pebibyte', symbol: 'PiB', factor: 1024 ** 5 },
        },
    },
};

const DEFAULTS = {
    cat: 'length',
    length: { from: 'm', to: 'ft', value: '1' },
    weight: { from: 'kg', to: 'lb', value: '1' },
    temperature: { from: 'c', to: 'f', value: '20' },
    data: { from: 'MB', to: 'MiB', value: '1' },
};

function tempToK(value, unit) {
    if (unit === 'c') return value + 273.15;
    if (unit === 'f') return (value + 459.67) * 5 / 9;
    return value;
}

function tempFromK(k, unit) {
    if (unit === 'c') return k - 273.15;
    if (unit === 'f') return k * 9 / 5 - 459.67;
    return k;
}

function convert(value, cat, from, to) {
    if (!Number.isFinite(value)) return null;
    if (from === to) return value;
    if (cat === 'temperature') {
        return tempFromK(tempToK(value, from), to);
    }
    const units = UNITS[cat].units;
    return value * units[from].factor / units[to].factor;
}

function format(n) {
    if (n === null || !Number.isFinite(n)) return '';
    if (n === 0) return '0';
    const abs = Math.abs(n);
    if (abs >= 1e16 || abs < 1e-6) {
        return n.toExponential(6).replace(/(\.\d*?)0+e/, '$1e').replace(/\.e/, 'e');
    }
    return parseFloat(n.toPrecision(10)).toString();
}

export default () => ({
    units: UNITS,
    cat: DEFAULTS.cat,
    from: DEFAULTS.length.from,
    to: DEFAULTS.length.to,
    value: DEFAULTS.length.value,
    result: '',
    source: 'left',
    lock: false,
    url: window.location.href,

    init() {
        this.initFromUrl();
        this.recompute();
        this.updateUrl();

        this.$watch('cat', (next, prev) => {
            if (next === prev) return;
            const def = DEFAULTS[next];
            this.lock = true;
            this.from = def.from;
            this.to = def.to;
            this.value = def.value;
            this.result = '';
            this.source = 'left';
            this.$nextTick(() => {
                this.lock = false;
                this.recompute();
                this.updateUrl();
            });
        });

        ['from', 'to'].forEach((p) => this.$watch(p, () => {
            if (this.lock) return;
            this.recompute();
            this.updateUrl();
        }));

        this.$watch('value', () => {
            if (this.lock) return;
            this.source = 'left';
            this.recompute();
            this.updateUrl();
        });

        this.$watch('result', () => {
            if (this.lock) return;
            this.source = 'right';
            this.recompute();
            this.updateUrl();
        });
    },

    recompute() {
        this.lock = true;
        if (this.source === 'left') {
            const n = parseFloat(this.value);
            this.result = Number.isFinite(n) ? format(convert(n, this.cat, this.from, this.to)) : '';
        } else {
            const n = parseFloat(this.result);
            this.value = Number.isFinite(n) ? format(convert(n, this.cat, this.to, this.from)) : '';
        }
        this.$nextTick(() => this.lock = false);
    },

    swap() {
        const newFrom = this.to;
        const newTo = this.from;
        const newValue = this.source === 'left' ? this.result : this.value;
        this.lock = true;
        this.from = newFrom;
        this.to = newTo;
        this.value = newValue;
        this.source = 'left';
        this.$nextTick(() => {
            this.lock = false;
            this.recompute();
            this.updateUrl();
        });
    },

    currentUnits() {
        return Object.entries(this.units[this.cat].units).map(([key, u]) => ({ key, ...u }));
    },

    formula() {
        const fromU = this.units[this.cat].units[this.from];
        const toU = this.units[this.cat].units[this.to];
        if (!fromU || !toU) return '';
        const v = parseFloat(this.value);
        if (!Number.isFinite(v)) return '';
        const r = convert(v, this.cat, this.from, this.to);
        if (!Number.isFinite(r)) return '';
        return `${format(v)} ${fromU.symbol} = ${format(r)} ${toU.symbol}`;
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);

        if (params.has('cat') && this.units[params.get('cat')]) {
            this.cat = params.get('cat');
        }

        const def = DEFAULTS[this.cat];
        this.from = def.from;
        this.to = def.to;
        this.value = def.value;

        if (params.has('from') && this.units[this.cat].units[params.get('from')]) {
            this.from = params.get('from');
        }
        if (params.has('to') && this.units[this.cat].units[params.get('to')]) {
            this.to = params.get('to');
        }
        if (params.has('value')) {
            this.value = params.get('value');
        }
    },

    updateUrl() {
        const params = new URLSearchParams();
        const def = DEFAULTS[this.cat];
        if (this.cat !== DEFAULTS.cat) params.set('cat', this.cat);
        if (this.from !== def.from) params.set('from', this.from);
        if (this.to !== def.to) params.set('to', this.to);
        if (this.value !== '' && this.value !== def.value) params.set('value', this.value);
        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
