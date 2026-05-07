import { withUrlState } from '../lib/urlState';

const HEX_RE = /^#?([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/;

function clamp(n, lo, hi) {
    return Math.min(hi, Math.max(lo, n));
}

function normalizeHex(input) {
    if (!input) return null;
    const m = String(input).trim().match(HEX_RE);
    if (!m) return null;
    let h = m[1];
    if (h.length === 3) h = h.split('').map((c) => c + c).join('');
    return '#' + h.toLowerCase();
}

function hexToRgb(hex) {
    const h = hex.replace('#', '');
    return {
        r: parseInt(h.slice(0, 2), 16),
        g: parseInt(h.slice(2, 4), 16),
        b: parseInt(h.slice(4, 6), 16),
    };
}

function rgbToHex(r, g, b) {
    const to = (n) => clamp(Math.round(n), 0, 255).toString(16).padStart(2, '0');
    return '#' + to(r) + to(g) + to(b);
}

function rgbToHsl(r, g, b) {
    const rn = r / 255, gn = g / 255, bn = b / 255;
    const max = Math.max(rn, gn, bn);
    const min = Math.min(rn, gn, bn);
    const l = (max + min) / 2;
    let h = 0;
    let s = 0;
    if (max !== min) {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
            case rn: h = (gn - bn) / d + (gn < bn ? 6 : 0); break;
            case gn: h = (bn - rn) / d + 2; break;
            case bn: h = (rn - gn) / d + 4; break;
        }
        h *= 60;
    }
    return {
        h: Math.round(h),
        s: Math.round(s * 100),
        l: Math.round(l * 100),
    };
}

function hslToRgb(h, s, l) {
    const hn = ((h % 360) + 360) % 360 / 360;
    const sn = clamp(s, 0, 100) / 100;
    const ln = clamp(l, 0, 100) / 100;
    if (sn === 0) {
        const v = Math.round(ln * 255);
        return { r: v, g: v, b: v };
    }
    const q = ln < 0.5 ? ln * (1 + sn) : ln + sn - ln * sn;
    const p = 2 * ln - q;
    const hue = (t) => {
        if (t < 0) t += 1;
        if (t > 1) t -= 1;
        if (t < 1 / 6) return p + (q - p) * 6 * t;
        if (t < 1 / 2) return q;
        if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
        return p;
    };
    return {
        r: Math.round(hue(hn + 1 / 3) * 255),
        g: Math.round(hue(hn) * 255),
        b: Math.round(hue(hn - 1 / 3) * 255),
    };
}

function relativeLuminance({ r, g, b }) {
    const adj = (v) => {
        const x = v / 255;
        return x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4);
    };
    return 0.2126 * adj(r) + 0.7152 * adj(g) + 0.0722 * adj(b);
}

function contrastRatio(hexA, hexB) {
    const la = relativeLuminance(hexToRgb(hexA));
    const lb = relativeLuminance(hexToRgb(hexB));
    const [light, dark] = la > lb ? [la, lb] : [lb, la];
    return (light + 0.05) / (dark + 0.05);
}

const schema = {
    fg: { type: 'color', default: '#2563eb', alias: 'c' },
    bg: { type: 'color', default: '#ffffff' },
};

export default withUrlState(schema, () => ({
    init() {
        ['fg', 'bg'].forEach((p) => this.$watch(p, () => {
            const norm = normalizeHex(this[p]);
            if (norm && norm !== this[p]) this[p] = norm;
        }));
    },

    setRgb(target, channel, value) {
        const v = clamp(parseInt(value, 10) || 0, 0, 255);
        const cur = hexToRgb(this[target]);
        cur[channel] = v;
        this[target] = rgbToHex(cur.r, cur.g, cur.b);
    },

    setHsl(target, channel, value) {
        const cur = rgbToHsl(...Object.values(hexToRgb(this[target])));
        const v = parseInt(value, 10);
        if (!Number.isFinite(v)) return;
        if (channel === 'h') cur.h = ((v % 360) + 360) % 360;
        if (channel === 's') cur.s = clamp(v, 0, 100);
        if (channel === 'l') cur.l = clamp(v, 0, 100);
        const rgb = hslToRgb(cur.h, cur.s, cur.l);
        this[target] = rgbToHex(rgb.r, rgb.g, rgb.b);
    },

    rgb(target) {
        return hexToRgb(this[target]);
    },

    hsl(target) {
        const { r, g, b } = hexToRgb(this[target]);
        return rgbToHsl(r, g, b);
    },

    rgbString(target) {
        const { r, g, b } = this.rgb(target);
        return `rgb(${r}, ${g}, ${b})`;
    },

    hslString(target) {
        const { h, s, l } = this.hsl(target);
        return `hsl(${h}, ${s}%, ${l}%)`;
    },

    swap() {
        const tmp = this.fg;
        this.fg = this.bg;
        this.bg = tmp;
    },

    contrast() {
        return contrastRatio(this.fg, this.bg);
    },

    contrastFormatted() {
        return this.contrast().toFixed(2) + ':1';
    },

    rating(level, size) {
        const ratio = this.contrast();
        if (level === 'AA' && size === 'normal') return ratio >= 4.5;
        if (level === 'AA' && size === 'large') return ratio >= 3;
        if (level === 'AAA' && size === 'normal') return ratio >= 7;
        if (level === 'AAA' && size === 'large') return ratio >= 4.5;
        return false;
    },
}));
