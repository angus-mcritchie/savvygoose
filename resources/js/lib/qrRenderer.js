// Themed QR rendering.
//
// The `qrcode` package only draws plain squares, so we take the raw module
// matrix from QRCode.create() and build our own SVG path data. Everything is
// expressed in module units (1 unit = 1 QR module) so the same paths can be
// stamped into an <svg> or filled onto a canvas with Path2D at any scale.
//
// Two shapes are chosen independently: how data modules are drawn, and how the
// function patterns are drawn. The latter covers the three corner finders and
// the alignment patterns, which scanners use to locate and sample the symbol,
// so they get their own vocabulary and are never broken into loose dots.

const FINDER = 7;

// Labels for these live in the Blade view, next to the error-correction
// options they sit beside.
export const MODULE_SHAPE_KEYS = ['square', 'rounded', 'fluid', 'dot'];
export const EYE_SHAPE_KEYS = ['square', 'rounded', 'circle', 'leaf'];

// One-click combinations. The picker highlights whichever preset matches the
// current module/eye pair, so these are shortcuts rather than a third setting.
export const QR_PRESETS = {
    classic: { label: 'Classic', module: 'square', eye: 'square' },
    rounded: { label: 'Rounded', module: 'rounded', eye: 'rounded' },
    fluid: { label: 'Fluid', module: 'fluid', eye: 'rounded' },
    dots: { label: 'Dots', module: 'dot', eye: 'rounded' },
    circles: { label: 'Circles', module: 'dot', eye: 'circle' },
    leaf: { label: 'Leaf', module: 'fluid', eye: 'leaf' },
};

// Trim float noise so path strings stay short and diffable.
function n(value) {
    return String(Math.round(value * 1000) / 1000);
}

function circlePath(cx, cy, r) {
    return `M${n(cx - r)} ${n(cy)}A${n(r)} ${n(r)} 0 1 0 ${n(cx + r)} ${n(cy)}A${n(r)} ${n(r)} 0 1 0 ${n(cx - r)} ${n(cy)}Z`;
}

// radii: a single number or [topLeft, topRight, bottomRight, bottomLeft].
// Exported for qrFrame.js, which builds its borders out of the same shapes.
export function roundedRectPath(x, y, w, h, radii = 0) {
    const list = Array.isArray(radii) ? radii : [radii, radii, radii, radii];
    const cap = Math.min(w, h) / 2;
    const [tl, tr, br, bl] = list.map((r) => Math.max(0, Math.min(r, cap)));

    if (!tl && !tr && !br && !bl) {
        return `M${n(x)} ${n(y)}H${n(x + w)}V${n(y + h)}H${n(x)}Z`;
    }

    const arc = (r, ex, ey) => (r ? `A${n(r)} ${n(r)} 0 0 1 ${n(ex)} ${n(ey)}` : `L${n(ex)} ${n(ey)}`);

    return [
        `M${n(x + tl)} ${n(y)}`,
        `H${n(x + w - tr)}`,
        arc(tr, x + w, y + tr),
        `V${n(y + h - br)}`,
        arc(br, x + w - br, y + h),
        `H${n(x + bl)}`,
        arc(bl, x, y + h - bl),
        `V${n(y + tl)}`,
        arc(tl, x + tl, y),
        'Z',
    ].join('');
}

function isDark(data, count, row, col) {
    if (row < 0 || col < 0 || row >= count || col >= count) return false;
    return !!data[row * count + col];
}

function inFinder(count, row, col) {
    const near = (v) => v < FINDER;
    const far = (v) => v >= count - FINDER;
    return (near(row) && near(col)) || (near(row) && far(col)) || (far(row) && near(col));
}

// True when a module overlaps the reserved logo area at all. Modules are
// dropped whole rather than painted over, so no shape is ever left sliced in
// half by the knockout: a half circle reads as a smaller circle to a decoder.
function inClearArea(clear, row, col) {
    if (!clear) return false;

    return col + 1 > clear.x
        && col < clear.x + clear.w
        && row + 1 > clear.y
        && row < clear.y + clear.h;
}

function modulePath(shape, dark, row, col) {
    switch (shape) {
        case 'dot':
            return circlePath(col + 0.5, row + 0.5, 0.45);

        case 'rounded':
            return roundedRectPath(col, row, 1, 1, 0.32);

        case 'fluid': {
            // Round a corner only where both of its edges are exposed, so runs
            // of modules read as one continuous ribbon and lone modules become
            // circles.
            const up = dark(row - 1, col);
            const down = dark(row + 1, col);
            const left = dark(row, col - 1);
            const right = dark(row, col + 1);
            return roundedRectPath(col, row, 1, 1, [
                !up && !left ? 0.5 : 0,
                !up && !right ? 0.5 : 0,
                !down && !right ? 0.5 : 0,
                !down && !left ? 0.5 : 0,
            ]);
        }

        case 'square':
        default:
            return roundedRectPath(col, row, 1, 1, 0);
    }
}

/**
 * One finder pattern at (x, y): a 7x7 ring one module thick, with a 3x3 pip
 * centred in it. The ring is the outer shape plus an inner shape punched out
 * with fill-rule="evenodd".
 *
 * Corner radii stay modest on purpose. A decoder locates the symbol by looking
 * for the 1:1:3:1:1 run of dark and light along a scan line through a finder,
 * and rounding a corner shortens that run on every row it touches.
 */
function eyePaths(shape, x, y) {
    switch (shape) {
        // Reads as a circle, but is a heavily rounded square rather than a true
        // one. A circular ring only holds the 1:1:3:1:1 ratio along the single
        // scan line through its centre, and decoders that sample several rows
        // reject it; measured against zbar, a true circle failed roughly one
        // symbol in seven while this shape passed every case.
        case 'circle':
            return {
                frame: roundedRectPath(x, y, 7, 7, 2.6) + roundedRectPath(x + 1, y + 1, 5, 5, 1.9),
                ball: roundedRectPath(x + 2, y + 2, 3, 3, 1.4),
            };

        case 'rounded':
            return {
                frame: roundedRectPath(x, y, 7, 7, 2) + roundedRectPath(x + 1, y + 1, 5, 5, 1.35),
                ball: roundedRectPath(x + 2, y + 2, 3, 3, 0.9),
            };

        // Two opposite corners swept, two left square.
        case 'leaf':
            return {
                frame: roundedRectPath(x, y, 7, 7, [0, 2, 0, 2]) + roundedRectPath(x + 1, y + 1, 5, 5, [0, 1.4, 0, 1.4]),
                ball: roundedRectPath(x + 2, y + 2, 3, 3, [0, 0.9, 0, 0.9]),
            };

        case 'square':
        default:
            return {
                frame: roundedRectPath(x, y, 7, 7, 0) + roundedRectPath(x + 1, y + 1, 5, 5, 0),
                ball: roundedRectPath(x + 2, y + 2, 3, 3, 0),
            };
    }
}

/**
 * Turn a QR module matrix into themed path data.
 *
 * @param {Uint8Array|number[]} data row-major module matrix, truthy = dark
 * @param {number} count modules per side
 * @param {object} options { module, eye, margin, clear }
 *   clear: {x, y, w, h} in module units relative to the symbol, whose data
 *   modules are left out entirely to make room for a centre logo.
 */
export function buildQrGeometry(data, count, { module = 'square', eye = 'square', margin = 4, clear = null } = {}) {
    const moduleShape = MODULE_SHAPE_KEYS.includes(module) ? module : 'square';
    const eyeShape = EYE_SHAPE_KEYS.includes(eye) ? eye : 'square';
    const quiet = Math.max(0, Math.round(margin) || 0);

    // Neighbour lookups for the fluid shape have to see the cleared area as
    // empty too, or modules along its edge round as though still joined.
    const dark = (row, col) => isDark(data, count, row, col) && !inClearArea(clear, row, col);

    // Alignment patterns are left to the module shape along with everything
    // else. Drawing them as solid bullseyes was tried, on the theory that
    // decoders sample the grid from them: across 512 combinations of shape,
    // symbol version, error-correction level and raster scale, both approaches
    // decoded identically, and a solid bullseye reads as a stray fourth corner.
    const modules = [];
    for (let row = 0; row < count; row++) {
        for (let col = 0; col < count; col++) {
            if (!dark(row, col)) continue;
            if (inFinder(count, row, col)) continue;
            modules.push(modulePath(moduleShape, dark, row, col));
        }
    }

    const frames = [];
    const balls = [];

    for (const [x, y] of [[0, 0], [count - FINDER, 0], [0, count - FINDER]]) {
        const parts = eyePaths(eyeShape, x, y);
        frames.push(parts.frame);
        balls.push(parts.ball);
    }

    return {
        module: moduleShape,
        eye: eyeShape,
        crisp: moduleShape === 'square' && eyeShape === 'square',
        count,
        margin: quiet,
        total: count + quiet * 2,
        paths: {
            modules: modules.join(''),
            eyeFrames: frames.join(''),
            eyeBalls: balls.join(''),
        },
    };
}

// A frame layout (see qrFrame.js) says how big the finished image is and where
// the symbol sits inside it. Unframed output is the same thing with the symbol
// filling the whole image, so both painters below only ever deal with a layout.
function layoutOr(geo, frame) {
    return frame ?? { width: geo.total, height: geo.total, qr: { x: 0, y: 0 }, shapes: [], caption: null };
}

// Frame parts name a role rather than a colour, so a frame follows whatever the
// code itself is drawn in.
function paintFor(role, fg, bg) {
    return role === 'bg' ? bg : fg;
}

function escapeXml(value) {
    return String(value).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
}

/**
 * Serialise geometry to an SVG string.
 *
 * `bg` may be null for a transparent background, and `fg` accepts any CSS
 * colour (including `currentColor`, which the theme thumbnails rely on).
 * `overlay` is raw SVG markup placed in the symbol's own coordinates, and
 * `frame` is an optional layout to wrap it all in.
 */
export function qrToSvg(geo, { width, fg = '#000000', bg = '#ffffff', overlay = '', frame = null } = {}) {
    const { margin, paths, crisp } = geo;
    const layout = layoutOr(geo, frame);
    const sized = width ? ` width="${n(width)}" height="${n((width * layout.height) / layout.width)}"` : '';
    // Scoped to the symbol: a frame's curves want the same antialiasing as
    // any other shape, even when the modules themselves are hard squares.
    const rendering = crisp ? ' shape-rendering="crispEdges"' : '';
    const backdrop = bg ? `<rect width="${n(layout.width)}" height="${n(layout.height)}" fill="${bg}"/>` : '';

    const shapes = layout.shapes
        .map((shape) => {
            const paint = paintFor(shape.fill, fg, bg);
            if (!paint) return '';
            return `<path d="${shape.d}" fill="${paint}" fill-rule="${shape.rule || 'nonzero'}"/>`;
        })
        .join('');

    const cap = layout.caption;
    const capPaint = cap ? paintFor(cap.fill, fg, bg) : null;
    const caption = cap && capPaint
        ? `<text x="${n(cap.x)}" y="${n(cap.y)}" fill="${capPaint}" font-family="${cap.font}" font-size="${n(cap.size)}" font-weight="${cap.weight}" text-anchor="middle" dominant-baseline="central">${escapeXml(cap.text)}</text>`
        : '';

    return [
        `<svg xmlns="http://www.w3.org/2000/svg"${sized} viewBox="0 0 ${n(layout.width)} ${n(layout.height)}">`,
        backdrop,
        shapes,
        caption,
        `<g transform="translate(${n(layout.qr.x + margin)} ${n(layout.qr.y + margin)})" fill="${fg}" fill-rule="evenodd"${rendering}>`,
        `<path d="${paths.modules}"/>`,
        `<path d="${paths.eyeFrames}"/>`,
        `<path d="${paths.eyeBalls}"/>`,
        '</g>',
        overlay ? `<g transform="translate(${n(layout.qr.x)} ${n(layout.qr.y)})">${overlay}</g>` : '',
        '</svg>',
    ].join('');
}

/**
 * Fill geometry onto a canvas `width` device pixels wide. The height follows
 * from the frame layout, so an unframed code stays square.
 */
export function qrToCanvas(canvas, geo, { width, fg = '#000000', bg = '#ffffff', frame = null } = {}) {
    const layout = layoutOr(geo, frame);
    const px = Math.max(1, Math.round(width));
    canvas.width = px;
    canvas.height = Math.max(1, Math.round((px * layout.height) / layout.width));

    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (bg) {
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    const scale = px / layout.width;

    ctx.save();
    ctx.scale(scale, scale);
    for (const shape of layout.shapes) {
        const paint = paintFor(shape.fill, fg, bg);
        if (!paint) continue;
        ctx.fillStyle = paint;
        ctx.fill(new Path2D(shape.d), shape.rule || 'nonzero');
    }
    const cap = layout.caption;
    const capPaint = cap ? paintFor(cap.fill, fg, bg) : null;
    if (cap && capPaint) {
        ctx.fillStyle = capPaint;
        ctx.font = `${cap.weight} ${n(cap.size)}px ${cap.font}`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(cap.text, cap.x, cap.y);
    }
    ctx.restore();

    ctx.save();
    ctx.translate((layout.qr.x + geo.margin) * scale, (layout.qr.y + geo.margin) * scale);
    ctx.scale(scale, scale);
    ctx.fillStyle = fg;
    for (const d of [geo.paths.modules, geo.paths.eyeFrames, geo.paths.eyeBalls]) {
        if (d) ctx.fill(new Path2D(d), 'evenodd');
    }
    ctx.restore();

    return canvas;
}

/**
 * The block of modules a centre logo needs, snapped outward to whole modules.
 *
 * `box` is the logo's own footprint in module units relative to the symbol.
 * Snapping to the grid is what keeps the knockout free of half-drawn modules,
 * and it is why this is computed here rather than at paint time.
 */
export function clearAreaFor(box, pad = 0) {
    const x = Math.floor(box.x - pad);
    const y = Math.floor(box.y - pad);

    return {
        x,
        y,
        w: Math.ceil(box.x + box.w + pad) - x,
        h: Math.ceil(box.y + box.h + pad) - y,
    };
}
