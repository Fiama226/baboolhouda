#!/usr/bin/env node
/**
 * YouTube channel → WordPress replays auto-import.
 *
 * Fetches the recent uploads of a YouTube channel and creates matching
 * "episode" posts on the WordPress site (deduplicated by video URL).
 *
 * Usage (requires a YouTube Data API v3 key and a WP application password):
 *   YT_API_KEY=... WP_URL=https://tv.bf WP_USER=admin \
 *   WP_APP_PASSWORD=xxxx WP_CHANNEL_ID=UCxxxxxxxx \
 *   node scripts/yt-import.mjs [--limit=10]
 *
 * Can be scheduled (cron / wp-cron) to keep the replays up to date.
 */
import process from 'node:process';

const limit = Number(process.argv.find((a) => a.startsWith('--limit='))?.split('=')[1] || 10);
const KEY = process.env.YT_API_KEY;
const WP_URL = (process.env.WP_URL || '').replace(/\/$/, '');
const WP_USER = process.env.WP_USER;
const WP_PASS = process.env.WP_APP_PASSWORD;
const CHANNEL = process.env.WP_CHANNEL_ID || process.env.YT_CHANNEL_ID;

const die = (msg) => {
    console.error(`✗ ${msg}`);
    process.exit(1);
};

if (!KEY || !WP_URL || !WP_USER || !WP_PASS) die('Missing env: YT_API_KEY, WP_URL, WP_USER, WP_APP_PASSWORD.');
if (!CHANNEL) die('Missing env: YT_CHANNEL_ID (channel ID "UC...").');

const auth = 'Authorization: Basic ' + Buffer.from(`${WP_USER}:${WP_PASS}`).toString('base64');

const yt = async (path) => {
    const res = await fetch(`https://www.googleapis.com/youtube/v3${path}&key=${KEY}`);
    if (!res.ok) die(`YouTube API ${res.status}: ${(await res.text()).slice(0, 200)}`);
    return res.json();
};
const wp = async (path, opts = {}) => {
    const res = await fetch(`${WP_URL}/wp-json${path}`, {
        ...opts,
        headers: { Authorization: auth, 'Content-Type': 'application/json', ...(opts.headers || {}) },
    });
    if (!res.ok) {
        throw new Error(`WP ${res.status} on ${path}: ${(await res.text()).slice(0, 300)}`);
    }
    return res.status === 204 ? null : res.json();
};

// 1. Find the channel's uploads playlist.
const pl = await yt(`/playlists?part=contentDetails&channelId=${CHANNEL}&maxResults=1`);
const uploads = pl.items?.[0]?.contentDetails?.playlistId;
if (!uploads) die('No uploads playlist found for this channel.');

// 2. Recent uploads (newest first).
const items = await yt(`/playlistItems?part=snippet,contentDetails&playlistId=${uploads}&maxResults=${Math.min(limit, 50)}`);
const ids = (items.items || []).map((i) => i.contentDetails?.videoId).filter(Boolean);
if (!ids.length) {
    console.log('No videos found.');
    process.exit(0);
}

// 3. Video details.
const vids = await yt(`/videos?part=snippet,statistics&id=${ids.join(',')}`);
const videos = (vids.items || []).map((v) => ({
    id: v.id,
    title: v.snippet.title,
    url: `https://www.youtube.com/watch?v=${v.id}`,
    desc: (v.snippet.description || '').slice(0, 2000),
    thumb: v.snippet.thumbnails?.medium?.url || '',
    published: v.snippet.publishedAt || '',
    views: Number(v.statistics?.viewCount || 0),
}));

// 4. Existing episodes (dedup by source URL).
const existing = await wp(`/wp/v2/episode?per_page=100&_fields=id,meta`);
const seen = new Set(existing.map((e) => e.meta?._bh_source_url));

let created = 0;
let skipped = 0;
for (const v of videos) {
    if (seen.has(v.url)) {
        skipped += 1;
        console.log(`= déjà présent : ${v.title}`);
        continue;
    }
    try {
        await wp('/wp/v2/episode', {
            method: 'POST',
            body: JSON.stringify({
                title: v.title,
                content: v.desc,
                status: 'publish',
                meta: {
                    _bh_source_type: 'youtube',
                    _bh_source_url: v.url,
                    _bh_source_id: v.id,
                },
            }),
        });
        created += 1;
        console.log(`+ importé : ${v.title} (${v.views} vues)`);
    } catch (e) {
        console.error(`! échec : ${v.title} — ${e.message}`);
    }
}
console.log(`\nTerminé : ${created} importé(s), ${skipped} déjà présent(s).`);
