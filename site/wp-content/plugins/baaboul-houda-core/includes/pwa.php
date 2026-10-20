<?php
/**
 * PWA — installable app shell:
 *  - /manifest.json  (Web app manifest)
 *  - /sw.js          (service worker: precache app shell, cache-first assets)
 * Icons: theme assets/dist/icons/*.png (generated at build time, committed).
 */

defined('ABSPATH') || exit;

add_action('init', function () {
    add_rewrite_rule('^manifest\.json$', 'index.php?bh_manifest=1', 'top');
    add_rewrite_rule('^sw\.js$', 'index.php?bh_sw=1', 'top');
});
add_filter('query_vars', function (array $vars): array {
    $vars[] = 'bh_manifest';
    $vars[] = 'bh_sw';
    return $vars;
}, 30);

function bh_icon_base(): string {
    return get_template_directory_uri() . '/assets/icons/';
}

// WordPress would 301 /sw.js and /sitemap.xml to a trailing-slash URL; a
// service worker script must not be behind a redirect.
add_filter('redirect_canonical', function ($redirect) {
    if (get_query_var('bh_sw') || get_query_var('bh_manifest') || get_query_var('bh_sitemap')) {
        return false;
    }
    return $redirect;
});

add_action('template_redirect', function () {
    $s = bh_settings();

    if (get_query_var('bh_manifest')) {
        nocache_headers();
        header('Content-Type: application/manifest+json; charset=utf-8');
        echo wp_json_encode([
            'name'          => 'TV Baaboul Houda',
            'short_name'    => 'Baaboul Houda',
            'description'   => 'TV et radio islamiques — en direct, programmes, Coran, prières.',
            'lang'          => 'fr',
            'dir'           => 'ltr',
            'start_url'     => home_url('/'),
            'scope'         => home_url('/'),
            'display'       => 'standalone',
            'background_color' => '#08130d',
            'theme_color'   => $s['color_accent'] ?: '#0e7a4e',
            'icons'         => [
                ['src' => bh_icon_base() . 'icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => bh_icon_base() . 'icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => bh_icon_base() . 'icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (get_query_var('bh_sw')) {
        nocache_headers();
        header('Content-Type: application/javascript; charset=utf-8');
        $version = BH_VERSION;
        $themeAssets = get_template_directory_uri() . '/assets/dist/';
        echo <<<JS
/* Service worker — TV Baaboul Houda v{$version} */
const CACHE = 'bh-v{$version}';
const APP_SHELL = [];
self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(APP_SHELL)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', (e) => {
  e.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== location.origin) return; // never cache third-parties (players, fonts)
  // Navigations: network first, fall back to cache (works offline once visited).
  if (req.mode === 'navigate') {
    e.respondWith(fetch(req).then((res) => {
      const copy = res.clone();
      caches.open(CACHE).then((c) => c.put(req, copy));
      return res;
    }).catch(() => caches.match(req)));
    return;
  }
  // Static assets: stale-while-revalidate (instant from cache, refreshed in
  // the background so a rebuilt bundle with the same ?ver= is picked up).
  if (/\\.(css|js|png|jpg|jpeg|webp|svg|woff2?|mp3)$/i.test(url.pathname) && !req.headers.has('range')) {
    e.respondWith(caches.match(req).then((hit) => {
      const net = fetch(req).then((res) => {
        if (res && res.ok) { const copy = res.clone(); caches.open(CACHE).then((c) => c.put(req, copy)); }
        return res;
      }).catch(() => hit);
      return hit || net;
    }));
  }
});
JS;
        exit;
    }
});
