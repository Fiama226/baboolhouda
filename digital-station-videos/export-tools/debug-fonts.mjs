import { createRequire } from "node:module";
const require = createRequire(import.meta.url);
const chromium = require("@sparticuz/chromium").default;
const puppeteer = require("puppeteer-core");
const BASE = "http://localhost:8080";

const execPath = await chromium.executablePath();
const browser = await puppeteer.launch({
  args: [...chromium.args, "--no-sandbox", "--disable-dev-shm-usage", "--disable-gpu", "--hide-scrollbars"],
  executablePath: execPath, headless: "shell",
  env: { ...process.env, LD_LIBRARY_PATH: "/tmp/al2023libs/lib" },
});
const page = await browser.newPage();
await page.setViewport({ width: 1280, height: 720 });
page.on("console", (m) => console.log("[console]", m.type(), m.text().slice(0, 200)));
page.on("pageerror", (e) => console.log("[pageerror]", e.message.slice(0, 200)));
page.on("requestfailed", (r) => console.log("[REQFAIL]", r.url().slice(-80), r.failure()?.errorText));
page.on("response", (r) => { if (/woff|ttf|font/i.test(r.url())) console.log("[font resp]", r.status(), r.url().split("/").slice(-2).join("/")); });

await page.goto(`${BASE}/video1.html?capture=1`, { waitUntil: "networkidle0", timeout: 60000 });
await page.addStyleTag({ url: `${BASE}/export-tools/node_modules/@fontsource/noto-color-emoji/index.css` });
await page.addStyleTag({ content: `
  @font-face{font-family:'CaptureSans';src:url('${BASE}/export-tools/fonts/OpenSans-Regular.ttf') format('truetype');font-weight:400}
  @font-face{font-family:'CaptureSans';src:url('${BASE}/export-tools/fonts/OpenSans-Bold.ttf') format('truetype');font-weight:700}
  *{font-family:'CaptureSans','Noto Color Emoji',sans-serif !important}
`});
const info = await page.evaluate(async () => {
  const before = document.fonts.check('16px "Noto Color Emoji"', "🔍");
  let loadRes = "non testé";
  try {
    const f = await document.fonts.load('16px "Noto Color Emoji"', "🔍🍗📞🌐");
    loadRes = "chargées: " + f.map((x) => x.status).join(",");
  } catch (e) { loadRes = "ERREUR " + e.message; }
  const after = document.fonts.check('16px "Noto Color Emoji"', "🔍");
  const all = [...document.fonts];
  return {
    before, after, loadRes,
    total: all.length,
    loaded: all.filter((f) => f.status === "loaded").length,
    error: all.filter((f) => f.status === "error").length,
    sample: all.slice(0, 5).map((f) => f.family + ":" + f.status),
  };
});
console.log(JSON.stringify(info, null, 2));

// affiche le plan 6 et photographie
await page.evaluate(() => new Promise((res) => {
  const sc = window.__spot.scenes();
  window.__spot.render(sc[5].start + sc[5].dur * 0.8);
  requestAnimationFrame(() => res());
}));
await page.screenshot({ path: "/tmp/test1/fonttest.png" });
console.log("screenshot -> /tmp/test1/fonttest.png");
await browser.close();
