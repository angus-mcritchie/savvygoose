// Schema-driven URL ↔ Alpine state binding.
//
// withUrlState(schema, factory) wraps an Alpine data factory so that every
// field listed in the schema is read from the query string on init, written
// back on change, and omitted when it equals its default. Each field can
// be { type, default, alias, min, max, maxLength, values, parse, serialize }.
// The wrapped component gains `url`, `urlTooLong`, `initFromUrl()`, and
// `updateUrl()`.

import LZString from 'lz-string';

// A string past its `maxLength` used to mean "no share link for you". It now
// gets one more chance: a compressed copy stashed under `<key>.z`, the trick
// the Mermaid editor has always used for its diagram source. Prose and markup
// roughly halve, so a value two or three times over the plain budget still
// fits, and the URL it produces is shorter than the percent-encoded original
// would have been.
const COMPRESSED_SUFFIX = '.z';

// Same budget the Mermaid editor settled on: an 8k query string is well
// inside what browsers, chat clients and link unfurlers tolerate.
const COMPRESSED_BUDGET = 8000;

// Below this, a value stays in the clear even when packing it would be
// shorter, so a link you can read and hand-edit stays that way. Past it
// nobody is reading the query string anyway, so length is all that's left
// to optimise for.
const PLAIN_BUDGET = 512;

// LZ decoding can expand a lot, so a hostile `?input.z=` gets a ceiling.
const MAX_DECOMPRESSED = 100000;

function compress(value) {
    try {
        return LZString.compressToEncodedURIComponent(value) || null;
    } catch {
        return null;
    }
}

function decompress(packed, budget) {
    if (!packed || packed.length > budget) return null;
    try {
        // LZString's URI-safe alphabet includes '+', which survives our own
        // URLSearchParams round-trip as %2B but comes back as a space from
        // anything that decoded the link along the way. A space can't appear
        // in the alphabet, so putting it back is unambiguous.
        const out = LZString.decompressFromEncodedURIComponent(packed.replace(/ /g, '+'));
        if (!out || out.length > MAX_DECOMPRESSED) return null;
        return out;
    } catch {
        return null;
    }
}

function defaultFor(def) {
    if (Object.prototype.hasOwnProperty.call(def, 'default')) return def.default;
    switch (def.type) {
        case 'string': return '';
        case 'number':
        case 'integer': return 0;
        case 'boolean': return false;
        case 'enum': return def.values?.[0] ?? '';
        case 'color': return '#000000';
        default: return null;
    }
}

function parseValue(raw, def, state) {
    if (def.parse) return def.parse(raw, state);

    switch (def.type) {
        case 'string': {
            if (def.values && !def.values.includes(raw)) return undefined;
            if (def.maxLength !== undefined && raw.length > def.maxLength) return undefined;
            return raw;
        }
        case 'number':
        case 'integer': {
            const n = def.type === 'integer' ? parseInt(raw, 10) : parseFloat(raw);
            if (!Number.isFinite(n)) return undefined;
            if (def.min !== undefined && n < def.min) return undefined;
            if (def.max !== undefined && n > def.max) return undefined;
            return n;
        }
        case 'boolean': {
            if (raw === '1' || raw === 'true') return true;
            if (raw === '0' || raw === 'false') return false;
            return undefined;
        }
        case 'enum': {
            return def.values?.includes(raw) ? raw : undefined;
        }
        case 'color': {
            const m = /^#?([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.exec(raw);
            if (!m) return undefined;
            let h = m[1];
            if (h.length === 3) h = h.split('').map((c) => c + c).join('');
            return '#' + h.toLowerCase();
        }
        default:
            return raw;
    }
}

function serializeByType(value, def) {
    switch (def.type) {
        case 'string': {
            if (value == null || value === '') return { skip: true };
            if (def.maxLength !== undefined && value.length > def.maxLength) {
                return { skip: true, tooLong: true };
            }
            return { value };
        }
        case 'number':
        case 'integer': {
            if (!Number.isFinite(value)) return { skip: true };
            return { value: String(value) };
        }
        case 'boolean': {
            return { value: value ? '1' : '0' };
        }
        case 'enum': {
            if (value == null) return { skip: true };
            return { value: String(value) };
        }
        case 'color': {
            if (!value) return { skip: true };
            return { value: String(value).replace(/^#/, '') };
        }
        default:
            return { value: String(value) };
    }
}

function pack(value, def) {
    if (typeof value !== 'string') return null;
    const out = compress(value);
    if (!out || out.length > (def.compressedMaxLength ?? COMPRESSED_BUDGET)) return null;
    return { value: out, compressed: true };
}

function serializeValue(value, def, state) {
    const result = def.serialize ? def.serialize(value, state) : serializeByType(value, def);

    // A field dropped purely for length gets a second chance: compressed, it
    // often fits, and a share link that works beats one that says it can't.
    // This catches the tools that budget across several fields at once too,
    // since they signal the same way.
    if (result.tooLong) return pack(value, def) ?? result;

    // Past the point where anyone reads the query string, pack whatever else
    // is long, provided packing actually wins.
    if (!result.skip && !result.compressed && typeof result.value === 'string') {
        const plain = encodeURIComponent(result.value).length;
        if (plain > PLAIN_BUDGET) {
            const out = pack(result.value, def);
            if (out && out.value.length < plain) return out;
        }
    }

    return result;
}

export function withUrlState(schema, factory) {
    return () => {
        const base = factory();
        const userInit = base.init;
        const userInitFromUrl = base.initFromUrl;
        const userUpdateUrl = base.updateUrl;

        // Build the wrapped scope by copying property descriptors from `base`
        // rather than spreading. Spread evaluates getters and replaces them with
        // their return values, which (a) breaks reactivity and (b) crashes if a
        // getter touches a schema field that only lives in stateDefaults.
        const wrapped = {};

        for (const [key, def] of Object.entries(schema)) {
            if (!(key in base)) {
                wrapped[key] = defaultFor(def);
            }
        }

        const baseDescriptors = Object.getOwnPropertyDescriptors(base);
        delete baseDescriptors.init;
        delete baseDescriptors.initFromUrl;
        delete baseDescriptors.updateUrl;
        delete baseDescriptors.url;
        delete baseDescriptors.urlTooLong;
        Object.defineProperties(wrapped, baseDescriptors);

        wrapped.url = 'url' in base ? base.url : window.location.href;
        wrapped.urlTooLong = 'urlTooLong' in base ? base.urlTooLong : false;

        wrapped.initFromUrl = function () {
            const params = new URLSearchParams(window.location.search);
            for (const [key, def] of Object.entries(schema)) {
                const urlKey = def.alias || key;
                let raw = params.get(urlKey);
                let activeDef = def;

                if (raw === null) {
                    const packed = params.get(urlKey + COMPRESSED_SUFFIX);
                    if (packed === null) continue;
                    raw = decompress(packed, def.compressedMaxLength ?? COMPRESSED_BUDGET);
                    if (raw === null) continue;
                    // `maxLength` caps how much text may sit in the URL in the
                    // clear, which is exactly what compressing it got around.
                    // MAX_DECOMPRESSED is the limit that applies here.
                    activeDef = { ...def, maxLength: undefined };
                }

                const parsed = parseValue(raw, activeDef, this);
                if (parsed !== undefined) this[key] = parsed;
            }
            if (userInitFromUrl) userInitFromUrl.call(this);
        };

        wrapped.updateUrl = function () {
            const params = new URLSearchParams(window.location.search);
            let urlTooLong = false;

            for (const [key, def] of Object.entries(schema)) {
                const urlKey = def.alias || key;
                const zKey = urlKey + COMPRESSED_SUFFIX;
                const value = this[key];
                const dflt = defaultFor(def);

                if (value === dflt || value == null) {
                    params.delete(urlKey);
                    params.delete(zKey);
                    continue;
                }

                const result = serializeValue(value, def, this);
                if (result.tooLong) urlTooLong = true;
                if (result.skip) {
                    params.delete(urlKey);
                    params.delete(zKey);
                } else if (result.compressed) {
                    params.delete(urlKey);
                    params.set(zKey, result.value);
                } else {
                    params.delete(zKey);
                    params.set(urlKey, result.value);
                }
            }

            this.urlTooLong = urlTooLong;
            const qs = params.toString();
            const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
            this.url = newUrl;
            window.history.replaceState({}, '', newUrl);

            if (userUpdateUrl) userUpdateUrl.call(this);
        };

        wrapped.init = function () {
            this.initFromUrl();
            for (const key of Object.keys(schema)) {
                this.$watch(key, () => this.updateUrl());
            }
            this.updateUrl();
            if (userInit) userInit.call(this);
        };

        return wrapped;
    };
}
