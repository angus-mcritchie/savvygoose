import { describe, it, expect } from 'vitest';
import {
    EYE_SHAPE_KEYS,
    MODULE_SHAPE_KEYS,
    QR_PRESETS,
    buildQrGeometry,
    clearAreaFor,
    qrToSvg,
} from '../../resources/js/lib/qrRenderer';

const COUNT = 21;

function matrix(cells = []) {
    const data = new Uint8Array(COUNT * COUNT);
    for (const [row, col] of cells) data[row * COUNT + col] = 1;
    return data;
}

function geometryFor(cells, options = {}) {
    return buildQrGeometry(matrix(cells), COUNT, { margin: 4, ...options });
}

describe('qr geometry', () => {
    it('sizes the viewport as the symbol plus a quiet zone on both sides', () => {
        const geo = geometryFor([], { margin: 4 });
        expect(geo.count).toBe(21);
        expect(geo.margin).toBe(4);
        expect(geo.total).toBe(29);
    });

    it('clamps a negative or unusable margin to zero', () => {
        expect(geometryFor([], { margin: -3 }).margin).toBe(0);
        expect(geometryFor([], { margin: NaN }).margin).toBe(0);
    });

    it('falls back to square for an unknown shape', () => {
        const geo = geometryFor([], { module: 'nope', eye: 'nope' });
        expect(geo.module).toBe('square');
        expect(geo.eye).toBe('square');
    });

    it('only claims crisp edges when both shapes are square', () => {
        expect(geometryFor([], { module: 'square', eye: 'square' }).crisp).toBe(true);
        expect(geometryFor([], { module: 'dot', eye: 'square' }).crisp).toBe(false);
        expect(geometryFor([], { module: 'square', eye: 'circle' }).crisp).toBe(false);
    });

    it('draws a data module as a unit square in the square shape', () => {
        expect(geometryFor([[10, 10]]).paths.modules).toBe('M10 10H11V11H10Z');
    });

    it('leaves the three finder patterns out of the module path', () => {
        // One module inside each finder area, plus one ordinary data module.
        const geo = geometryFor([[0, 0], [3, 18], [18, 3], [10, 10]]);
        expect(geo.paths.modules).toBe('M10 10H11V11H10Z');
    });

    it('always emits three eye frames and three eye balls on a v1 symbol', () => {
        for (const eye of EYE_SHAPE_KEYS) {
            const geo = geometryFor([], { eye });
            // Each frame is an outer shape plus a punched-out inner shape.
            expect(geo.paths.eyeFrames.match(/M/g)).toHaveLength(6);
            expect(geo.paths.eyeBalls.match(/M/g)).toHaveLength(3);
        }
    });

    it('renders dot modules as circles', () => {
        const geo = geometryFor([[10, 10]], { module: 'dot' });
        expect(geo.paths.modules).toBe('M10.05 10.5A0.45 0.45 0 1 0 10.95 10.5A0.45 0.45 0 1 0 10.05 10.5Z');
    });

    it('rounds every corner of an isolated fluid module', () => {
        const geo = geometryFor([[10, 10]], { module: 'fluid' });
        expect(geo.paths.modules.match(/A0\.5 0\.5/g)).toHaveLength(4);
    });

    it('keeps fluid corners square where a neighbour continues the run', () => {
        // A horizontal pair: the touching corners stay sharp, the outer ends round.
        const geo = geometryFor([[10, 10], [10, 11]], { module: 'fluid' });
        expect(geo.paths.modules.match(/A0\.5 0\.5/g)).toHaveLength(4);
    });

    // A true circular ring only holds the 1:1:3:1:1 finder ratio on the scan
    // line through its centre, which some decoders reject outright.
    it('draws the circle eye as a rounded square, not a real circle', () => {
        const geo = geometryFor([], { eye: 'circle' });
        expect(geo.paths.eyeFrames).not.toContain('A3.5 3.5');
        expect(geo.paths.eyeFrames).toContain('A2.6 2.6');
    });

    it('offers a preset for every shape it names', () => {
        for (const preset of Object.values(QR_PRESETS)) {
            expect(MODULE_SHAPE_KEYS).toContain(preset.module);
            expect(EYE_SHAPE_KEYS).toContain(preset.eye);
        }
    });
});

describe('qr svg output', () => {
    it('matches the viewBox to the total module count and offsets the quiet zone', () => {
        const svg = qrToSvg(geometryFor([[10, 10]]), { width: 256 });
        expect(svg).toContain('viewBox="0 0 29 29"');
        expect(svg).toContain('width="256" height="256"');
        expect(svg).toContain('transform="translate(4 4)"');
    });

    it('fills a background rect only when a background color is given', () => {
        expect(qrToSvg(geometryFor([]), { bg: '#ffffff' })).toContain('<rect width="29" height="29" fill="#ffffff"/>');
        expect(qrToSvg(geometryFor([]), { bg: null })).not.toContain('<rect');
    });

    it('uses evenodd so the eye frames read as rings', () => {
        expect(qrToSvg(geometryFor([]))).toContain('fill-rule="evenodd"');
    });

    it('asks for crisp edges on square output only', () => {
        expect(qrToSvg(geometryFor([]))).toContain('shape-rendering="crispEdges"');
        expect(qrToSvg(geometryFor([], { module: 'dot' }))).not.toContain('shape-rendering');
    });

    it('appends overlay markup inside the root element, in the symbol\'s own coordinates', () => {
        const svg = qrToSvg(geometryFor([]), { overlay: '<image href="x"/>' });
        expect(svg.endsWith('<g transform="translate(0 0)"><image href="x"/></g></svg>')).toBe(true);
    });
});

describe('logo clear area', () => {
    it('snaps outward to whole modules so nothing is left half-covered', () => {
        expect(clearAreaFor({ x: 8.4, y: 8.4, w: 4.2, h: 4.2 })).toEqual({ x: 8, y: 8, w: 5, h: 5 });
    });

    it('grows the area by the padding on every side', () => {
        expect(clearAreaFor({ x: 8, y: 8, w: 4, h: 4 }, 1)).toEqual({ x: 7, y: 7, w: 6, h: 6 });
    });

    it('drops every module the logo touches, and no others', () => {
        // A full 21x21 grid, with the centre 5x5 reserved.
        const all = new Uint8Array(COUNT * COUNT).fill(1);
        const clear = clearAreaFor({ x: 8, y: 8, w: 5, h: 5 });
        const geo = buildQrGeometry(all, COUNT, { margin: 0, clear });

        // Data modules are everything outside the three 7x7 finders.
        const dataModules = COUNT * COUNT - 3 * 49;
        expect(geo.paths.modules.match(/M/g)).toHaveLength(dataModules - 25);
    });

    it('leaves the code untouched when there is no logo', () => {
        const all = new Uint8Array(COUNT * COUNT).fill(1);
        const geo = buildQrGeometry(all, COUNT, { margin: 0, clear: null });
        expect(geo.paths.modules.match(/M/g)).toHaveLength(COUNT * COUNT - 3 * 49);
    });

    it('rounds fluid modules against the cleared edge as though it were empty', () => {
        // A single row of three, with the rightmost cleared away: the middle
        // module becomes the new end of the run and rounds on that side.
        const cells = [[10, 9], [10, 10], [10, 11]];
        const open = geometryFor(cells, { module: 'fluid', margin: 0 });
        const cut = geometryFor(cells, { module: 'fluid', margin: 0, clear: { x: 11, y: 10, w: 1, h: 1 } });

        expect(open.paths.modules.match(/A0\.5 0\.5/g)).toHaveLength(4);
        expect(cut.paths.modules.match(/A0\.5 0\.5/g)).toHaveLength(4);
        expect(cut.paths.modules.match(/M/g)).toHaveLength(2);
    });
});
