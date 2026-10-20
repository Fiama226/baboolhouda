/**
 * build/icons/generate.mjs
 * ------------------------
 * Generates the PWA icons (pure JS rasterizer + PNG encoder — no native
 * dependencies, so it runs anywhere `node` does) and copies the SVG favicon.
 * Output: site/wp-content/themes/baaboul-houda/assets/dist/icons/
 */
import { deflateSync } from 'node:zlib';
import { writeFileSync, mkdirSync, copyFileSync, existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
// NOTE: icons live OUTSIDE vite's outDir (assets/dist) so `vite build`
// (emptyOutDir) never wipes them.
const outDir = path.join(root, 'site/wp-content/themes/baaboul-houda/assets/icons');
mkdirSync(outDir, { recursive: true });

// The official logo (assets/brand/logo-tv-baaboul-houda.jpg) provides the app
// icons: they were rendered from it once and are committed. Never overwrite
// them with the placeholder emblem below (kept only as a fallback for forks
// without a logo file).
if (existsSync(path.join(root, 'site/wp-content/themes/baaboul-houda/assets/brand/logo-tv-baaboul-houda.jpg'))) {
    console.log('icons: official logo present (assets/brand) — nothing to generate');
    process.exit(0);
}

/* ---------------- PNG encoder (RGBA, 8-bit) ---------------- */
const crcTable = (() => {
    const t = new Uint32Array(256);
    for (let n = 0; n < 256; n++) {
        let c = n;
        for (let k = 0; k < 8; k++) c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
        t[n] = c >>> 0;
    }
    return t;
})();
const crc32 = (buf) => {
    let c = 0xffffffff;
    for (let i = 0; i < buf.length; i++) c = crcTable[(c ^ buf[i]) & 0xff] ^ (c >>> 8);
    return (c ^ 0xffffffff) >>> 0;
};
const chunk = (type, data) => {
    const len = Buffer.alloc(4);
    len.writeUInt32BE(data.length);
    const typeBuf = Buffer.from(type, 'ascii');
    const crc = Buffer.alloc(4);
    crc.writeUInt32BE(crc32(Buffer.concat([typeBuf, data])));
    return Buffer.concat([len, typeBuf, data, crc]);
};
function encodePng(size, rgba) {
    const sig = Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]);
    const ihdr = Buffer.alloc(13);
    ihdr.writeUInt32BE(size, 0);
    ihdr.writeUInt32BE(size, 4);
    ihdr[8] = 8; ihdr[9] = 6; // 8-bit RGBA
    const raw = Buffer.alloc(size * (size * 4 + 1));
    for (let y = 0; y < size; y++) {
        raw[y * (size * 4 + 1)] = 0; // filter: none
        rgba.copy(raw, y * (size * 4 + 1) + 1, y * size * 4, (y + 1) * size * 4);
    }
    return Buffer.concat([sig, chunk('IHDR', ihdr), chunk('IDAT', deflateSync(raw, { level: 9 })), chunk('IEND', Buffer.alloc(0))]);
}

/* ---------------- Emblem drawing ---------------- */
const GREEN = [14, 122, 78];
const GOLD = [201, 162, 39];
const WHITE = [255, 255, 255];
const BG = [7, 60, 38];

function hex(x) { return Math.max(0, Math.min(255, Math.round(x))); }
function mix(a, b, t) { return [hex(a[0] + (b[0] - a[0]) * t), hex(a[1] + (b[1] - a[1]) * t), hex(a[2] + (b[2] - a[2]) * t)]; }

/**
 * Draws the Baaboul Houda emblem: rounded green tile, white dome (arc),
 * crescent, gold broadcast bar. `pad` = maskable safe-zone padding ratio.
 */
function draw(size, pad = 0) {
    const rgba = Buffer.alloc(size * size * 4);
    const px = (x, y, [r, g, b]) => {
        const i = (y * size + x) * 4;
        rgba[i] = r; rgba[i + 1] = g; rgba[i + 2] = b; rgba[i + 3] = 255;
    };
    const s = (v) => v * (1 - pad) + (size * pad) / 2; // scale coordinate into safe zone
    const R = size * 0.16 * (1 - pad);

    for (let y = 0; y < size; y++) {
        for (let x = 0; x < size; x++) {
            // Rounded-rect background.
            const cx = Math.min(Math.max(x, R), size - R);
            const cy = Math.min(Math.max(y, R), size - R);
            const d = Math.hypot(x - cx, y - cy);
            if (d > R) { px(x, y, [0, 0, 0, 0]); continue; }
            let col = GREEN;

            // Dome: upper half of circle centered (0.5, 0.62) r 0.30
            const dome = s(0.5) + (0) * 0; // center x
            const dx = x - s(0.5);
            const dy = y - s(0.60);
            const rr = s(0.30) - (size * 0.30) / 2 + size * 0.30 / 2; // = 0.30*size
            if (dy < 0 && Math.hypot(dx, dy) <= rr) {
                // Crescent: subtract offset circle.
                const cx2 = x - (s(0.58));
                const cy2 = y - (s(0.545));
                const crescentR = s(0.24) * 0.82;
                if (Math.hypot(cx2, cy2) >= crescentR * 0.98) col = WHITE;
                else col = GREEN;
            }
            // Minaret finial: small circle atop dome.
            const fx = x - s(0.5), fy = y - s(0.27);
            if (Math.hypot(fx, fy) <= size * 0.035) col = WHITE;
            // Gold bar.
            const bx0 = s(0.24), bx1 = s(0.76), by0 = s(0.72), by1 = s(0.79);
            if (x >= bx0 && x <= bx1 && y >= by0 && y <= by1) {
                const inset = size * 0.02;
                const rx = Math.min(Math.max(x, bx0 + inset), bx1 - inset);
                const ry = Math.min(Math.max(y, by0 + inset), by1 - inset);
                if (Math.hypot(x - rx, y - ry) <= inset) col = GOLD;
            }
            // Soft vertical shading (bottom 15% darker).
            if (y > size * 0.85) col = mix(col, [0, 0, 0], (y - size * 0.85) / (size * 0.15) * 0.18);
            px(x, y, col);
        }
    }
    return rgba;
}

function writeIcon(name, size, pad) {
    writeFileSync(path.join(outDir, name), encodePng(size, draw(size, pad)));
    console.log(`icon ${name} (${size}px)`);
}

writeIcon('icon-192.png', 192, 0);
writeIcon('icon-512.png', 512, 0);
writeIcon('icon-maskable-512.png', 512, 0.15);
writeIcon('apple-touch-icon.png', 180, 0);

// Favicon: SVG (crisp at any size).
const fallbackSvg = path.join(root, 'site/wp-content/themes/baaboul-houda/svg/logo.svg');
if (existsSync(fallbackSvg)) {
    copyFileSync(fallbackSvg, path.join(outDir, 'favicon.svg'));
    console.log('icon favicon.svg (copied)');
}
