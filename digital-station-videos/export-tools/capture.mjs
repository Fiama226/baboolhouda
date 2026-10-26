/* Capture image par image d'un spot (mode ?capture du lecteur)
   Usage : node capture.mjs <page.html> <dossierSortie> [fps] [--test]
   --test : rend une image au milieu de chaque plan (contrôle visuel) */
import fs from "node:fs";
import path from "node:path";
import { createRequire } from "node:module";
const require = createRequire(import.meta.url);
const chromium = require("@sparticuz/chromium").default;
const puppeteer = require("puppeteer-core");

const [pageName, outDir, fpsArg, testFlag] = process.argv.slice(2);
const FPS = parseInt(fpsArg || "20", 10);
const TEST = testFlag === "--test" || fpsArg === "--test";
const BASE = "http://localhost:8080";

fs.mkdirSync(outDir, { recursive: true });

const execPath = await chromium.executablePath();
const browser = await puppeteer.launch({
  args: [...chromium.args, "--no-sandbox", "--disable-dev-shm-usage", "--disable-gpu",
         "--hide-scrollbars", "--mute-audio", "--force-color-profile=srgb"],
  executablePath: execPath,
  headless: "shell",
  env: { ...process.env, LD_LIBRARY_PATH: "/tmp/al2023libs/lib" },
});
const page = await browser.newPage();
await page.setViewport({ width: 1280, height: 720, deviceScaleFactor: 1 });
page.on("pageerror", (e) => console.error("PAGEERROR:", e.message));

await page.goto(`${BASE}/${pageName}?capture=1`, { waitUntil: "networkidle0", timeout: 60000 });
await page.waitForFunction(() => window.__spot && window.__spot.duration > 0, { timeout: 20000 });

/* Polices : Open Sans (texte) + Noto Color Emoji (emojis) */
await page.addStyleTag({ url: `${BASE}/export-tools/node_modules/@fontsource/noto-color-emoji/index.css` });
await page.addStyleTag({ content: `
  @font-face{font-family:'CaptureSans';src:url('${BASE}/export-tools/fonts/OpenSans-Regular.ttf') format('truetype');font-weight:400}
  @font-face{font-family:'CaptureSans';src:url('${BASE}/export-tools/fonts/OpenSans-Bold.ttf') format('truetype');font-weight:700}
  @font-face{font-family:'CaptureSans';src:url('${BASE}/export-tools/fonts/OpenSans-Italic.ttf') format('truetype');font-style:italic}
  *{font-family:'CaptureSans','Noto Color Emoji',sans-serif !important}
`});
await page.evaluate(async () => {
  const samples = "🔍🍗👑📞💬🏗️📱🔒🕳️💨👍❤️🔖👥📈🌐⭐🏆🪰🤤♥✦✓●➜⟲⛶";
  try { await document.fonts.load('16px "Noto Color Emoji"', samples); } catch (e) {}
  try {
    await document.fonts.load('16px "CaptureSans"');
    await document.fonts.load('700 16px "CaptureSans"');
    await document.fonts.load('900 16px "CaptureSans"');
  } catch (e) {}
  await document.fonts.ready;
  await Promise.all([...document.images].map((i) => i.complete ? 1 : new Promise((r) => { i.onload = i.onerror = r; })));
});

/* Remplacement des emojis par des images SVG Twemoji :
   les glyphes couleur CBDT ne se dessinent pas dans ce Chromium headless,
   les images vectorielles passent toujours. Capture uniquement. */
const EMOJI_CP = ["1f310","1f357","1f37d","1f3c6","1f3d7","1f41f","1f44d","1f451","1f465","1f4a8",
  "1f4ac","1f4c8","1f4de","1f4f1","1f501","1f50a","1f50d","1f512","1f516","1f525","1f573","1f924",
  "1f9b4","1fab0","2b50","2764","26a1"];
const EMOJI_MAP = {};
for (const cp of EMOJI_CP) EMOJI_MAP[String.fromCodePoint(parseInt(cp, 16))] = cp;
await page.evaluate(async (map, base) => {
  const style = document.createElement("style");
  style.textContent = "img.emoji-img{height:1em;width:1em;vertical-align:-.12em;display:inline-block}";
  document.head.appendChild(style);
  const keys = Object.keys(map).sort((a, b) => b.length - a.length);
  const esc = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  const re = new RegExp("(" + keys.map(esc).join("|") + ")\\uFE0F?", "gu");
  const srcOf = (ch) => base + map[ch] + ".svg";
  /* précharge (cache chaud pour <img> et <image> SVG) */
  await Promise.all(keys.map((k) => new Promise((r) => {
    const i = new Image(); i.onload = i.onerror = () => r(1); i.src = srcOf(k);
  })));
  const stage = document.getElementById("stage");
  /* 1) <text> SVG ne contenant qu'un emoji -> <image> SVG */
  const SVGNS = "http://www.w3.org/2000/svg";
  stage.querySelectorAll("text").forEach((t) => {
    const txt = (t.textContent || "").trim();
    const key = keys.find((k) => txt === k || txt === k + "\uFE0F");
    if (!key) return;
    const fsize = parseFloat(t.getAttribute("font-size") || getComputedStyle(t).fontSize) || 30;
    const x = parseFloat(t.getAttribute("x") || 0), y = parseFloat(t.getAttribute("y") || 0);
    const anch = t.getAttribute("text-anchor") || "start";
    const img = document.createElementNS(SVGNS, "image");
    img.setAttribute("href", srcOf(key));
    img.setAttribute("width", fsize); img.setAttribute("height", fsize);
    img.setAttribute("x", anch === "middle" ? x - fsize / 2 : anch === "end" ? x - fsize : x);
    img.setAttribute("y", y - fsize * 0.82);
    t.replaceWith(img);
  });
  /* 2) nœuds texte HTML -> <img> */
  const walker = document.createTreeWalker(stage, NodeFilter.SHOW_TEXT, {
    acceptNode: (n) => (n.parentElement && n.parentElement.namespaceURI === SVGNS)
      ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT
  });
  const nodes = [];
  while (walker.nextNode()) nodes.push(walker.currentNode);
  for (const node of nodes) {
    const text = node.nodeValue;
    if (!text) continue;
    re.lastIndex = 0;
    if (!re.test(text)) continue;
    re.lastIndex = 0;
    const frag = document.createDocumentFragment();
    let last = 0, m;
    while ((m = re.exec(text)) !== null) {
      if (m.index > last) frag.appendChild(document.createTextNode(text.slice(last, m.index)));
      const img = document.createElement("img");
      img.className = "emoji-img"; img.src = srcOf(m[1]); img.alt = m[1];
      frag.appendChild(img);
      last = m.index + m[0].length;
    }
    if (last < text.length) frag.appendChild(document.createTextNode(text.slice(last)));
    node.replaceWith(frag);
  }
  await Promise.all([...stage.querySelectorAll("img.emoji-img")].map((i) =>
    i.complete ? 1 : new Promise((r) => { i.onload = i.onerror = () => r(1); })));
}, EMOJI_MAP, `${BASE}/export-tools/twemoji-svg/`);
console.log("emojis remplacés par des SVG Twemoji (capture)");

const meta = await page.evaluate(() => ({
  duration: window.__spot.duration,
  cues: window.__spot.cues(),
  scenes: window.__spot.scenes(),
}));
console.log(`${pageName} : durée ${meta.duration.toFixed(2)}s · ${meta.cues.length} cues audio · ${meta.scenes.length} plans`);
fs.writeFileSync(path.join(outDir, "cues.json"), JSON.stringify({ ...meta, fps: FPS }, null, 2));

const cdp = await page.createCDPSession();
async function frameAt(t, file, type = "jpeg") {
  await page.evaluate((tt) => new Promise((res) => {
    window.__spot.render(tt);
    requestAnimationFrame(() => res());
  }), t);
  const { data } = await cdp.send("Page.captureScreenshot",
    type === "jpeg" ? { format: "jpeg", quality: 90, fromSurface: true } : { format: "png", fromSurface: true });
  fs.writeFileSync(file, Buffer.from(data, "base64"));
}

if (TEST) {
  for (let i = 0; i < meta.scenes.length; i++) {
    const s = meta.scenes[i];
    const t = s.start + s.dur * 0.55;
    const f = path.join(outDir, `test_${i + 1}.png`);
    await frameAt(t, f, "png");
    console.log(`plan ${i + 1} (${s.title}) @${t.toFixed(2)}s -> ${f}`);
  }
} else {
  const N = Math.ceil(meta.duration * FPS);
  const t0 = Date.now();
  for (let i = 0; i < N; i++) {
    await frameAt(i / FPS, path.join(outDir, `f${String(i).padStart(4, "0")}.jpg`));
    if (i % 100 === 0) console.log(`  ${i}/${N} images (${((Date.now() - t0) / 1000).toFixed(0)}s)`);
  }
  console.log(`OK : ${N} images en ${((Date.now() - t0) / 1000).toFixed(1)}s`);
}
await browser.close();
