import { withUrlState } from '../lib/urlState';

const STANDARD_CLAIMS = {
    iss: 'Issuer',
    sub: 'Subject',
    aud: 'Audience',
    exp: 'Expires',
    nbf: 'Not before',
    iat: 'Issued at',
    jti: 'JWT ID',
};

const TIME_CLAIMS = ['exp', 'nbf', 'iat'];
const MAX_URL_INPUT = 4000;

function base64UrlDecode(segment) {
    let s = segment.replace(/-/g, '+').replace(/_/g, '/');
    const pad = s.length % 4;
    if (pad) s += '='.repeat(4 - pad);
    const binary = atob(s);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
    return new TextDecoder().decode(bytes);
}

function formatRelative(seconds) {
    const abs = Math.abs(seconds);
    const future = seconds > 0;
    const units = [
        ['year', 60 * 60 * 24 * 365],
        ['month', 60 * 60 * 24 * 30],
        ['day', 60 * 60 * 24],
        ['hour', 60 * 60],
        ['minute', 60],
        ['second', 1],
    ];
    for (const [name, secs] of units) {
        if (abs >= secs || name === 'second') {
            const v = Math.floor(abs / secs);
            const label = `${v} ${name}${v === 1 ? '' : 's'}`;
            return future ? `in ${label}` : `${label} ago`;
        }
    }
    return '';
}

function formatTimestamp(seconds) {
    if (!Number.isFinite(seconds)) return '';
    try {
        return new Date(seconds * 1000).toLocaleString();
    } catch (e) {
        return '';
    }
}

const schema = {
    token: { type: 'string', maxLength: MAX_URL_INPUT },
};

export default withUrlState(schema, () => ({
    error: '',

    get parts() {
        return this.token.trim().split('.');
    },

    get isValidShape() {
        const p = this.parts;
        return p.length === 3 && p.every((s) => s.length > 0);
    },

    get headerJson() {
        return this.decodeSegment(0);
    },

    get payloadJson() {
        return this.decodeSegment(1);
    },

    get signature() {
        return this.parts[2] || '';
    },

    decodeSegment(index) {
        if (!this.token.trim()) return null;
        if (!this.isValidShape) return null;
        try {
            const decoded = base64UrlDecode(this.parts[index]);
            return JSON.parse(decoded);
        } catch (e) {
            return null;
        }
    },

    get prettyHeader() {
        const h = this.headerJson;
        return h ? JSON.stringify(h, null, 2) : '';
    },

    get prettyPayload() {
        const p = this.payloadJson;
        return p ? JSON.stringify(p, null, 2) : '';
    },

    get parseError() {
        if (!this.token.trim()) return '';
        if (!this.isValidShape) {
            return 'A JWT must have three dot-separated segments: header.payload.signature.';
        }
        if (this.headerJson === null) return 'Header is not valid Base64URL-encoded JSON.';
        if (this.payloadJson === null) return 'Payload is not valid Base64URL-encoded JSON.';
        return '';
    },

    get expiryStatus() {
        const p = this.payloadJson;
        if (!p || typeof p.exp !== 'number') return null;
        const now = Math.floor(Date.now() / 1000);
        const diff = p.exp - now;
        return {
            expired: diff <= 0,
            relative: formatRelative(diff),
            absolute: formatTimestamp(p.exp),
        };
    },

    get notYetActive() {
        const p = this.payloadJson;
        if (!p || typeof p.nbf !== 'number') return false;
        return p.nbf > Math.floor(Date.now() / 1000);
    },

    get standardClaims() {
        const p = this.payloadJson;
        if (!p) return [];
        return Object.entries(STANDARD_CLAIMS)
            .filter(([key]) => key in p)
            .map(([key, label]) => {
                const raw = p[key];
                let display = raw;
                let helper = '';
                if (TIME_CLAIMS.includes(key) && typeof raw === 'number') {
                    helper = formatTimestamp(raw);
                    const diff = raw - Math.floor(Date.now() / 1000);
                    helper += helper ? ` · ${formatRelative(diff)}` : formatRelative(diff);
                }
                if (typeof display === 'object') display = JSON.stringify(display);
                return { key, label, value: String(display), helper };
            });
    },

    get otherClaims() {
        const p = this.payloadJson;
        if (!p) return [];
        return Object.entries(p)
            .filter(([key]) => !(key in STANDARD_CLAIMS))
            .map(([key, raw]) => {
                let display = raw;
                if (typeof display === 'object') display = JSON.stringify(display);
                return { key, value: String(display) };
            });
    },

    clear() {
        this.token = '';
    },
}));
