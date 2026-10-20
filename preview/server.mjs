import http from 'node:http';
import { readFile } from 'node:fs/promises';
import { existsSync, statSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const port = Number(process.env.PORT || 4173);

const MIME = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'application/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.ico': 'image/x-icon',
};

const server = http.createServer(async (req, res) => {
  try {
    let urlPath = decodeURIComponent(new URL(req.url, `http://${req.headers.host}`).pathname);
    if (urlPath === '/') urlPath = '/index.html';
    if (urlPath.startsWith('/site/')) {
      // Serve the real theme assets so the preview uses the exact production CSS.
      let p = path.join(root, urlPath);
      if (!p.startsWith(root) || !existsSync(p)) { res.writeHead(404); res.end('not found'); return; }
      res.writeHead(200, { 'Content-Type': MIME[path.extname(p)] || 'application/octet-stream' });
      res.end(await readFile(p));
      return;
    }
    let p = path.join(root, 'preview', urlPath);
    if (!p.startsWith(path.join(root, 'preview')) || !existsSync(p) || !statSync(p).isFile()) {
      res.writeHead(404, { 'Content-Type': 'text/plain' });
      res.end('not found');
      return;
    }
    res.writeHead(200, { 'Content-Type': MIME[path.extname(p)] || 'application/octet-stream' });
    res.end(await readFile(p));
  } catch (e) {
    res.writeHead(500, { 'Content-Type': 'text/plain' });
    res.end(String(e));
  }
});

server.listen(port, '0.0.0.0', () => {
  console.log(`Design preview → http://0.0.0.0:${port}/`);
});
