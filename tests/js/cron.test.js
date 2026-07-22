import { describe as descTest, it, expect } from 'vitest';
import { parseCron, describe, nextRuns } from '../../resources/js/data/cronExpression';

descTest('cron parsing and validation', () => {
    it('rejects the wrong number of fields', () => {
        expect(() => parseCron('* * * *')).toThrow();
        expect(() => parseCron('* * * * * *')).toThrow();
    });

    it('rejects out-of-range values', () => {
        expect(() => parseCron('60 * * * *')).toThrow();
        expect(() => parseCron('* 24 * * *')).toThrow();
    });

    it('rejects malformed steps and ranges instead of partially parsing them', () => {
        expect(() => parseCron('*/2x * * * *')).toThrow('bad step');
        expect(() => parseCron('5/15 * * * *')).toThrow('needs * or a range');
        expect(() => parseCron('1-2-3 * * * *')).toThrow('bad range');
    });

    it('accepts steps, ranges, lists, and names', () => {
        expect(() => parseCron('*/5 * * * *')).not.toThrow();
        expect(() => parseCron('0 9 * * 1-5')).not.toThrow();
        expect(() => parseCron('0 0 1,15 * *')).not.toThrow();
        expect(() => parseCron('0 0 * jan mon')).not.toThrow();
    });

    it('treats day-of-week 7 as Sunday (0)', () => {
        const parsed = parseCron('0 0 * * 7');
        expect(parsed.sets.dow.has(0)).toBe(true);
        expect(parsed.sets.dow.has(7)).toBe(false);
    });
});

descTest('cron descriptions', () => {
    it('describes common expressions', () => {
        expect(describe(parseCron('* * * * *'))).toContain('Every minute');
        expect(describe(parseCron('*/5 * * * *'))).toContain('Every 5 minutes');
        expect(describe(parseCron('0 9 * * *'))).toContain('At 09:00');
        expect(describe(parseCron('0 9 * * 1-5'))).toContain('Monday');
    });

    it('makes the OR relationship between both restricted day fields explicit', () => {
        expect(describe(parseCron('0 9 1 * 1'))).toContain('either day-of-month 1 or Monday matches');
    });
});

descTest('cron next runs', () => {
    it('computes the next matching times from a fixed start', () => {
        // Wednesday 2026-01-07 08:00 local
        const from = new Date(2026, 0, 7, 8, 0, 0);
        const runs = nextRuns(parseCron('0 9 * * 1-5'), from, 3);
        expect(runs).toHaveLength(3);
        // First run: same day 09:00 (Wednesday is a weekday)
        expect(runs[0].getHours()).toBe(9);
        expect(runs[0].getMinutes()).toBe(0);
        expect(runs[0].getDate()).toBe(7);
    });

    it('skips to the next valid day for weekday-only jobs', () => {
        // Saturday 2026-01-10 08:00 local
        const from = new Date(2026, 0, 10, 8, 0, 0);
        const runs = nextRuns(parseCron('0 9 * * 1-5'), from, 1);
        // Next weekday run is Monday 2026-01-12
        expect(runs[0].getDay()).toBe(1);
        expect(runs[0].getDate()).toBe(12);
    });
});
