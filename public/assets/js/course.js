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
})();
