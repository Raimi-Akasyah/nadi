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
