// Frames that wrap a finished symbol.
//
// A frame never touches the module grid. It only adds space around the code
// and draws into that space, so anything here is safe by construction: the
// quiet zone the symbol already carries stays intact underneath.
//
// Everything is expressed in module units (1 unit = 1 QR module) and sized as a
// fraction of the symbol, so a frame looks the same whether it wraps a sparse
// version 1 code or a dense version 40 one. buildFrameLayout() hands back a
// plain layout that qrToSvg() and qrToCanvas() know how to paint.

import { roundedRectPath } from './qrRenderer';

// A stack every browser and vector editor can resolve, since the caption ships
// inside downloaded SVGs where a webfont would not follow.
export const FRAME_FONT = 'Arial, Helvetica, sans-serif';

export const FRAME_KEYS = ['none', 'border', 'corners', 'label', 'label-top', 'bubble'];

const CAPTION_FRAMES = ['label', 'label-top', 'bubble'];

export const DEFAULT_CAPTION = 'SCAN ME';

// Fractions of the symbol's own width.
const STROKE = 0.045; // border thickness
const GAP = 0.03; // breathing room between the border and the code
const BAND = 0.2; // caption bar height
const RADIUS = 0.09; // outer corner radius
const FONT = 0.115; // caption size before it is fitted
const CAPTION_INSET = 0.09; // side padding the caption keeps clear, per side
const TAIL_HEIGHT = 0.07;
const TAIL_WIDTH = 0.13;
const BUBBLE_GAP = 0.03;

export function frameHasCaption(key) {
    return CAPTION_FRAMES.includes(key);
}

// The symbol drawn at its natural size, with nothing around it.
function bare(total) {
    return { key: 'none', width: total, height: total, qr: { x: 0, y: 0 }, shapes: [], caption: null };
}

// A hollow rounded rectangle: outer shape with the inner one punched out.
function ring(x, y, w, h, radius, stroke) {
    return {
        d: roundedRectPath(x, y, w, h, radius)
            + roundedRectPath(x + stroke, y + stroke, w - 2 * stroke, h - 2 * stroke, Math.max(0, radius - stroke)),
        fill: 'fg',
        rule: 'evenodd',
    };
}

// One corner bracket, as two rounded bars meeting at (x, y). `dx`/`dy` are the
// directions the arms run in. They overlap at the corner, so the shape is
// filled nonzero to union them rather than punch the overlap back out.
function bracket(x, y, len, thick, dx, dy) {
    const arm = roundedRectPath(dx > 0 ? x : x - len, dy > 0 ? y : y - thick, len, thick, thick / 2);
    const leg = roundedRectPath(dx > 0 ? x : x - thick, dy > 0 ? y : y - len, thick, len, thick / 2);

    return arm + leg;
}

let measureCtx = null;

function textWidth(text, size) {
    if (typeof document === 'undefined') return 0;
    if (!measureCtx) measureCtx = document.createElement('canvas').getContext('2d');
    if (!measureCtx) return 0;

    measureCtx.font = `700 ${size}px ${FRAME_FONT}`;

    return measureCtx.measureText(text).width;
}

// Long captions shrink to fit rather than run off the edge, down to a floor
// where the text is still worth printing.
function caption(text, x, y, width, symbol) {
    const ideal = FONT * symbol;
    const room = width * (1 - 2 * CAPTION_INSET);
    const measured = textWidth(text, ideal);
    const size = measured > room && measured > 0
        ? Math.max(ideal * 0.45, (ideal * room) / measured)
        : ideal;

    return { text, x, y, size, fill: 'bg', weight: 700, font: FRAME_FONT };
}

/**
 * Build the layout for one frame.
 *
 * @param {number} total symbol width in module units, quiet zone included
 * @param {object} options { frame, caption }
 * @returns {{width: number, height: number, qr: {x: number, y: number}, shapes: object[], caption: object|null}}
 */
export function buildFrameLayout(total, { frame = 'none', caption: label = '' } = {}) {
    if (!FRAME_KEYS.includes(frame) || frame === 'none') return bare(total);

    const stroke = STROKE * total;
    const gap = GAP * total;
    const radius = RADIUS * total;
    const band = BAND * total;
    // The light square the code sits in, and the frame width that follows it.
    const plate = total + 2 * gap;
    const width = plate + 2 * stroke;
    const inset = stroke + gap;
    const text = frameHasCaption(frame) ? String(label).trim() : '';

    switch (frame) {
        case 'border':
            return {
                key: frame,
                width,
                height: width,
                qr: { x: inset, y: inset },
                shapes: [ring(0, 0, width, width, radius, stroke)],
                caption: null,
            };

        case 'corners': {
            const len = 0.3 * width;
            const thick = stroke * 1.2;

            return {
                key: frame,
                width,
                height: width,
                qr: { x: inset, y: inset },
                shapes: [{
                    d: bracket(0, 0, len, thick, 1, 1)
                        + bracket(width, 0, len, thick, -1, 1)
                        + bracket(0, width, len, thick, 1, -1)
                        + bracket(width, width, len, thick, -1, -1),
                    fill: 'fg',
                }],
                caption: null,
            };
        }

        // Caption below the code, which is where a reader looks after the code
        // itself. The bar is the bottom of the frame rather than a strip stuck
        // underneath it, so the whole thing reads as one object.
        case 'label': {
            const height = stroke + plate + band;

            return {
                key: frame,
                width,
                height,
                qr: { x: inset, y: inset },
                shapes: [
                    { d: roundedRectPath(0, 0, width, height, radius), fill: 'fg' },
                    { d: roundedRectPath(stroke, stroke, plate, plate, Math.max(0, radius - stroke)), fill: 'bg' },
                ],
                caption: text ? caption(text, width / 2, stroke + plate + band / 2, width, total) : null,
            };
        }

        case 'label-top': {
            const height = band + plate + stroke;

            return {
                key: frame,
                width,
                height,
                qr: { x: inset, y: band + gap },
                shapes: [
                    { d: roundedRectPath(0, 0, width, height, radius), fill: 'fg' },
                    { d: roundedRectPath(stroke, band, plate, plate, Math.max(0, radius - stroke)), fill: 'bg' },
                ],
                caption: text ? caption(text, width / 2, band / 2, width, total) : null,
            };
        }

        // A pill under the code with a tail pointing back at it, for when the
        // caption should read as an aside rather than part of the border.
        case 'bubble': {
            const tail = TAIL_HEIGHT * total;
            const tailWidth = TAIL_WIDTH * total;
            const pillY = width + BUBBLE_GAP * total + tail;

            return {
                key: frame,
                width,
                height: pillY + band,
                qr: { x: inset, y: inset },
                shapes: [
                    ring(0, 0, width, width, radius, stroke),
                    { d: roundedRectPath(0, pillY, width, band, band / 2), fill: 'fg' },
                    {
                        d: `M${width / 2 - tailWidth / 2} ${pillY}L${width / 2} ${pillY - tail}L${width / 2 + tailWidth / 2} ${pillY}Z`,
                        fill: 'fg',
                    },
                ],
                caption: text ? caption(text, width / 2, pillY + band / 2, width, total) : null,
            };
        }

        default:
            return bare(total);
    }
}
