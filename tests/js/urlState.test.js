import { beforeEach, describe, it, expect, vi } from 'vitest';
import LZString from 'lz-string';
import { withUrlState } from '../../resources/js/lib/urlState';

// The wrapped scope expects an Alpine-ish host: a `$watch` it can register on
// and a `window.location` / `window.history` to read and rewrite.
function mount(schema, factory = () => ({}), search = '') {
    const url = new URL('https://savvygoose.com/markdown-converter' + search);
    const history = { replaceState: vi.fn() };

    vi.stubGlobal('window', {
        location: { search: url.search, origin: url.origin, pathname: url.pathname, href: url.href },
        history,
    });

    const scope = withUrlState(schema, factory)();
    scope.$watch = vi.fn();
    scope.init();

    return { scope, history };
}

const longText = 'The quick brown fox jumps over the lazy dog. '.repeat(60); // 2640 chars

// The URL this value would have produced in the clear, built the way
// updateUrl builds one. encodeURIComponent is not a stand-in: it spends three
// characters on a space where URLSearchParams spends one, so it overstates the
// plain form by about 40% and would let a packed URL "win" without trying.
function plainUrlFor(key, value) {
    return 'https://savvygoose.com/markdown-converter?' + new URLSearchParams([[key, value]]).toString();
}

// Deterministic pseudo-random alphanumerics: nothing for LZ to find, and
// URL-safe, so the plain form is exactly `n` characters long.
function incompressible(n) {
    const alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let seed = 1;
    let out = '';
    for (let i = 0; i < n; i++) {
        seed = (seed * 1103515245 + 12345) % 2147483648;
        out += alphabet[seed % alphabet.length];
    }
    return out;
}

beforeEach(() => {
    vi.unstubAllGlobals();
});

describe('withUrlState compression', () => {
    it('leaves a short value in the clear so the link stays readable', () => {
        const { scope } = mount({ input: { type: 'string', maxLength: 3000 } }, () => ({ input: 'hello world' }));

        expect(scope.url).toBe('https://savvygoose.com/markdown-converter?input=hello+world');
        expect(scope.urlTooLong).toBe(false);
    });

    it('packs a value that is still inside its budget but past readable length', () => {
        const { scope } = mount({ input: { type: 'string', maxLength: 3000 } }, () => ({ input: longText }));

        const params = new URLSearchParams(new URL(scope.url).search);
        expect(params.has('input.z')).toBe(true);
        expect(scope.url.length).toBeLessThan(plainUrlFor('input', longText).length);
        expect(LZString.decompressFromEncodedURIComponent(params.get('input.z'))).toBe(longText);
    });

    it('leaves a value in the clear when packing it would not be shorter', () => {
        const noise = incompressible(700);
        const { scope } = mount({ input: { type: 'string', maxLength: 3000 } }, () => ({ input: noise }));

        expect(LZString.compressToEncodedURIComponent(noise).length).toBeGreaterThan(noise.length);

        const params = new URLSearchParams(new URL(scope.url).search);
        expect(params.get('input')).toBe(noise);
        expect(params.has('input.z')).toBe(false);
    });

    it('does not pack a value that only looks like a win under encodeURIComponent', () => {
        // Space-heavy incompressible text: encodeURIComponent spends three
        // characters on each space and calls packing a win, URLSearchParams
        // spends one and knows better. Packing here would lengthen the URL.
        const spaced = incompressible(800).replace(/(.{4})/g, '$1 ').trim();
        expect(LZString.compressToEncodedURIComponent(spaced).length)
            .toBeLessThan(encodeURIComponent(spaced).length);

        const { scope } = mount({ input: { type: 'string' } }, () => ({ input: spaced }));

        const params = new URLSearchParams(new URL(scope.url).search);
        expect(params.has('input.z')).toBe(false);
        expect(params.get('input')).toBe(spaced);
    });

    it('holds the compressed budget against the query string, not the decoded value', () => {
        const packed = LZString.compressToEncodedURIComponent(longText);
        expect(packed).toContain('+'); // three characters once URLSearchParams writes it

        const overBudget = (budget) => {
            const schema = { input: { type: 'string', maxLength: 10, compressedMaxLength: budget } };
            return mount(schema, () => ({ input: longText })).scope;
        };

        // A budget of exactly the decoded length fits by the wrong measure.
        expect(overBudget(packed.length).urlTooLong).toBe(true);
        expect(overBudget(new URLSearchParams([['', packed]]).toString().length - 1).urlTooLong).toBe(false);
    });

    it('compresses an over-budget value into <key>.z instead of dropping it', () => {
        const { scope } = mount({ input: { type: 'string', maxLength: 1000 } }, () => ({ input: longText }));

        const params = new URLSearchParams(new URL(scope.url).search);
        expect(params.has('input')).toBe(false);
        expect(scope.urlTooLong).toBe(false);
        expect(LZString.decompressFromEncodedURIComponent(params.get('input.z'))).toBe(longText);
    });

    it('produces a shorter URL than the plain value would have', () => {
        const { scope } = mount({ input: { type: 'string', maxLength: 1000 } }, () => ({ input: longText }));

        expect(scope.url.length).toBeLessThan(plainUrlFor('input', longText).length);
    });

    it('reads a compressed value back out of the URL', () => {
        const packed = LZString.compressToEncodedURIComponent(longText);
        const { scope } = mount(
            { input: { type: 'string', maxLength: 1000 } },
            () => ({ input: '' }),
            '?input.z=' + encodeURIComponent(packed),
        );

        expect(scope.input).toBe(longText);
    });

    it('honours an alias when choosing the compressed key', () => {
        const { scope } = mount({ text: { type: 'string', maxLength: 1000, alias: 'q' } }, () => ({ text: longText }));

        const params = new URLSearchParams(new URL(scope.url).search);
        expect(params.has('q.z')).toBe(true);
        expect(params.has('text.z')).toBe(false);
    });

    it('recovers a link whose + characters were decoded into spaces in transit', () => {
        const packed = LZString.compressToEncodedURIComponent(longText);
        expect(packed).toContain('+');

        const { scope } = mount(
            { input: { type: 'string', maxLength: 1000 } },
            () => ({ input: '' }),
            '?input.z=' + packed, // a literal '+' decodes to a space
        );

        expect(scope.input).toBe(longText);
    });

    it('still gives up, and says so, when even the compressed form busts the budget', () => {
        // Nothing to compress, so this stays over the 8k ceiling either way.
        const noise = incompressible(30000);
        const { scope } = mount({ input: { type: 'string', maxLength: 3000 } }, () => ({ input: noise }));

        expect(new URL(scope.url).search).toBe('');
        expect(scope.urlTooLong).toBe(true);
    });

    it('respects a per-field compressedMaxLength', () => {
        const schema = { input: { type: 'string', maxLength: 100, compressedMaxLength: 50 } };
        const { scope } = mount(schema, () => ({ input: longText }));

        expect(new URL(scope.url).search).toBe('');
        expect(scope.urlTooLong).toBe(true);
    });

    it('ignores a compressed param that does not decode', () => {
        const { scope } = mount(
            { input: { type: 'string', maxLength: 1000, default: 'fallback' } },
            () => ({ input: 'fallback' }),
            '?input.z=not-actually-compressed',
        );

        expect(scope.input).toBe('fallback');
    });

    it('prefers the plain param when both are present', () => {
        const packed = LZString.compressToEncodedURIComponent(longText);
        const { scope } = mount(
            { input: { type: 'string', maxLength: 1000 } },
            () => ({ input: '' }),
            '?input=plain&input.z=' + encodeURIComponent(packed),
        );

        expect(scope.input).toBe('plain');
    });

    it('clears a stale compressed param once the value fits again', () => {
        const packed = LZString.compressToEncodedURIComponent(longText);
        const { scope } = mount(
            { input: { type: 'string', maxLength: 1000 } },
            () => ({ input: '' }),
            '?input.z=' + encodeURIComponent(packed),
        );

        scope.input = 'short';
        scope.updateUrl();

        expect(new URL(scope.url).search).toBe('?input=short');
    });

    it('clears both keys when the value returns to its default', () => {
        const { scope } = mount({ input: { type: 'string', maxLength: 1000 } }, () => ({ input: longText }));

        scope.input = '';
        scope.updateUrl();

        expect(new URL(scope.url).search).toBe('');
    });

    it('rescues a value that the tool\'s own serializer dropped for length', () => {
        // How base64-encoder, hash-generator, diff-viewer and regex-tester all
        // signal "too long", including the ones budgeting across fields.
        const schema = {
            input: {
                type: 'string',
                serialize: (value) => (value.length > 100 ? { skip: true, tooLong: true } : { value }),
            },
        };
        const { scope } = mount(schema, () => ({ input: longText }));

        const params = new URLSearchParams(new URL(scope.url).search);
        expect(scope.urlTooLong).toBe(false);
        expect(LZString.decompressFromEncodedURIComponent(params.get('input.z'))).toBe(longText);
    });

    it('leaves the tool\'s verdict alone when compression cannot rescue it either', () => {
        const schema = {
            input: {
                type: 'string',
                compressedMaxLength: 50,
                serialize: (value) => (value.length > 100 ? { skip: true, tooLong: true } : { value }),
            },
        };
        const { scope } = mount(schema, () => ({ input: longText }));

        expect(new URL(scope.url).search).toBe('');
        expect(scope.urlTooLong).toBe(true);
    });

    it('does not re-pack a serializer that already compressed its own value', () => {
        // The Mermaid editor's shape: it packs into the plain key itself, and
        // existing links depend on that key staying put.
        const schema = {
            code: {
                type: 'string',
                default: '',
                serialize: (value) => ({ value: LZString.compressToEncodedURIComponent(value) }),
            },
        };
        const { scope } = mount(schema, () => ({ code: longText }));

        const params = new URLSearchParams(new URL(scope.url).search);
        expect(params.has('code.z')).toBe(false);
        expect(LZString.decompressFromEncodedURIComponent(params.get('code'))).toBe(longText);
    });

    it('does not touch fields that bring their own serializer', () => {
        const schema = {
            code: {
                type: 'string',
                default: '',
                parse: (raw) => raw.toUpperCase(),
                serialize: (value) => ({ value: value.toLowerCase() }),
            },
        };
        const { scope } = mount(schema, () => ({ code: 'MiXeD' }));

        expect(new URL(scope.url).search).toBe('?code=mixed');
    });
});
