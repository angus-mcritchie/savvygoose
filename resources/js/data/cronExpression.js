import { withUrlState } from '../lib/urlState';

const MONTHS = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
const DOWS = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const DOW_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const FIELDS = [
    { key: 'minute', min: 0, max: 59 },
    { key: 'hour', min: 0, max: 23 },
    { key: 'dom', min: 1, max: 31 },
    { key: 'month', min: 1, max: 12, names: MONTHS },
    { key: 'dow', min: 0, max: 7, names: DOWS },
];

function nameToNum(token, def) {
    const t = token.toLowerCase();
    if (def.names) {
        const i = def.names.indexOf(t);
        if (i !== -1) return def.key === 'month' ? i + 1 : i;
    }
    if (!/^\d+$/.test(t)) return null;
    return parseInt(t, 10);
}

function parseField(expr, def) {
    const values = new Set();
    for (const part of expr.split(',')) {
        if (part === '') throw new Error(`empty term in ${def.key}`);
        let step = 1;
        let range = part;
        const slash = part.split('/');
        if (slash.length === 2) {
            range = slash[0];
            step = parseInt(slash[1], 10);
            if (!Number.isInteger(step) || step < 1) throw new Error(`bad step in ${def.key}`);
        } else if (slash.length > 2) {
            throw new Error(`bad term in ${def.key}`);
        }

        let lo;
        let hi;
        if (range === '*') {
            lo = def.min;
            hi = def.max;
        } else if (range.includes('-')) {
            const [a, b] = range.split('-');
            lo = nameToNum(a, def);
            hi = nameToNum(b, def);
        } else {
            lo = hi = nameToNum(range, def);
        }

        if (lo == null || hi == null || lo > hi || lo < def.min || hi > def.max) {
            throw new Error(`out-of-range value in ${def.key}`);
        }
        for (let v = lo; v <= hi; v += step) values.add(v);
    }

    // In cron, day-of-week 7 and 0 both mean Sunday.
    if (def.key === 'dow' && values.has(7)) {
        values.delete(7);
        values.add(0);
    }
    return values;
}

function parseCron(expr) {
    const parts = expr.trim().split(/\s+/);
    if (parts.length !== 5) {
        throw new Error('A cron expression needs 5 fields: minute hour day-of-month month day-of-week.');
    }
    const sets = {};
    FIELDS.forEach((def, i) => {
        sets[def.key] = parseField(parts[i], def);
    });
    return { sets, parts, domRestricted: parts[2] !== '*', dowRestricted: parts[4] !== '*' };
}

function matches(date, parsed) {
    const { sets, domRestricted, dowRestricted } = parsed;
    if (!sets.minute.has(date.getMinutes())) return false;
    if (!sets.hour.has(date.getHours())) return false;
    if (!sets.month.has(date.getMonth() + 1)) return false;

    const domOk = sets.dom.has(date.getDate());
    const dowOk = sets.dow.has(date.getDay());

    // Standard cron: when both day fields are restricted, either may match.
    if (domRestricted && dowRestricted) return domOk || dowOk;
    if (domRestricted) return domOk;
    if (dowRestricted) return dowOk;
    return true;
}

function nextRuns(parsed, from, count) {
    const runs = [];
    const d = new Date(from.getTime());
    d.setSeconds(0, 0);
    d.setMinutes(d.getMinutes() + 1);
    // Cap the search at ~366 days of minutes so even yearly jobs resolve.
    for (let i = 0; i < 366 * 24 * 60 && runs.length < count; i++) {
        if (matches(d, parsed)) runs.push(new Date(d.getTime()));
        d.setMinutes(d.getMinutes() + 1);
    }
    return runs;
}

function listPhrase(set, formatter) {
    const arr = [...set].sort((a, b) => a - b);
    const items = arr.map(formatter);
    if (items.length === 1) return items[0];
    if (items.length === 2) return `${items[0]} and ${items[1]}`;
    return `${items.slice(0, -1).join(', ')}, and ${items[items.length - 1]}`;
}

function stepOf(raw) {
    const m = raw.match(/^\*\/(\d+)$/);
    return m ? parseInt(m[1], 10) : null;
}

function describe(parsed) {
    const [minRaw, hourRaw, domRaw, monthRaw, dowRaw] = parsed.parts;
    const { sets } = parsed;
    const parts = [];

    const minStep = stepOf(minRaw);
    const hourStep = stepOf(hourRaw);
    const pad = (n) => String(n).padStart(2, '0');

    if (minRaw === '*' && hourRaw === '*') {
        parts.push('Every minute');
    } else if (minStep && hourRaw === '*') {
        parts.push(`Every ${minStep} minutes`);
    } else if (sets.minute.size === 1 && sets.hour.size === 1) {
        parts.push(`At ${pad([...sets.hour][0])}:${pad([...sets.minute][0])}`);
    } else if (sets.minute.size === 1 && hourRaw === '*') {
        parts.push(`At ${[...sets.minute][0]} minutes past every hour`);
    } else if (hourStep && minRaw === '0') {
        parts.push(`At the top of every ${hourStep}th hour`);
    } else {
        const minPhrase = minRaw === '*' ? 'every minute' : `minute ${listPhrase(sets.minute, String)}`;
        const hourPhrase = hourRaw === '*' ? 'every hour' : `hour ${listPhrase(sets.hour, String)}`;
        parts.push(`At ${minPhrase} of ${hourPhrase}`);
    }

    if (domRaw !== '*') parts.push(`on day-of-month ${listPhrase(sets.dom, String)}`);
    if (dowRaw !== '*') parts.push(`on ${listPhrase(sets.dow, (n) => DOW_NAMES[n])}`);
    if (monthRaw !== '*') parts.push(`in ${listPhrase(sets.month, (n) => MONTH_NAMES[n - 1])}`);

    return parts.join(', ') + '.';
}

const PRESETS = [
    { label: 'Every minute', expr: '* * * * *' },
    { label: 'Every 5 minutes', expr: '*/5 * * * *' },
    { label: 'Every hour', expr: '0 * * * *' },
    { label: 'Every day at midnight', expr: '0 0 * * *' },
    { label: 'Every day at 9am', expr: '0 9 * * *' },
    { label: 'Weekdays at 9am', expr: '0 9 * * 1-5' },
    { label: 'Every Monday', expr: '0 0 * * 1' },
    { label: 'First of the month', expr: '0 0 1 * *' },
];

export { parseCron, describe, nextRuns };

const schema = {
    expr: { type: 'string', default: '0 9 * * 1-5', maxLength: 120, alias: 'e' },
};

export default withUrlState(schema, () => ({
    presets: PRESETS,
    error: '',
    description: '',
    runs: [],

    init() {
        this.$watch('expr', () => this.compute());
        this.compute();
    },

    compute() {
        try {
            const parsed = parseCron(this.expr);
            this.error = '';
            this.description = describe(parsed);
            this.runs = nextRuns(parsed, new Date(), 5).map((d) =>
                d.toLocaleString(undefined, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }),
            );
        } catch (e) {
            this.error = e.message || 'Invalid cron expression.';
            this.description = '';
            this.runs = [];
        }
    },

    setPreset(expr) {
        this.expr = expr;
    },
}));
