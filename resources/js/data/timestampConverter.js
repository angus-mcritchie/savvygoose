function browserTimezone() {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
    } catch (_) {
        return 'UTC';
    }
}

function tzOffsetMs(timestampMs, tz) {
    const d = new Date(timestampMs);
    const fmt = new Intl.DateTimeFormat('en-US', {
        timeZone: tz,
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
    });
    const parts = Object.fromEntries(fmt.formatToParts(d).map((p) => [p.type, p.value]));
    const hour = parts.hour === '24' ? 0 : +parts.hour;
    const asUTC = Date.UTC(+parts.year, +parts.month - 1, +parts.day, hour, +parts.minute, +parts.second);
    return asUTC - timestampMs;
}

function offsetToString(offsetMs) {
    const sign = offsetMs >= 0 ? '+' : '-';
    const abs = Math.abs(offsetMs);
    const hours = Math.floor(abs / 3600000);
    const minutes = Math.floor((abs % 3600000) / 60000);
    return `${sign}${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
}

function partsInZone(timestampMs, tz) {
    const fmt = new Intl.DateTimeFormat('en-US', {
        timeZone: tz,
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
    });
    const parts = Object.fromEntries(fmt.formatToParts(new Date(timestampMs)).map((p) => [p.type, p.value]));
    return {
        year: +parts.year,
        month: +parts.month,
        day: +parts.day,
        hour: parts.hour === '24' ? 0 : +parts.hour,
        minute: +parts.minute,
        second: +parts.second,
    };
}

function wallToUnixMs(year, month, day, hour, minute, second, tz) {
    const guess = Date.UTC(year, month - 1, day, hour, minute, second);
    const offset = tzOffsetMs(guess, tz);
    return guess - offset;
}

function smartParse(input) {
    const raw = (input || '').trim();
    if (!raw) return null;

    if (/^-?\d+(\.\d+)?$/.test(raw)) {
        const n = parseFloat(raw);
        if (!Number.isFinite(n)) return null;
        const abs = Math.abs(n);
        if (abs >= 1e12) return { ms: Math.round(n), source: 'unix-ms' };
        return { ms: Math.round(n * 1000), source: 'unix-s' };
    }

    const t = Date.parse(raw);
    if (Number.isFinite(t)) return { ms: t, source: 'iso' };

    return null;
}

function relative(ms, nowMs) {
    const diff = ms - nowMs;
    const abs = Math.abs(diff);
    const future = diff >= 0;
    const units = [
        { ms: 31536000000, name: 'year' },
        { ms: 2592000000, name: 'month' },
        { ms: 604800000, name: 'week' },
        { ms: 86400000, name: 'day' },
        { ms: 3600000, name: 'hour' },
        { ms: 60000, name: 'minute' },
        { ms: 1000, name: 'second' },
    ];
    for (const u of units) {
        if (abs >= u.ms) {
            const n = Math.floor(abs / u.ms);
            const label = `${n} ${u.name}${n === 1 ? '' : 's'}`;
            return future ? `in ${label}` : `${label} ago`;
        }
    }
    return 'just now';
}

const DEFAULTS = {
    tz: browserTimezone(),
};

export default () => ({
    tz: DEFAULTS.tz,
    unixMs: Date.now(),
    rawInput: '',
    parseError: '',
    parseHint: '',
    nowMs: Date.now(),
    nowTimer: null,
    url: window.location.href,
    copied: '',
    selectedDate: '',
    selectedTime: '',
    syncingPickers: false,

    init() {
        this.initFromUrl();
        this.rawInput = this.formatUnixSeconds(this.unixMs);
        this.syncPickersFromUnixMs();
        this.refreshHint();
        this.updateUrl();

        this.nowTimer = setInterval(() => { this.nowMs = Date.now(); }, 1000);

        this.$watch('tz', () => {
            this.syncPickersFromUnixMs();
            this.refreshHint();
            this.updateUrl();
        });

        this.$watch('unixMs', () => {
            this.syncPickersFromUnixMs();
            this.refreshHint();
            this.updateUrl();
        });

        this.$watch('selectedDate', (v) => {
            if (this.syncingPickers) return;
            this.setFromDateAndTime(v, this.timeInputValue());
        });

        this.$watch('selectedTime', (v) => {
            if (this.syncingPickers) return;
            this.setFromDateAndTime(this.selectedDate, v);
        });
    },

    destroy() {
        if (this.nowTimer) clearInterval(this.nowTimer);
    },

    parseRaw() {
        const parsed = smartParse(this.rawInput);
        if (!parsed) {
            this.parseError = this.rawInput.trim() ? 'Could not parse this timestamp.' : '';
            return;
        }
        this.parseError = '';
        this.unixMs = parsed.ms;
    },

    refreshHint() {
        const parsed = smartParse(this.rawInput);
        if (!parsed) { this.parseHint = ''; return; }
        const labels = { 'unix-s': 'Unix seconds', 'unix-ms': 'Unix milliseconds', iso: 'ISO 8601 / parsed' };
        this.parseHint = `Detected: ${labels[parsed.source]}`;
    },

    setNow() {
        this.unixMs = Date.now();
        this.rawInput = this.formatUnixSeconds(this.unixMs);
        this.parseError = '';
    },

    setUnixMs(ms) {
        this.unixMs = ms;
        this.rawInput = this.formatUnixSeconds(ms);
        this.parseError = '';
    },

    setFromDateAndTime(dateStr, timeStr) {
        if (!dateStr) return;
        const [y, mo, d] = dateStr.split('-').map(Number);
        const time = (timeStr || '00:00:00').padEnd(8, ':00');
        const [h, mi, s] = time.split(':').map(Number);
        if ([y, mo, d, h, mi].some((n) => !Number.isFinite(n))) return;
        const ms = wallToUnixMs(y, mo, d, h, mi, Number.isFinite(s) ? s : 0, this.tz);
        this.setUnixMs(ms);
    },

    formatUnixSeconds(ms) {
        return Math.floor(ms / 1000).toString();
    },

    unixSeconds() {
        return Math.floor(this.unixMs / 1000).toString();
    },

    unixMilliseconds() {
        return Math.round(this.unixMs).toString();
    },

    isoUtc() {
        return new Date(this.unixMs).toISOString();
    },

    isoZoned() {
        const p = partsInZone(this.unixMs, this.tz);
        const offset = offsetToString(-tzOffsetMs(this.unixMs, this.tz));
        const pad = (n, w = 2) => String(n).padStart(w, '0');
        return `${pad(p.year, 4)}-${pad(p.month)}-${pad(p.day)}T${pad(p.hour)}:${pad(p.minute)}:${pad(p.second)}${offset}`;
    },

    humanLong() {
        try {
            return new Intl.DateTimeFormat(undefined, {
                timeZone: this.tz,
                dateStyle: 'full',
                timeStyle: 'long',
            }).format(new Date(this.unixMs));
        } catch (_) {
            return new Date(this.unixMs).toString();
        }
    },

    nowFormatted() {
        try {
            return new Intl.DateTimeFormat(undefined, {
                timeZone: this.tz,
                dateStyle: 'medium',
                timeStyle: 'medium',
            }).format(new Date(this.nowMs));
        } catch (_) {
            return new Date(this.nowMs).toString();
        }
    },

    relative() {
        return relative(this.unixMs, this.nowMs);
    },

    dateInputValue() {
        const p = partsInZone(this.unixMs, this.tz);
        const pad = (n, w = 2) => String(n).padStart(w, '0');
        return `${pad(p.year, 4)}-${pad(p.month)}-${pad(p.day)}`;
    },

    timeInputValue() {
        const p = partsInZone(this.unixMs, this.tz);
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(p.hour)}:${pad(p.minute)}:${pad(p.second)}`;
    },

    timePickerValue() {
        const p = partsInZone(this.unixMs, this.tz);
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(p.hour)}:${pad(p.minute)}`;
    },

    syncPickersFromUnixMs() {
        this.syncingPickers = true;
        this.selectedDate = this.dateInputValue();
        this.selectedTime = this.timePickerValue();
        this.$nextTick(() => { this.syncingPickers = false; });
    },

    async copy(key, value) {
        try {
            await navigator.clipboard.writeText(value);
            this.copied = key;
            setTimeout(() => { if (this.copied === key) this.copied = ''; }, 1200);
        } catch (_) {}
    },

    initFromUrl() {
        const params = new URLSearchParams(window.location.search);

        if (params.has('tz')) {
            const tz = params.get('tz');
            try {
                new Intl.DateTimeFormat('en-US', { timeZone: tz });
                this.tz = tz;
            } catch (_) {}
        }

        if (params.has('ts')) {
            const raw = params.get('ts');
            const parsed = smartParse(raw);
            if (parsed) this.unixMs = parsed.ms;
        }
    },

    updateUrl() {
        const params = new URLSearchParams();
        params.set('ts', this.unixSeconds());
        if (this.tz !== DEFAULTS.tz) params.set('tz', this.tz);
        const qs = params.toString();
        const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
        this.url = newUrl;
        window.history.replaceState({}, '', newUrl);
    },
});
