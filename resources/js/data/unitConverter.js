import { withUrlState } from '../lib/urlState';

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
    volume: {
        label: 'Volume',
        units: {
            ml: { label: 'Milliliter', symbol: 'mL', factor: 1 },
            l: { label: 'Liter', symbol: 'L', factor: 1000 },
            cm3: { label: 'Cubic centimeter', symbol: 'cm³', factor: 1 },
            m3: { label: 'Cubic meter', symbol: 'm³', factor: 1_000_000 },
            in3: { label: 'Cubic inch', symbol: 'in³', factor: 16.387064 },
            ft3: { label: 'Cubic foot', symbol: 'ft³', factor: 28316.846592 },
            tsp: { label: 'Teaspoon (US)', symbol: 'tsp', factor: 4.92892159375 },
            tbsp: { label: 'Tablespoon (US)', symbol: 'tbsp', factor: 14.78676478125 },
            floz: { label: 'Fluid ounce (US)', symbol: 'fl oz', factor: 29.5735295625 },
            cup: { label: 'Cup (US)', symbol: 'cup', factor: 236.5882365 },
            pt: { label: 'Pint (US)', symbol: 'pt', factor: 473.176473 },
            qt: { label: 'Quart (US)', symbol: 'qt', factor: 946.352946 },
            gal: { label: 'Gallon (US)', symbol: 'gal', factor: 3785.411784 },
            gal_uk: { label: 'Gallon (UK)', symbol: 'gal', factor: 4546.09 },
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
    volume: { from: 'l', to: 'gal', value: '1' },
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

const schema = {
    cat: { type: 'enum', values: Object.keys(UNITS), default: 'length' },
    from: {
        type: 'string',
        parse: (raw, state) => UNITS[state.cat]?.units[raw] ? raw : undefined,
        serialize: (value, state) => {
            const def = DEFAULTS[state.cat];
            if (value === def?.from) return { skip: true };
            return { value };
        },
    },
    to: {
        type: 'string',
        parse: (raw, state) => UNITS[state.cat]?.units[raw] ? raw : undefined,
        serialize: (value, state) => {
            const def = DEFAULTS[state.cat];
            if (value === def?.to) return { skip: true };
            return { value };
        },
    },
    value: {
        type: 'string',
        serialize: (value, state) => {
            const def = DEFAULTS[state.cat];
            if (!value || value === def?.value) return { skip: true };
            return { value };
        },
    },
};

export default withUrlState(schema, () => ({
    units: UNITS,
    cat: DEFAULTS.cat,
    from: DEFAULTS.length.from,
    to: DEFAULTS.length.to,
    value: DEFAULTS.length.value,
    result: '',
    source: 'left',
    lock: false,

    init() {
        // After URL-state parsing, if from/to don't match the (possibly-changed) category,
        // fall back to the category's defaults. Same for value.
        const def = DEFAULTS[this.cat];
        if (!UNITS[this.cat]?.units[this.from]) this.from = def.from;
        if (!UNITS[this.cat]?.units[this.to]) this.to = def.to;
        if (!this.value) this.value = def.value;

        this.recompute();

        this.$watch('cat', (next, prev) => {
            if (next === prev) return;
            const newDef = DEFAULTS[next];
            this.lock = true;
            this.from = newDef.from;
            this.to = newDef.to;
            this.value = newDef.value;
            this.result = '';
            this.source = 'left';
            this.$nextTick(() => {
                this.lock = false;
                this.recompute();
            });
        });

        ['from', 'to'].forEach((p) => this.$watch(p, () => {
            if (this.lock) return;
            this.recompute();
        }));

        this.$watch('value', () => {
            if (this.lock) return;
            this.source = 'left';
            this.recompute();
        });

        this.$watch('result', () => {
            if (this.lock) return;
            this.source = 'right';
            this.recompute();
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
}));
