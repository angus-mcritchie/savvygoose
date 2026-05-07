function parseUserAgent(ua) {
    let browser = 'Unknown';
    let browserVersion = '';
    let engine = 'Unknown';

    if (/Edg\//.test(ua)) {
        browser = 'Edge';
        browserVersion = ua.match(/Edg\/([\d.]+)/)?.[1] ?? '';
    } else if (/OPR\//.test(ua)) {
        browser = 'Opera';
        browserVersion = ua.match(/OPR\/([\d.]+)/)?.[1] ?? '';
    } else if (/Firefox\//.test(ua)) {
        browser = 'Firefox';
        browserVersion = ua.match(/Firefox\/([\d.]+)/)?.[1] ?? '';
    } else if (/Chrome\//.test(ua) && !/Chromium/.test(ua)) {
        browser = 'Chrome';
        browserVersion = ua.match(/Chrome\/([\d.]+)/)?.[1] ?? '';
    } else if (/Chromium\//.test(ua)) {
        browser = 'Chromium';
        browserVersion = ua.match(/Chromium\/([\d.]+)/)?.[1] ?? '';
    } else if (/Version\//.test(ua) && /Safari\//.test(ua)) {
        browser = 'Safari';
        browserVersion = ua.match(/Version\/([\d.]+)/)?.[1] ?? '';
    }

    if (/Gecko\/\d/.test(ua)) engine = 'Gecko';
    else if (/AppleWebKit/.test(ua)) engine = /Edg\/|Chrome\//.test(ua) ? 'Blink' : 'WebKit';

    let os = 'Unknown';
    let osVersion = '';
    if (/Windows NT/.test(ua)) {
        os = 'Windows';
        const v = ua.match(/Windows NT ([\d.]+)/)?.[1] ?? '';
        const map = { '10.0': '10 / 11', '6.3': '8.1', '6.2': '8', '6.1': '7' };
        osVersion = map[v] || v;
    } else if (/Mac OS X/.test(ua)) {
        os = 'macOS';
        osVersion = (ua.match(/Mac OS X ([\d_.]+)/)?.[1] ?? '').replace(/_/g, '.');
    } else if (/Android/.test(ua)) {
        os = 'Android';
        osVersion = ua.match(/Android ([\d.]+)/)?.[1] ?? '';
    } else if (/iPhone|iPad|iPod/.test(ua)) {
        os = /iPad/.test(ua) ? 'iPadOS' : 'iOS';
        osVersion = (ua.match(/OS ([\d_]+) like Mac/)?.[1] ?? '').replace(/_/g, '.');
    } else if (/CrOS/.test(ua)) {
        os = 'Chrome OS';
    } else if (/Linux/.test(ua)) {
        os = 'Linux';
    }

    return { browser, browserVersion, engine, os, osVersion };
}

export default () => ({
    viewportW: 0,
    viewportH: 0,
    online: true,
    colorScheme: 'Light',
    reducedMotion: false,
    copied: false,
    abort: null,

    init() {
        this.refreshViewport();
        this.refreshPrefs();

        this.abort = new AbortController();
        const { signal } = this.abort;

        window.addEventListener('resize', () => this.refreshViewport(), { signal });
        window.addEventListener('online', () => (this.online = navigator.onLine), { signal });
        window.addEventListener('offline', () => (this.online = navigator.onLine), { signal });

        const dark = window.matchMedia('(prefers-color-scheme: dark)');
        const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
        dark.addEventListener('change', () => this.refreshPrefs(), { signal });
        motion.addEventListener('change', () => this.refreshPrefs(), { signal });
    },

    destroy() {
        this.abort?.abort();
    },

    refreshViewport() {
        this.viewportW = window.innerWidth;
        this.viewportH = window.innerHeight;
    },

    refreshPrefs() {
        this.colorScheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'Dark' : 'Light';
        this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.online = navigator.onLine;
    },

    get ua() {
        return navigator.userAgent;
    },

    get parsed() {
        return parseUserAgent(this.ua);
    },

    get orientation() {
        return this.viewportW >= this.viewportH ? 'Landscape' : 'Portrait';
    },

    get screenW() {
        return window.screen?.width ?? 0;
    },

    get screenH() {
        return window.screen?.height ?? 0;
    },

    get availScreenW() {
        return window.screen?.availWidth ?? 0;
    },

    get availScreenH() {
        return window.screen?.availHeight ?? 0;
    },

    get dpr() {
        const v = window.devicePixelRatio || 1;
        return Math.round(v * 1000) / 1000;
    },

    get colorDepth() {
        return window.screen?.colorDepth ?? 0;
    },

    get languages() {
        const list = navigator.languages?.length ? [...navigator.languages] : [navigator.language];
        return list.filter(Boolean).join(', ');
    },

    get cookiesEnabled() {
        return !!navigator.cookieEnabled;
    },

    get touchPoints() {
        return navigator.maxTouchPoints ?? 0;
    },

    get cores() {
        return navigator.hardwareConcurrency || null;
    },

    get deviceMemory() {
        return 'deviceMemory' in navigator ? `${navigator.deviceMemory} GB` : null;
    },

    get connection() {
        const c = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (!c) return null;
        const parts = [];
        if (c.effectiveType) parts.push(c.effectiveType);
        if (typeof c.downlink === 'number') parts.push(`${c.downlink} Mb/s`);
        if (typeof c.rtt === 'number') parts.push(`${c.rtt} ms RTT`);
        if (c.saveData) parts.push('save-data');
        return parts.join(' · ') || null;
    },

    get timezone() {
        try {
            return Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch (e) {
            return '';
        }
    },

    get tzOffset() {
        const m = -new Date().getTimezoneOffset();
        const sign = m >= 0 ? '+' : '-';
        const abs = Math.abs(m);
        const hh = String(Math.floor(abs / 60)).padStart(2, '0');
        const mm = String(abs % 60).padStart(2, '0');
        return `UTC${sign}${hh}:${mm}`;
    },

    async copyUa() {
        try {
            await navigator.clipboard.writeText(this.ua);
            this.copied = true;
            setTimeout(() => (this.copied = false), 1500);
        } catch (e) {
            // ignore
        }
    },
});
