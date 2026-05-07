// Schema-driven URL ↔ Alpine state binding.
//
// withUrlState(schema, factory) wraps an Alpine data factory so that every
// field listed in the schema is read from the query string on init, written
// back on change, and omitted when it equals its default. Each field can
// be { type, default, alias, min, max, maxLength, values, parse, serialize }.
// The wrapped component gains `url`, `urlTooLong`, `initFromUrl()`, and
// `updateUrl()`.

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

function serializeValue(value, def, state) {
    if (def.serialize) return def.serialize(value, state);

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

export function withUrlState(schema, factory) {
    return () => {
        const base = factory();
        const userInit = base.init;
        const userInitFromUrl = base.initFromUrl;
        const userUpdateUrl = base.updateUrl;

        const stateDefaults = {};
        for (const [key, def] of Object.entries(schema)) {
            if (!(key in base)) {
                stateDefaults[key] = defaultFor(def);
            }
        }

        const wrapped = {
            ...stateDefaults,
            ...base,
            url: 'url' in base ? base.url : window.location.href,
            urlTooLong: 'urlTooLong' in base ? base.urlTooLong : false,

            initFromUrl() {
                const params = new URLSearchParams(window.location.search);
                for (const [key, def] of Object.entries(schema)) {
                    const urlKey = def.alias || key;
                    if (!params.has(urlKey)) continue;
                    const raw = params.get(urlKey);
                    const parsed = parseValue(raw, def, this);
                    if (parsed !== undefined) this[key] = parsed;
                }
                if (userInitFromUrl) userInitFromUrl.call(this);
            },

            updateUrl() {
                const params = new URLSearchParams(window.location.search);
                let urlTooLong = false;

                for (const [key, def] of Object.entries(schema)) {
                    const urlKey = def.alias || key;
                    const value = this[key];
                    const dflt = defaultFor(def);

                    if (value === dflt || value == null) {
                        params.delete(urlKey);
                        continue;
                    }

                    const result = serializeValue(value, def, this);
                    if (result.tooLong) urlTooLong = true;
                    if (result.skip) {
                        params.delete(urlKey);
                    } else {
                        params.set(urlKey, result.value);
                    }
                }

                this.urlTooLong = urlTooLong;
                const qs = params.toString();
                const newUrl = `${window.location.origin}${window.location.pathname}${qs ? '?' + qs : ''}`;
                this.url = newUrl;
                window.history.replaceState({}, '', newUrl);

                if (userUpdateUrl) userUpdateUrl.call(this);
            },

            init() {
                this.initFromUrl();
                for (const key of Object.keys(schema)) {
                    this.$watch(key, () => this.updateUrl());
                }
                this.updateUrl();
                if (userInit) userInit.call(this);
            },
        };

        return wrapped;
    };
}
