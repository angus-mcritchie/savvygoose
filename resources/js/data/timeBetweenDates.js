import { withUrlState } from '../lib/urlState';

const DAY_MS = 86400000;
const FALLBACK_COUNTRY = 'au';

function detectCountry(supported) {
    const set = new Set(supported);
    const candidates = [navigator.language, ...(navigator.languages || [])].filter(Boolean);
    for (const tag of candidates) {
        const match = /[-_]([A-Za-z]{2,3})\b/.exec(tag);
        if (!match) continue;
        const code = match[1].toLowerCase();
        if (set.has(code)) return code;
        if (code === 'gb' || code === 'uk') {
            for (const fallback of ['gb-eng', 'gb-sct', 'gb-cym', 'gb-nir']) {
                if (set.has(fallback)) return fallback;
            }
        }
    }
    return set.has(FALLBACK_COUNTRY) ? FALLBACK_COUNTRY : supported[0] || '';
}

function todayIso() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function parseIso(s) {
    if (!s || !/^\d{4}-\d{2}-\d{2}$/.test(s)) return null;
    const [y, m, d] = s.split('-').map(Number);
    const dt = new Date(Date.UTC(y, m - 1, d));
    if (dt.getUTCFullYear() !== y || dt.getUTCMonth() !== m - 1 || dt.getUTCDate() !== d) return null;
    return dt;
}

function formatIso(date) {
    return `${date.getUTCFullYear()}-${String(date.getUTCMonth() + 1).padStart(2, '0')}-${String(date.getUTCDate()).padStart(2, '0')}`;
}

function addDays(date, days) {
    return new Date(date.getTime() + days * DAY_MS);
}

function diffDays(a, b) {
    return Math.round((b.getTime() - a.getTime()) / DAY_MS);
}

function weekendDaysBetween(from, to) {
    const total = diffDays(from, to) + 1;
    if (total <= 0) return 0;

    const fullWeeks = Math.floor(total / 7);
    let weekends = fullWeeks * 2;

    const remainder = total - fullWeeks * 7;
    const fromDow = from.getUTCDay();
    for (let i = 0; i < remainder; i++) {
        const dow = (fromDow + i) % 7;
        if (dow === 0 || dow === 6) weekends++;
    }
    return weekends;
}

function calendarBreakdown(from, to) {
    if (diffDays(from, to) < 0) return '0 years, 0 days';
    let years = to.getUTCFullYear() - from.getUTCFullYear();
    let months = to.getUTCMonth() - from.getUTCMonth();
    let days = to.getUTCDate() - from.getUTCDate();

    if (days < 0) {
        months--;
        const prev = new Date(Date.UTC(to.getUTCFullYear(), to.getUTCMonth(), 0));
        days += prev.getUTCDate();
    }
    if (months < 0) {
        years--;
        months += 12;
    }

    const parts = [];
    if (years) parts.push(`${years} year${years === 1 ? '' : 's'}`);
    if (months) parts.push(`${months} month${months === 1 ? '' : 's'}`);
    parts.push(`${days} day${days === 1 ? '' : 's'}`);
    return parts.join(', ');
}

const schema = {
    start: {
        type: 'string',
        default: '',
        parse: (raw) => (parseIso(raw) ? raw : undefined),
    },
    end: {
        type: 'string',
        default: '',
        parse: (raw) => (parseIso(raw) ? raw : undefined),
    },
    country: {
        type: 'string',
        default: '',
        parse: (raw) => raw.toLowerCase(),
        serialize: (value, state) => {
            if (!value || value === state.defaultCountry) return { skip: true };
            return { value };
        },
    },
    inclusive: { type: 'boolean', default: true },
};

export default ({ supported = [] } = {}) => withUrlState(schema, () => ({
    supported,
    defaultCountry: detectCountry(supported),
    start: todayIso(),
    end: todayIso(),
    holidays: [],
    holidaysLoading: false,
    holidaysError: '',
    fetchToken: 0,

    init() {
        if (!this.country) this.country = this.defaultCountry;

        ['start', 'end', 'country'].forEach((prop) => {
            this.$watch(prop, () => this.fetchHolidays());
        });

        this.fetchHolidays();
    },

    get sortedRange() {
        const a = parseIso(this.start);
        const b = parseIso(this.end);
        if (!a || !b) return null;
        return a.getTime() <= b.getTime() ? { from: a, to: b } : { from: b, to: a };
    },

    get errorMessage() {
        if (!parseIso(this.start) || !parseIso(this.end)) return 'Pick a valid start and end date.';
        return '';
    },

    get holidaysInRange() {
        const range = this.sortedRange;
        if (!range) return [];
        const fromIso = formatIso(range.from);
        const toIso = formatIso(range.to);
        return this.holidays.filter((h) => h.date >= fromIso && h.date <= toIso);
    },

    get stats() {
        const range = this.sortedRange;
        const empty = { totalDays: 0, weekdays: 0, weekendDays: 0, holidays: 0, businessDays: 0, calendarBreakdown: '0 days' };
        if (!range) return empty;

        const inclusive = !!this.inclusive;
        const totalDays = diffDays(range.from, range.to) + (inclusive ? 1 : 0);
        if (totalDays <= 0) return empty;

        const weekendUpper = inclusive ? range.to : addDays(range.to, -1);
        const weekendDays = weekendDaysBetween(range.from, weekendUpper);
        const weekdays = totalDays - weekendDays;

        const fromIso = formatIso(range.from);
        const upperIso = formatIso(weekendUpper);
        const holidaysWeekday = this.holidays.filter((h) => {
            if (h.date < fromIso || h.date > upperIso) return false;
            const dow = parseIso(h.date)?.getUTCDay();
            return dow !== 0 && dow !== 6;
        }).length;
        const holidaysAll = this.holidays.filter((h) => h.date >= fromIso && h.date <= upperIso).length;

        const businessDays = Math.max(0, weekdays - holidaysWeekday);

        return {
            totalDays,
            weekdays,
            weekendDays,
            holidays: holidaysAll,
            businessDays,
            calendarBreakdown: calendarBreakdown(range.from, inclusive ? range.to : addDays(range.to, -1)),
        };
    },

    swap() {
        const tmp = this.start;
        this.start = this.end;
        this.end = tmp;
    },

    setPreset(preset) {
        const today = new Date();
        const t = new Date(Date.UTC(today.getFullYear(), today.getMonth(), today.getDate()));
        if (preset === 'today') {
            this.start = formatIso(t);
            this.end = formatIso(t);
        } else if (preset === 'next-30') {
            this.start = formatIso(t);
            this.end = formatIso(addDays(t, 30));
        } else if (preset === 'next-90') {
            this.start = formatIso(t);
            this.end = formatIso(addDays(t, 90));
        } else if (preset === 'this-year') {
            this.start = formatIso(new Date(Date.UTC(t.getUTCFullYear(), 0, 1)));
            this.end = formatIso(new Date(Date.UTC(t.getUTCFullYear(), 11, 31)));
        } else if (preset === 'next-year') {
            this.start = formatIso(new Date(Date.UTC(t.getUTCFullYear() + 1, 0, 1)));
            this.end = formatIso(new Date(Date.UTC(t.getUTCFullYear() + 1, 11, 31)));
        }
    },

    rangeSummary() {
        const range = this.sortedRange;
        if (!range) return '';
        const fmt = (d) => new Intl.DateTimeFormat(undefined, { dateStyle: 'long', timeZone: 'UTC' }).format(d);
        if (range.from.getTime() === range.to.getTime()) return fmt(range.from);
        return `${fmt(range.from)} → ${fmt(range.to)}`;
    },

    holidaysSubheading() {
        const n = this.holidaysInRange.length;
        if (this.holidaysError) return '';
        if (n === 0) return 'No public holidays fall in this range.';
        return `${n} ${n === 1 ? 'holiday' : 'holidays'} from the selected calendar.`;
    },

    formatHolidayDate(iso) {
        const d = parseIso(iso);
        if (!d) return iso;
        return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeZone: 'UTC' }).format(d);
    },

    formatNumber(n) {
        if (!Number.isFinite(n)) return '0';
        return new Intl.NumberFormat().format(Math.round(n));
    },

    formatDecimal(n) {
        if (!Number.isFinite(n)) return '0';
        return n.toFixed(2);
    },

    async fetchHolidays() {
        if (!this.country) {
            this.holidays = [];
            this.holidaysError = '';
            return;
        }
        const range = this.sortedRange;
        if (!range) return;

        const token = ++this.fetchToken;
        this.holidaysLoading = true;
        this.holidaysError = '';

        try {
            const params = new URLSearchParams({
                country: this.country,
                from: formatIso(new Date(Date.UTC(range.from.getUTCFullYear(), 0, 1))),
                to: formatIso(new Date(Date.UTC(range.to.getUTCFullYear(), 11, 31))),
            });
            const res = await fetch(`/api/holidays?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });
            if (token !== this.fetchToken) return;

            if (!res.ok) {
                const body = await res.json().catch(() => ({}));
                this.holidays = [];
                this.holidaysError = body.message || `Couldn't load holidays (${res.status}).`;
                return;
            }

            const data = await res.json();
            if (token !== this.fetchToken) return;
            this.holidays = data.holidays || [];
        } catch (_) {
            if (token === this.fetchToken) {
                this.holidays = [];
                this.holidaysError = "Couldn't load holidays. Check your connection.";
            }
        } finally {
            if (token === this.fetchToken) this.holidaysLoading = false;
        }
    },
}))();
