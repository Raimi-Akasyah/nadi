(function () {
  'use strict';

  // ---- Slide viewer ----
  var viewer = document.getElementById('viewer');
  if (viewer) {
    var slides = viewer.querySelectorAll('[data-slide]');
    var count = parseInt(viewer.getAttribute('data-count'), 10) || slides.length;
    var current = parseInt(viewer.getAttribute('data-current'), 10) || 1;
    var prevBtn = document.getElementById('prev-btn');
    var nextBtn = document.getElementById('next-btn');
    var counter = document.getElementById('slide-counter');
    var completeBtn = document.getElementById('complete-btn');

    var slideUrl = function (n) {
      var url = new URL(window.location.href);
      url.searchParams.set('slide', String(n));
      return url.pathname + url.search;
    };

    var setLink = function (el, enabled, n) {
      if (!el) return;
      if (enabled) {
        el.classList.remove('is-disabled');
        el.removeAttribute('aria-disabled');
        el.setAttribute('href', slideUrl(n));
      } else {
        el.classList.add('is-disabled');
        el.setAttribute('aria-disabled', 'true');
        el.removeAttribute('href');
      }
    };

    var show = function (n) {
      if (n < 1 || n > count) return;
      current = n;
      for (var i = 0; i < slides.length; i++) {
        slides[i].hidden = parseInt(slides[i].getAttribute('data-slide'), 10) !== n;
      }
      if (counter) counter.textContent = n + '/' + count;
      setLink(prevBtn, n > 1, n - 1);
      setLink(nextBtn, n < count, n + 1);
      if (completeBtn) completeBtn.disabled = n !== count;
      viewer.setAttribute('data-current', String(n));
      viewer.style.setProperty('--slide-progress', (n * 100 / count) + '%');
      if (window.history && window.history.replaceState) {
        window.history.replaceState(null, '', slideUrl(n));
      }
    };

    if (prevBtn) {
      prevBtn.addEventListener('click', function (ev) {
        ev.preventDefault();
        show(current - 1);
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', function (ev) {
        ev.preventDefault();
        show(current + 1);
      });
    }

    document.addEventListener('keydown', function (ev) {
      var tag = (ev.target && ev.target.tagName) || '';
      if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
      if (ev.key === 'ArrowLeft') {
        show(current - 1);
      } else if (ev.key === 'ArrowRight') {
        show(current + 1);
      }
    });

    // ---- Fullscreen ----
    var fsBtn = document.getElementById('fullscreen-btn');
    if (fsBtn) {
      fsBtn.addEventListener('click', function () {
        var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
        if (fsEl) {
          (document.exitFullscreen || document.webkitExitFullscreen).call(document);
        } else if (viewer.requestFullscreen) {
          viewer.requestFullscreen();
        } else if (viewer.webkitRequestFullscreen) {
          viewer.webkitRequestFullscreen();
        }
      });
    }
  }

  // ---- Keep-alive ----
  // Slide changes and quiz answers make no page request, so a long read (Modul 4 takes about 45 minutes)
  // would hit the 30-minute idle timeout. While the learner is active, tell the server at most every
  // KEEPALIVE_MS. A learner who stops interacting is still signed out after the timeout.
  var csrfInput = document.querySelector('input[name="csrf"]');
  var KEEPALIVE_MS = 4 * 60 * 1000;
  var lastPing = Date.now(); // the page load itself counted as activity
  var pinging = false;
  var keepAlive = function () {
    if (!csrfInput || pinging || Date.now() - lastPing < KEEPALIVE_MS || !window.fetch) return;
    pinging = true;
    var body = new URLSearchParams();
    body.set('csrf', csrfInput.value);
    fetch('keepalive.php', { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function () { lastPing = Date.now(); })
      .catch(function () { /* offline: try again on the next activity */ })
      .then(function () { pinging = false; });
  };
  ['click', 'keydown', 'pointerdown', 'touchstart', 'scroll', 'wheel', 'change'].forEach(function (type) {
    document.addEventListener(type, keepAlive, { passive: true, capture: true });
  });

  // ---- Video lesson (Self-Learning) ----
  // Same player as videoLesson() in docs/assets/js/app.js: keep both in sync.
  var videoRoot = document.getElementById('video-lesson');
  if (videoRoot) {
    videoLesson({
      root: videoRoot,
      youtube: videoRoot.getAttribute('data-youtube'),
      duration: parseInt(videoRoot.getAttribute('data-duration'), 10) || 0,
      done: videoRoot.getAttribute('data-done') === '1',
      progress: sessionProgress(videoRoot.getAttribute('data-progress-key')),
      // Watching makes no clicks, so a long video would otherwise hit the idle timeout.
      onActivity: keepAlive
    });
  }

  // ---- Drawer ----
  // Below 960px the sidebar is an off-canvas drawer (CSS, only under html.js); this opens and closes it.
  var drawerToggle = document.getElementById('sidebar-toggle');
  var drawer = document.getElementById('course-sidebar');
  if (drawerToggle && drawer) {
    var backdrop = document.querySelector('.sidebar-backdrop');
    var drawerSearch = document.getElementById('lesson-search');
    var isOpen = function () { return document.body.classList.contains('sidebar-open'); };
    var setDrawer = function (open) {
      if (open === isOpen()) return;
      document.body.classList.toggle('sidebar-open', open);
      drawerToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) {
        if (drawerSearch) drawerSearch.focus();
      } else {
        drawerToggle.focus();
      }
    };
    drawerToggle.addEventListener('click', function () { setDrawer(!isOpen()); });
    if (backdrop) backdrop.addEventListener('click', function () { setDrawer(false); });
    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && isOpen()) setDrawer(false);
    });
    drawer.addEventListener('click', function (ev) {
      var link = ev.target && ev.target.closest ? ev.target.closest('.lesson-row a') : null;
      if (link && isOpen()) {
        document.body.classList.remove('sidebar-open');
        drawerToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // ---- Sidebar search ----
  var search = document.getElementById('lesson-search');
  if (search) {
    var sections = document.querySelectorAll('.sidebar-section');
    var initialOpen = [];
    for (var s = 0; s < sections.length; s++) initialOpen.push(sections[s].open);

    search.addEventListener('input', function () {
      var q = search.value.trim().toLowerCase();
      for (var i = 0; i < sections.length; i++) {
        var sec = sections[i];
        var rows = sec.querySelectorAll('.lesson-row');
        var titleEl = sec.querySelector('.section-title');
        var sectionMatch = q !== '' && titleEl && titleEl.textContent.toLowerCase().indexOf(q) !== -1;
        var any = false;
        for (var j = 0; j < rows.length; j++) {
          var title = (rows[j].getAttribute('data-title') || '').toLowerCase();
          var match = q === '' || sectionMatch || title.indexOf(q) !== -1;
          rows[j].hidden = !match;
          if (match) any = true;
        }
        sec.hidden = !any;
        sec.open = q === '' ? initialOpen[i] : any;
      }
    });
  }

  // Furthest point watched, in sessionStorage under a key that changes per learner and sign-in (src/helpers.php).
  function sessionProgress(key) {
    var store = null;
    try { store = window.sessionStorage; } catch (e) { store = null; }
    return {
      get: function () {
        try { return store && key ? parseFloat(store.getItem(key) || '0') || 0 : 0; } catch (e) { return 0; }
      },
      set: function (v) { try { if (store && key) store.setItem(key, String(v)); } catch (e) { /* ignore */ } },
      clear: function () { try { if (store && key) store.removeItem(key); } catch (e) { /* ignore */ } }
    };
  }

  /*
   * Video lesson player. The page shows a light poster button; only a click loads the YouTube IFrame API and
   * player (privacy-enhanced youtube-nocookie.com), so the page stays light. The learner must watch to the end:
   * skipping ahead past the furthest point watched jumps back, and "LENGKAP & TERUSKAN" is enabled only when the
   * video ends. Speeds up to 2x are allowed (VIDEO_MAX_SPEED in src/helpers.php). The furthest point watched is
   * kept by o.progress ({ get, set, clear }) so a reload continues from there. A video already completed can be
   * watched freely.
   * Keep in sync with videoLesson() in docs/assets/js/app.js.
   */
  function videoLesson(o) {
    var MAX_SPEED = 2;
    var SKIP_TOLERANCE = 2.5; // seconds ahead of the furthest point that still count as normal playback
    var root = o.root;
    var facade = root.querySelector('#video-play');
    var host = root.querySelector('#video-player');
    var text = root.querySelector('#video-watch-text');
    var bar = root.querySelector('#video-watch-bar');
    var completeBtn = root.querySelector('#complete-btn');
    var done = !!o.done;
    var duration = o.duration;
    var maxWatched = done ? 0 : o.progress.get();
    var player = null;
    var timer = null;
    var lastSaved = 0;
    var noteUntil = 0;

    function fmt(sec) {
      sec = Math.max(0, Math.floor(sec));
      var h = Math.floor(sec / 3600);
      var m = Math.floor((sec % 3600) / 60);
      var s = sec % 60;
      return (h > 0 ? h + ':' + (m < 10 ? '0' : '') + m : String(m)) + ':' + (s < 10 ? '0' : '') + s;
    }
    function setText(msg) { if (text) text.textContent = msg; }
    function showProgress() {
      if (done) return;
      if (bar) bar.style.width = (duration > 0 ? Math.min(100, maxWatched * 100 / duration) : 0) + '%';
      if (Date.now() >= noteUntil) setText('Ditonton ' + fmt(maxWatched) + ' / ' + fmt(duration));
    }
    function note(msg) {
      noteUntil = Date.now() + 4000;
      setText(msg);
    }
    function save(force) {
      if (done || (!force && Date.now() - lastSaved < 5000)) return;
      lastSaved = Date.now();
      o.progress.set(Math.floor(maxWatched));
    }
    function finish() {
      done = true;
      stopTimer();
      o.progress.clear();
      if (bar) bar.style.width = '100%';
      setText('Video selesai ditonton. Tekan LENGKAP & TERUSKAN untuk ke kuiz.');
      if (completeBtn) completeBtn.disabled = false;
      root.classList.add('is-watched');
      if (o.onWatched) o.onWatched();
    }
    function tick() {
      if (!player || typeof player.getCurrentTime !== 'function') return;
      var d = player.getDuration ? player.getDuration() : 0;
      if (d > 0) duration = d;
      var t = player.getCurrentTime();
      if (!done && t > maxWatched + SKIP_TOLERANCE) {
        player.seekTo(maxWatched, true);
        note('Video tidak boleh dilangkau ke hadapan. Sila tonton sampai habis.');
      } else if (t > maxWatched) {
        maxWatched = t;
      }
      showProgress();
      save(false);
      if (o.onActivity) o.onActivity();
    }
    function startTimer() { if (!timer) timer = setInterval(tick, 500); }
    function stopTimer() { if (timer) { clearInterval(timer); timer = null; } }

    function onState(ev) {
      var YTS = window.YT && window.YT.PlayerState ? window.YT.PlayerState : { ENDED: 0, PLAYING: 1 };
      if (ev.data === YTS.PLAYING) {
        startTimer();
      } else if (ev.data === YTS.ENDED) {
        tick();
        if (done || maxWatched >= duration - 3) {
          finish();
        } else {
          stopTimer();
          player.seekTo(maxWatched, true);
          note('Video tidak boleh dilangkau ke hadapan. Sila tonton sampai habis.');
        }
      } else {
        tick();
        stopTimer();
        save(true);
      }
    }
    function onRate(ev) {
      if (ev.data > MAX_SPEED && player.setPlaybackRate) {
        player.setPlaybackRate(MAX_SPEED);
        note('Kelajuan maksimum ialah ' + MAX_SPEED + 'x.');
      }
    }
    function fail() {
      root.classList.remove('is-loading');
      facade.disabled = false;
      note('Video tidak dapat dimuatkan. Semak sambungan internet anda dan cuba lagi.');
    }

    facade.addEventListener('click', function () {
      if (player) return;
      root.classList.add('is-loading');
      facade.disabled = true;
      loadYouTubeApi().then(function (YT) {
        player = new YT.Player(host, {
          host: 'https://www.youtube-nocookie.com',
          videoId: o.youtube,
          width: '100%',
          height: '100%',
          playerVars: { autoplay: 1, rel: 0, playsinline: 1, modestbranding: 1, start: done ? 0 : Math.floor(maxWatched) },
          events: {
            onReady: function () {
              root.classList.remove('is-loading');
              root.classList.add('is-playing');
              facade.hidden = true;
              if (player.playVideo) player.playVideo();
            },
            onStateChange: onState,
            onPlaybackRateChange: onRate,
            onError: function () { note('Video tidak dapat dimainkan. Sila cuba lagi sebentar lagi.'); }
          }
        });
      }).catch(fail);
    });
    showProgress();
    return {
      destroy: function () {
        save(true);
        stopTimer();
        if (player && player.destroy) player.destroy();
        player = null;
      }
    };
  }

  // Loads the YouTube IFrame API once (only after the first click on a video).
  var youTubeApi = null;
  function loadYouTubeApi() {
    if (window.YT && window.YT.Player) return Promise.resolve(window.YT);
    if (youTubeApi) return youTubeApi;
    youTubeApi = new Promise(function (resolve, reject) {
      var prev = window.onYouTubeIframeAPIReady;
      var timeout = setTimeout(function () { youTubeApi = null; reject(new Error('timeout')); }, 15000);
      window.onYouTubeIframeAPIReady = function () {
        clearTimeout(timeout);
        if (typeof prev === 'function') prev();
        resolve(window.YT);
      };
      var s = document.createElement('script');
      s.src = 'https://www.youtube.com/iframe_api';
      s.async = true;
      s.onerror = function () { clearTimeout(timeout); youTubeApi = null; reject(new Error('load')); };
      document.head.appendChild(s);
    });
    return youTubeApi;
  }
})();
