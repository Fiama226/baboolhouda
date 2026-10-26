/* ============================================================
   DIGITAL STATION — Moteur de lecture des spots animés
   - Timeline calculée depuis la durée réelle des voix
   - Scènes, bulles, sous-titres, machine à écrire, compteurs
   - Lecture / pause / redémarrage / plein écran / chapitres
   ============================================================ */
(function () {
  "use strict";

  function fmt(s) {
    s = Math.max(0, Math.round(s));
    return Math.floor(s / 60) + ":" + String(s % 60).padStart(2, "0");
  }

  window.SpotPlayer = function (config) {
    const stage = document.getElementById("stage");
    const subsEl = document.getElementById("subs");
    const bigplay = document.getElementById("bigplay");
    const btnPlay = document.getElementById("btnPlay");
    const btnRestart = document.getElementById("btnRestart");
    const btnMute = document.getElementById("btnMute");
    const btnFs = document.getElementById("btnFs");
    const prog = document.getElementById("progress");
    const fill = document.getElementById("pfill");
    const head = document.getElementById("phead");
    const ticks = document.getElementById("pticks");
    const timeEl = document.getElementById("time");
    const chapEl = document.getElementById("chapter");

    const PAD = 0.55; // respiration après une réplique
    let scenes = config.scenes.map((s) => ({ ...s, el: document.getElementById(s.id) }));
    let audios = {}; // src -> {el, dur}
    let cues = []; // {abs, el, rate, fired}
    let cum = []; // débuts absolus des scènes
    let durs = [];
    let total = 0;

    let playing = false;
    let started = false;
    let ended = false;
    let t = 0;
    let lastTs = null;
    let curScene = -1;
    let muted = false;
    let lastSubKey = "";
    let liveAudios = new Set();

    /* ---------- Préchargement audio ---------- */
    scenes.forEach((s) =>
      (s.audio || []).forEach((a) => {
        if (!audios[a.src]) {
          const el = new Audio();
          el.preload = "auto";
          el.src = a.src;
          audios[a.src] = { el, dur: a.est || 2.5 };
          el.addEventListener("loadedmetadata", () => {
            if (isFinite(el.duration) && el.duration > 0) {
              audios[a.src].dur = el.duration;
              if (!started) buildTimeline();
            }
          });
          el.addEventListener("ended", () => liveAudios.delete(el));
        }
      })
    );

    /* ---------- Construction de la timeline ---------- */
    function buildTimeline() {
      durs = scenes.map((s) => s.min || 5);
      // contraintes : chaque audio doit tenir avant la fin de sa scène (ou scène spanTo)
      for (let iter = 0; iter < 4; iter++) {
        cum = [];
        let acc = 0;
        durs.forEach((d) => { cum.push(acc); acc += d; });
        scenes.forEach((s, i) => {
          (s.audio || []).forEach((a) => {
            const need = (a.at || 0) + audios[a.src].dur / (a.rate || 1) + PAD;
            const target = a.spanTo != null ? a.spanTo : i;
            const curEnd = cum[target] + durs[target] - cum[i]; // exprimé depuis début scène i
            if (need > curEnd) durs[target] += need - curEnd;
          });
        });
      }
      cum = [];
      let acc = 0;
      durs.forEach((d) => { cum.push(acc); acc += d; });
      total = acc;

      cues = [];
      scenes.forEach((s, i) =>
        (s.audio || []).forEach((a) => {
          cues.push({ abs: cum[i] + (a.at || 0), src: a.src, rate: a.rate || 1, fired: false });
        })
      );
      cues.sort((x, y) => x.abs - y.abs);

      // ticks chapitres
      ticks.innerHTML = "";
      cum.forEach((c, i) => {
        if (i === 0) return;
        const d = document.createElement("div");
        d.className = "tick";
        d.style.left = (c / total) * 100 + "%";
        ticks.appendChild(d);
      });
      timeEl.textContent = fmt(t) + " / " + fmt(total);
    }

    /* ---------- Scènes ---------- */
    function activateScene(idx, force) {
      if (idx === curScene && !force) return;
      scenes.forEach((s) => s.el && s.el.classList.remove("active"));
      const el = scenes[idx] && scenes[idx].el;
      if (el) {
        void el.offsetWidth; // relance les animations CSS
        el.classList.add("active");
        el.querySelectorAll("[data-from].on").forEach((n) => n.classList.remove("on"));
      }
      curScene = idx;
      if (chapEl && scenes[idx]) chapEl.textContent = scenes[idx].title || "";
      lastSubKey = "";
    }

    function sceneAt(time) {
      let idx = 0;
      for (let i = 0; i < cum.length; i++) if (time >= cum[i]) idx = i;
      return idx;
    }

    /* ---------- Éléments pilotés par le temps local ---------- */
    function updateSceneFx(idx, lt) {
      const root = scenes[idx].el;
      if (!root) return;
      root.querySelectorAll("[data-from]").forEach((n) => {
        const from = parseFloat(n.dataset.from || "0");
        const to = n.dataset.to ? parseFloat(n.dataset.to) : Infinity;
        const on = lt >= from && lt < to;
        if (on !== n.classList.contains("on")) n.classList.toggle("on", on);
      });
      root.querySelectorAll("[data-type]").forEach((n) => {
        const full = n.dataset.type || "";
        const from = parseFloat(n.dataset.typeFrom || "0");
        const cps = parseFloat(n.dataset.typeCps || "11");
        const nCh = Math.max(0, Math.min(full.length, Math.floor((lt - from) * cps)));
        const txt = full.slice(0, nCh);
        if (n._txt !== txt) { n._txt = txt; n.textContent = txt; }
      });
      root.querySelectorAll("[data-count]").forEach((n) => {
        const target = parseFloat(n.dataset.count || "0");
        const from = parseFloat(n.dataset.countFrom || "0");
        const dur = parseFloat(n.dataset.countDur || "2");
        let p = Math.max(0, Math.min(1, (lt - from) / dur));
        p = 1 - Math.pow(1 - p, 3);
        const v = Math.round(target * p);
        const txt = v.toLocaleString("fr-FR");
        if (n._txt !== txt) { n._txt = txt; n.textContent = txt; }
      });
    }

    function updateSubs(idx, lt) {
      const subs = scenes[idx].subs || [];
      const cur = subs.find((s) => lt >= s.from && lt < s.to);
      const key = cur ? idx + "|" + cur.text : "";
      if (key === lastSubKey) return;
      lastSubKey = key;
      if (!cur) { subsEl.classList.remove("show"); return; }
      subsEl.innerHTML =
        (cur.who ? '<span class="who ' + (cur.cls || "") + '">' + cur.who + "</span>" : "") +
        cur.text;
      subsEl.classList.add("show");
    }

    /* ---------- Audio ---------- */
    function fireCues() {
      cues.forEach((c) => {
        if (!c.fired && t >= c.abs && t - c.abs < 0.6) {
          c.fired = true;
          const a = audios[c.src];
          if (a) {
            try {
              a.el.playbackRate = c.rate;
              a.el.muted = muted;
              a.el.currentTime = 0;
              const p = a.el.play();
              if (p) p.catch(() => {});
              liveAudios.add(a.el);
            } catch (e) {}
          }
        } else if (!c.fired && t - c.abs >= 0.6) {
          c.fired = true; // trop tard (saut), on ne joue pas en retard
        }
      });
    }
    function stopAllAudio() {
      Object.values(audios).forEach((a) => { try { a.el.pause(); a.el.currentTime = 0; } catch (e) {} });
      liveAudios.clear();
    }

    /* ---------- Boucle ---------- */
    function loop(ts) {
      requestAnimationFrame(loop);
      if (lastTs == null) lastTs = ts;
      const dt = Math.min(0.05, (ts - lastTs) / 1000);
      lastTs = ts;
      if (!playing) return;

      t += dt;
      if (t >= total) {
        t = total;
        finish();
      }
      const idx = sceneAt(t);
      activateScene(idx, false);
      const lt = t - cum[idx];
      fireCues();
      updateSceneFx(idx, lt);
      updateSubs(idx, lt);
      const ratio = total ? (t / total) * 100 : 0;
      fill.style.width = ratio + "%";
      head.style.left = ratio + "%";
      timeEl.textContent = fmt(t) + " / " + fmt(total);
    }

    /* ---------- Transport ---------- */
    function play() {
      if (ended) seek(0);
      started = true;
      ended = false;
      playing = true;
      stage.classList.remove("paused");
      bigplay.classList.add("hide");
      btnPlay.textContent = "❚❚";
      liveAudios.forEach((el) => { const p = el.play(); if (p) p.catch(() => {}); });
    }
    function pause() {
      playing = false;
      stage.classList.add("paused");
      btnPlay.textContent = "▶";
      Object.values(audios).forEach((a) => { if (!a.el.paused) a.el.pause(); });
    }
    function finish() {
      playing = false;
      ended = true;
      stage.classList.remove("paused");
      btnPlay.textContent = "▶";
      bigplay.querySelector("h2").textContent = "Revoir le spot";
      bigplay.querySelector("p").textContent = "Cliquez pour relancer la vidéo";
      bigplay.classList.remove("hide");
    }
    function seek(nt) {
      stopAllAudio();
      t = Math.max(0, Math.min(total - 0.05, nt));
      cues.forEach((c) => (c.fired = c.abs < t - 0.02));
      const idx = sceneAt(t);
      curScene = -1;
      activateScene(idx, true);
      updateSceneFx(idx, t - cum[idx]);
      updateSubs(idx, t - cum[idx]);
      ended = false;
    }

    /* ---------- Mode capture (export MP4 image par image) ---------- */
    const CAPTURE = new URLSearchParams(location.search).has("capture");
    if (CAPTURE) document.body.classList.add("capture");
    function scrubAnimations(idx, lt) {
      const root = scenes[idx].el;
      if (!root || !root.getAnimations) return;
      void root.offsetWidth; // force le calcul des styles/animations
      root.getAnimations({ subtree: true }).forEach((a) => {
        try {
          a.pause();
          let base = 0;
          const name = a.animationName || "";
          if (name === "popIn" || name === "bubbleIn" || name === "ringOut") {
            const tgt = a.effect && a.effect.target;
            if (tgt && tgt.dataset && tgt.dataset.from) base = parseFloat(tgt.dataset.from);
            else if (tgt && tgt.closest) {
              const h = tgt.closest("[data-from]");
              if (h) base = parseFloat(h.dataset.from);
            }
          }
          a.currentTime = Math.max(0, (lt - base) * 1000);
        } catch (e) {}
      });
    }
    window.__spot = {
      get duration() { return total; },
      cues() { return cues.map((c) => ({ src: c.src, abs: c.abs, rate: c.rate })); },
      scenes() { return scenes.map((s, i) => ({ title: s.title, start: cum[i], dur: durs[i] })); },
      render(tt) {
        t = tt;
        const idx = sceneAt(t);
        activateScene(idx, false);
        const lt = t - cum[idx];
        updateSceneFx(idx, lt);
        updateSubs(idx, lt);
        if (CAPTURE) scrubAnimations(idx, lt);
      }
    };

    /* ---------- Événements ---------- */
    bigplay.addEventListener("click", play);
    btnPlay.addEventListener("click", () => (playing ? pause() : play()));
    btnRestart.addEventListener("click", () => { seek(0); play(); });
    btnMute.addEventListener("click", () => {
      muted = !muted;
      btnMute.textContent = muted ? "🔇" : "🔊";
      Object.values(audios).forEach((a) => (a.el.muted = muted));
    });
    btnFs.addEventListener("click", () => {
      const wrap = stage;
      if (document.fullscreenElement) document.exitFullscreen();
      else if (wrap.requestFullscreen) wrap.requestFullscreen();
    });
    prog.addEventListener("click", (e) => {
      const r = prog.querySelector(".track").getBoundingClientRect();
      const ratio = Math.max(0, Math.min(1, (e.clientX - r.left) / r.width));
      const target = ratio * total;
      seek(cum[sceneAt(target)] + 0.01); // accroche au début du plan
      if (!playing && started) play();
    });
    document.addEventListener("keydown", (e) => {
      if (e.code === "Space" && !/INPUT|TEXTAREA/.test(e.target.tagName)) {
        e.preventDefault();
        playing ? pause() : play();
      }
      if (e.code === "ArrowRight") seek(cum[Math.min(scenes.length - 1, curScene + 1)] + 0.01);
      if (e.code === "ArrowLeft") seek(cum[Math.max(0, curScene - 1)] + 0.01);
    });

    buildTimeline();
    activateScene(0, true);
    requestAnimationFrame(loop);
  };
})();
