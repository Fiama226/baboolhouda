#!/usr/bin/env python3
"""Rendu MP4 des spots Digital Station.
Entrées  : /tmp/renders/v{1,2,3}/f%04d.jpg + cues.json (capture.mjs), audio/*.mp3
Sorties  : exports/<slug>-16x9.mp4, -facebook-1x1.mp4 (1080x1080), -vertical-9x16.mp4 (1080x1920)
Usage    : python3 render.py [v1 v2 v3]   (défaut : les trois)
"""
import json
import os
import subprocess
import sys

import imageio_ffmpeg
from PIL import Image, ImageDraw, ImageFont

FF = imageio_ffmpeg.get_ffmpeg_exe()
HERE = os.path.dirname(os.path.abspath(__file__))          # export-tools/
ROOT = os.path.dirname(HERE)                               # digital-station-videos/
EXPORTS = os.path.join(ROOT, "exports")
os.makedirs(EXPORTS, exist_ok=True)

# --- charte ---
BLUE_DARK = (10, 44, 95)        # #0a2c5f
BLUE_DEEP = (6, 29, 66)         # #061d42
BLUE = (28, 99, 198)            # #1c63c6
SKY = (125, 180, 255)
PALE = (169, 200, 245)
ORANGE = (255, 138, 0)
ORANGE_TXT = (36, 19, 0)
WHITE = (255, 255, 255)
GREEN = (31, 174, 98)

FONT_BOLD = os.path.join(HERE, "fonts", "OpenSans-Bold.ttf")
FONT_REG = os.path.join(HERE, "fonts", "OpenSans-Regular.ttf")
LOGO = os.path.join(ROOT, "assets", "logo.png")
QR = os.path.join(ROOT, "assets", "qr-whatsapp.png")
PHONE = "+226 70 71 93 66"

SPOTS = {
    "v1": dict(slug="spot1-btp-introuvable",
               title="« Le BTP introuvable »",
               hook="La meilleure entreprise…\nque personne ne trouve ?"),
    "v2": dict(slug="spot2-services-digital-station",
               title="Vos services Digital Station",
               hook="Rendons votre entreprise\nVISIBLE !"),
    "v3": dict(slug="spot3-maquis-introuvable",
               title="« Le maquis introuvable »",
               hook="Vos clients ont faim…\nils mangent où ?"),
}


def run(cmd, **kw):
    print("+", " ".join(str(c) for c in cmd)[:220])
    subprocess.run(cmd, check=True, **kw)


def font(path, size):
    return ImageFont.truetype(path, size)


def text_w(draw, s, f):
    return draw.textlength(s, font=f)


def draw_centered(draw, cx, y, s, f, fill):
    draw.text((cx - text_w(draw, s, f) / 2, y), s, font=f, fill=fill)


def gradient_bg(w, h):
    img = Image.new("RGB", (w, h), BLUE_DEEP)
    d = ImageDraw.Draw(img, "RGBA")
    for y in range(h):
        t = y / max(h - 1, 1)
        c = tuple(int(BLUE_DARK[i] + (BLUE_DEEP[i] - BLUE_DARK[i]) * t) for i in range(3))
        d.line([(0, y), (w, y)], fill=c)
    # halo central léger + pixels déco
    glow = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    gd = ImageDraw.Draw(glow)
    gd.ellipse([w * 0.08, h * 0.16, w * 0.92, h * 0.52], fill=(28, 99, 198, 46))
    img = Image.alpha_composite(img.convert("RGBA"), glow)
    d = ImageDraw.Draw(img, "RGBA")
    for (x, y, s, a) in [(0.07, 0.10, 16, 90), (0.88, 0.07, 13, 90), (0.12, 0.86, 12, 70),
                         (0.90, 0.82, 17, 80), (0.80, 0.30, 10, 60), (0.05, 0.45, 10, 55)]:
        d.rectangle([w * x, h * y, w * x + s, h * y + s], fill=(125, 180, 255, a))
    return img


def paste_logo_card(img, cx, cy, card_w):
    """Carte blanche arrondie centrée en (cx, cy), logo dedans. Retourne (y_bas)."""
    logo = Image.open(LOGO).convert("RGBA")
    pad = int(card_w * 0.07)
    lw = card_w - 2 * pad
    lh = int(lw * logo.height / logo.width)
    card_h = lh + 2 * pad
    x0, y0 = int(cx - card_w / 2), int(cy - card_h / 2)
    d = ImageDraw.Draw(img, "RGBA")
    d.rounded_rectangle([x0, y0, x0 + card_w, y0 + card_h], radius=int(card_w * 0.055), fill=WHITE + (255,))
    img.alpha_composite(logo.resize((lw, lh), Image.LANCZOS), (x0 + pad, y0 + pad))
    return y0 + card_h


def paste_qr(img, x0, y0, size):
    d = ImageDraw.Draw(img, "RGBA")
    d.rounded_rectangle([x0, y0, x0 + size, y0 + size], radius=int(size * 0.08), fill=WHITE + (255,))
    pad = int(size * 0.055)
    qr = Image.open(QR).convert("RGBA").resize((size - 2 * pad, size - 2 * pad), Image.NEAREST)
    img.alpha_composite(qr, (x0 + pad, y0 + pad))


def phone_chip(draw, x, y, f):
    """Pastille Orange avec le numéro. (x, y) = coin haut-gauche. Retourne largeur."""
    s = PHONE
    tw = text_w(draw, s, f)
    pad_x, h = int(f.size * 0.62), int(f.size * 1.72)
    w = int(tw + 2 * pad_x)
    draw.rounded_rectangle([x, y, x + w, y + h], radius=h // 2, fill=ORANGE + (255,))
    draw.text((x + pad_x, y + (h - f.size) / 2 - f.size * 0.08), s, font=f, fill=ORANGE_TXT)
    return w


def frame_1x1(spot):
    """1080x1080 : logo en haut, vidéo 1080x608 à y=236, titre + tél + QR en bas."""
    W, H, VY = 1080, 1080, 236
    img = gradient_bg(W, H)
    paste_logo_card(img, W / 2, 116, 400)
    d = ImageDraw.Draw(img, "RGBA")
    # liserés au-dessus et en-dessous de la fenêtre vidéo
    d.rectangle([0, VY - 5, W, VY], fill=ORANGE + (255,))
    d.rectangle([0, VY + 608, W, VY + 613], fill=ORANGE + (255,))
    # bas : titre + téléphone à gauche, QR à droite
    d.text((48, 886), spot["title"], font=font(FONT_BOLD, 45), fill=WHITE)
    phone_chip(d, 48, 956, font(FONT_BOLD, 37))
    paste_qr(img, 868, 866, 152)
    d = ImageDraw.Draw(img, "RGBA")
    draw_centered(d, 868 + 76, 1026, "WhatsApp", font(FONT_BOLD, 25), PALE)
    out = f"/tmp/frame-{spot['slug']}-1x1.png"
    img.convert("RGB").save(out)
    return out, VY


def frame_9x16(spot):
    """1080x1920 : logo + accroche, vidéo à y=600, services + tél + QR en bas."""
    W, H, VY = 1080, 1920, 600
    img = gradient_bg(W, H)
    paste_logo_card(img, W / 2, 218, 480)
    d = ImageDraw.Draw(img, "RGBA")
    hook_f = font(FONT_BOLD, 56)
    lines = spot["hook"].split("\n")
    y = 392
    for ln in lines:
        draw_centered(d, W / 2, y, ln, hook_f, WHITE)
        y += 74
    d.rectangle([0, VY - 5, W, VY], fill=ORANGE + (255,))
    d.rectangle([0, VY + 608, W, VY + 613], fill=ORANGE + (255,))
    # services
    f_svc = font(FONT_BOLD, 41)
    services = [("01", "Création de sites web"), ("02", "Réseaux sociaux"), ("03", "Référencement Google")]
    y = 1278
    for num, label in services:
        total = text_w(d, num + "  ", f_svc) + text_w(d, label, f_svc)
        x = (W - total) / 2
        d.text((x, y), num + "  ", font=f_svc, fill=ORANGE)
        d.text((x + text_w(d, num + "  ", f_svc), y), label, font=f_svc, fill=WHITE)
        y += 66
    # téléphone centré
    f_ph = font(FONT_BOLD, 46)
    w_chip = int(text_w(d, PHONE, f_ph) + 2 * int(f_ph.size * 0.62))
    phone_chip(d, (W - w_chip) / 2, 1508, f_ph)
    # QR centré + légende + signature
    paste_qr(img, (W - 236) // 2, 1620, 236)
    d = ImageDraw.Draw(img, "RGBA")
    draw_centered(d, W / 2, 1866, "Scannez pour discuter sur WhatsApp", font(FONT_REG, 29), PALE)
    out = f"/tmp/frame-{spot['slug']}-9x16.png"
    img.convert("RGB").save(out)
    return out, VY


def mix_audio(frames_dir, meta):
    cues = meta["cues"]
    dur = meta["duration"]
    if not cues:
        out = os.path.join(frames_dir, "mix.wav")
        run([FF, "-y", "-f", "lavfi", "-i", "anullsrc=r=48000:cl=mono", "-t", str(dur), out])
        return out
    inputs, filters, labels = [], [], []
    for i, c in enumerate(cues):
        src = os.path.join(ROOT, c["src"])
        inputs += ["-i", src]
        ms = int(round(c["abs"] * 1000))
        filters.append(f"[{i}:a]aresample=48000,atempo={c['rate']},adelay={ms}[a{i}]")
        labels.append(f"[a{i}]")
    fc = ";".join(filters) + ";" + "".join(labels) + \
        f"amix=inputs={len(cues)}:duration=longest:dropout_transition=0:normalize=0," + \
        f"apad,atrim=0:{dur:.3f},loudnorm=I=-16:TP=-1.5:LRA=11,aresample=48000[mix]"
    out = os.path.join(frames_dir, "mix.wav")
    run([FF, "-y", *inputs, "-filter_complex", fc, "-map", "[mix]", "-ar", "48000", out],
        stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    return out


def encode_master(frames_dir, meta, mix, dest):
    run([FF, "-y", "-framerate", str(meta["fps"]), "-i", os.path.join(frames_dir, "f%04d.jpg"),
         "-i", mix, "-vf", "fps=30,format=yuv420p",
         "-c:v", "libx264", "-profile:v", "high", "-level", "4.1", "-crf", "19", "-preset", "medium",
         "-c:a", "aac", "-b:a", "160k", "-ac", "2", "-ar", "48000",
         "-movflags", "+faststart", "-shortest", dest],
        stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)


def encode_framed(frame_png, y, master, dest):
    run([FF, "-y", "-loop", "1", "-framerate", "30", "-i", frame_png, "-i", master,
         "-filter_complex", f"[1:v]scale=1080:608[vid];[0:v][vid]overlay=0:{y},format=yuv420p[vout]",
         "-map", "[vout]", "-map", "1:a",
         "-c:v", "libx264", "-profile:v", "high", "-level", "4.2", "-crf", "19", "-preset", "medium",
         "-r", "30", "-c:a", "copy", "-shortest", "-movflags", "+faststart", dest],
        stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)


def render(key):
    spot = SPOTS[key]
    frames_dir = f"/tmp/renders/{key}"
    meta = json.load(open(os.path.join(frames_dir, "cues.json")))
    print(f"\n=== {key} · {spot['slug']} · {meta['duration']:.2f}s · {meta['fps']} fps source ===")
    mix = mix_audio(frames_dir, meta)
    master = os.path.join(EXPORTS, f"{spot['slug']}-16x9.mp4")
    encode_master(frames_dir, meta, mix, master)
    print("master OK:", os.path.basename(master))
    f1, y1 = frame_1x1(spot)
    out1 = os.path.join(EXPORTS, f"{spot['slug']}-facebook-1x1.mp4")
    encode_framed(f1, y1, master, out1)
    print("1:1 OK:", os.path.basename(out1))
    f9, y9 = frame_9x16(spot)
    out9 = os.path.join(EXPORTS, f"{spot['slug']}-vertical-9x16.mp4")
    encode_framed(f9, y9, master, out9)
    print("9:16 OK:", os.path.basename(out9))


if __name__ == "__main__":
    keys = sys.argv[1:] or ["v1", "v2", "v3"]
    for k in keys:
        render(k)
    print("\nTerminé. Fichiers dans", EXPORTS)
