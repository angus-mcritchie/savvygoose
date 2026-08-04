import { describe, it, expect } from 'vitest';
import { buildQrGeometry, qrToSvg } from '../../resources/js/lib/qrRenderer';
import { FRAME_KEYS, buildFrameLayout, frameHasCaption } from '../../resources/js/lib/qrFrame';

const COUNT = 21;
const TOTAL = 29; // 21 modules plus a 4-module quiet zone on both sides

function geometry(options = {}) {
    return buildQrGeometry(new Uint8Array(COUNT * COUNT), COUNT, { margin: 4, ...options });
}

const CAPTIONED = FRAME_KEYS.filter((key) => frameHasCaption(key));
const PLAIN = FRAME_KEYS.filter((key) => key !== 'none' && !frameHasCaption(key));

describe('frame layout', () => {
    it('leaves the symbol alone when there is no frame', () => {
        expect(buildFrameLayout(TOTAL, { frame: 'none' })).toEqual({
            key: 'none', width: TOTAL, height: TOTAL, qr: { x: 0, y: 0 }, shapes: [], caption: null,
        });
    });

    it('falls back to no frame for an unknown key', () => {
        expect(buildFrameLayout(TOTAL, { frame: 'nope' }).key).toBe('none');
    });

    // The whole safety argument for frames is that they only ever add space
    // around the symbol, so the quiet zone the code carries stays intact.
    it('never draws inside the symbol', () => {
        for (const frame of FRAME_KEYS) {
            const layout = buildFrameLayout(TOTAL, { frame, caption: 'SCAN ME' });
            expect(layout.qr.x).toBeGreaterThanOrEqual(0);
            expect(layout.qr.y).toBeGreaterThanOrEqual(0);
            expect(layout.width).toBeGreaterThanOrEqual(layout.qr.x + TOTAL);
            expect(layout.height).toBeGreaterThanOrEqual(layout.qr.y + TOTAL);
        }
    });

    it('centres the symbol horizontally in every frame', () => {
        for (const frame of FRAME_KEYS) {
            const layout = buildFrameLayout(TOTAL, { frame, caption: 'SCAN ME' });
            expect(layout.qr.x).toBeCloseTo((layout.width - TOTAL) / 2, 6);
        }
    });

    it('stays square without a caption, and grows taller with one', () => {
        for (const frame of PLAIN) {
            const layout = buildFrameLayout(TOTAL, { frame });
            expect(layout.height).toBeCloseTo(layout.width, 6);
        }

        for (const frame of CAPTIONED) {
            const layout = buildFrameLayout(TOTAL, { frame, caption: 'SCAN ME' });
            expect(layout.height).toBeGreaterThan(layout.width);
        }
    });

    it('scales with the symbol rather than a fixed number of modules', () => {
        const small = buildFrameLayout(TOTAL, { frame: 'label', caption: 'SCAN ME' });
        const large = buildFrameLayout(TOTAL * 3, { frame: 'label', caption: 'SCAN ME' });

        expect(large.width / large.height).toBeCloseTo(small.width / small.height, 6);
    });

    it('carries a caption only on the frames that have somewhere to put one', () => {
        for (const frame of CAPTIONED) {
            expect(buildFrameLayout(TOTAL, { frame, caption: 'SCAN ME' }).caption.text).toBe('SCAN ME');
        }

        for (const frame of PLAIN) {
            expect(buildFrameLayout(TOTAL, { frame, caption: 'SCAN ME' }).caption).toBeNull();
        }
    });

    it('drops an empty or blank caption instead of drawing nothing', () => {
        expect(buildFrameLayout(TOTAL, { frame: 'label' }).caption).toBeNull();
        expect(buildFrameLayout(TOTAL, { frame: 'label', caption: '   ' }).caption).toBeNull();
    });

    it('keeps the caption inside the frame it sits in', () => {
        for (const frame of CAPTIONED) {
            const { caption, width, height } = buildFrameLayout(TOTAL, { frame, caption: 'SCAN ME' });
            expect(caption.x).toBeCloseTo(width / 2, 6);
            expect(caption.y).toBeGreaterThan(0);
            expect(caption.y).toBeLessThan(height);
        }
    });

    it('puts the caption above the code for label-top and below it for label', () => {
        const top = buildFrameLayout(TOTAL, { frame: 'label-top', caption: 'SCAN ME' });
        const bottom = buildFrameLayout(TOTAL, { frame: 'label', caption: 'SCAN ME' });

        expect(top.caption.y).toBeLessThan(top.qr.y);
        expect(bottom.caption.y).toBeGreaterThan(bottom.qr.y + TOTAL);
    });

    it('paints frame parts in the code colours rather than fixed ones', () => {
        for (const frame of FRAME_KEYS) {
            const layout = buildFrameLayout(TOTAL, { frame, caption: 'SCAN ME' });
            for (const shape of layout.shapes) {
                expect(['fg', 'bg']).toContain(shape.fill);
                expect(shape.d.length).toBeGreaterThan(0);
            }
        }
    });
});

describe('framed svg output', () => {
    it('sizes the viewport to the frame and keeps the export width', () => {
        const layout = buildFrameLayout(TOTAL, { frame: 'label', caption: 'SCAN ME' });
        const svg = qrToSvg(geometry(), { width: 256, frame: layout });

        expect(svg).toContain(`viewBox="0 0 ${layout.width} ${layout.height}"`);
        expect(svg).toContain('width="256"');
        expect(svg).toContain(`height="${Math.round((256 * layout.height) / layout.width * 1000) / 1000}"`);
    });

    it('shifts the symbol by the frame offset on top of the quiet zone', () => {
        const layout = buildFrameLayout(TOTAL, { frame: 'border' });
        const svg = qrToSvg(geometry({ margin: 4 }), { frame: layout });

        expect(svg).toContain(`transform="translate(${layout.qr.x + 4} ${layout.qr.y + 4})"`);
    });

    it('writes the caption as text, escaped', () => {
        const layout = buildFrameLayout(TOTAL, { frame: 'label', caption: 'Tom & Jerry <hi>' });
        const svg = qrToSvg(geometry(), { frame: layout });

        expect(svg).toContain('>Tom &amp; Jerry &lt;hi&gt;</text>');
        expect(svg).toContain('text-anchor="middle"');
    });

    it('skips frame parts whose colour is not set', () => {
        const layout = buildFrameLayout(TOTAL, { frame: 'label', caption: 'SCAN ME' });
        const svg = qrToSvg(geometry(), { bg: null, frame: layout });

        // The plate and the caption are drawn in the background colour.
        expect(svg).not.toContain('fill="null"');
        expect(svg).not.toContain('<text');
    });
});
