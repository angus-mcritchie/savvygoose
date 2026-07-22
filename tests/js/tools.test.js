import { describe, it, expect } from 'vitest';
import { reformatJson } from '../../resources/js/data/jsonFormatter';
import { coerceCell } from '../../resources/js/data/formatConverter';
import { smartParse } from '../../resources/js/data/timestampConverter';
import xPercentOfY from '../../resources/js/data/xPercentOfY';
import percentDiff from '../../resources/js/data/percentageDifferenceOfXAndY';

describe('jsonFormatter.reformatJson', () => {
    it('preserves big integers and huge exponents instead of rounding them', () => {
        const src = '{"id":12345678901234567890,"big":9999999999999999,"huge":1e400}';
        const out = reformatJson(src, '');
        expect(out).toContain('12345678901234567890');
        expect(out).toContain('9999999999999999');
        expect(out).toContain('1e400');
    });

    it('minifies to valid JSON', () => {
        const src = '{ "a" : 1 , "b" : [ 2 , 3 ] }';
        expect(reformatJson(src, '')).toBe('{"a":1,"b":[2,3]}');
    });

    it('pretty-prints with the given indent and keeps empty containers inline', () => {
        const out = reformatJson('{"a":[1,2],"empty":{}}', '  ');
        expect(out).toBe('{\n  "a": [\n    1,\n    2\n  ],\n  "empty": {}\n}');
    });
});

describe('formatConverter.coerceCell', () => {
    it('keeps leading-zero values as strings (zip codes, phone numbers)', () => {
        expect(coerceCell('07030')).toBe('07030');
        expect(coerceCell('0123456789')).toBe('0123456789');
    });

    it('keeps ids beyond safe-integer range as strings', () => {
        expect(coerceCell('12345678901234567890')).toBe('12345678901234567890');
    });

    it('does not coerce boolean-like words', () => {
        expect(coerceCell('true')).toBe('true');
        expect(coerceCell('false')).toBe('false');
    });

    it('coerces clean numbers that round-trip losslessly', () => {
        expect(coerceCell('30')).toBe(30);
        expect(coerceCell('3.14')).toBe(3.14);
        expect(coerceCell('-5')).toBe(-5);
    });

    it('keeps values whose numeric form would not round-trip', () => {
        expect(coerceCell('1.50')).toBe('1.50');
    });
});

describe('timestampConverter.smartParse', () => {
    it('detects seconds, milliseconds, microseconds, and nanoseconds', () => {
        expect(smartParse('1700000000').source).toBe('unix-s');
        expect(smartParse('1700000000000').source).toBe('unix-ms');
        expect(smartParse('1700000000000000').source).toBe('unix-us');
        expect(smartParse('1700000000000000000').source).toBe('unix-ns');
    });

    it('normalizes every unit to the same millisecond instant', () => {
        expect(smartParse('1700000000').ms).toBe(1700000000000);
        expect(smartParse('1700000000000').ms).toBe(1700000000000);
        expect(smartParse('1700000000000000').ms).toBe(1700000000000);
        expect(smartParse('1700000000000000000').ms).toBe(1700000000000);
    });

    it('rejects values that overflow the valid Date range instead of crashing', () => {
        expect(smartParse('999999999999999999999999999')).toBeNull();
    });
});

describe('percentage calculators handle zero operands', () => {
    it('x% of y returns 0 (not "--") when an operand is 0', () => {
        const c = xPercentOfY();
        c.x = 0; c.y = 500;
        expect(c.getResult()).toBe('0');
        c.x = 50; c.y = 0;
        expect(c.getResult()).toBe('0');
    });

    it('percent change to zero is -100%, not "--"', () => {
        const c = percentDiff();
        c.x = 100; c.y = 0;
        expect(c.getResult()).toContain('-100');
        expect(c.getResult()).toContain('%');
    });

    it('percent change from zero is undefined and shows "--"', () => {
        const c = percentDiff();
        c.x = 0; c.y = 100;
        expect(c.getResult()).toBe('--');
    });
});
