/*
 * NADI LMS static preview (GitHub Pages draft).
 * Mirrors the PHP portal's flow with no backend: state lives in localStorage only.
 * Content below is copied from content/course.php and content/quiz.php.
 */
(function () {
  'use strict';

  // ---- Content (keep in sync with content/course.php and content/quiz.php) ----
  function placeholderSlides(topic, count) {
    var slides = [];
    for (var i = 1; i <= count; i++) {
      slides.push({
        title: topic + ' - Slaid ' + i,
        body: 'Kandungan contoh untuk slaid ' + i + ' bagi topik "' + topic + '". ' +
          'Gantikan teks ini dengan kandungan sebenar modul.'
      });
    }
    return slides;
  }
  function pres(key, title, topic, count) {
    return { key: key, title: title, type: 'presentation', slides: placeholderSlides(topic, count) };
  }

  var COURSE = {
    key: 'ai-essentials',
    title: 'Malaysia AWS AI Essentials',
    quizRequires: ['modul-1', 'modul-2', 'modul-3', 'modul-4', 'modul-5'],
    sections: [
      { key: 'pre-survey', title: 'Pre-Survey', lessons: [pres('pre-survey', 'Pre-Survey', 'Pre-Survey', 1)] },
      { key: 'modul-1', title: 'Modul 1: Kesedaran AI & Keyakinan Digital',
        lessons: [pres('modul-1-pengenalan', 'Pengenalan AI', 'Pengenalan AI', 5)] },
      { key: 'modul-2', title: 'Modul 2: Asas AI',
        lessons: [pres('modul-2-pengenalan', 'Asas AI', 'Asas AI', 5)] },
      { key: 'modul-3', title: 'Modul 3: Kemahiran Praktikal AI',
        lessons: [pres('modul-3-pengenalan', 'Kemahiran Praktikal AI', 'Kemahiran Praktikal AI', 5)] },
      { key: 'modul-4', title: 'Modul 4: AI untuk Perniagaan Mikro & Produktiviti',
        lessons: [pres('modul-4-pengenalan', 'AI untuk Perniagaan Mikro', 'AI untuk Perniagaan Mikro', 5)] },
      { key: 'modul-5', title: 'Modul 5: AI Bertanggungjawab & Kesediaan Masa Hadapan',
        lessons: [pres('modul-5-pengenalan', 'AI Bertanggungjawab', 'AI Bertanggungjawab', 5)] },
      { key: 'kuiz-akhir', title: 'Kuiz Akhir', lessons: [{ key: 'kuiz-akhir', title: 'Kuiz Akhir', type: 'quiz' }] },
      { key: 'post-survey', title: 'Post-Survey', lessons: [pres('post-survey', 'Post-Survey', 'Post-Survey', 1)] },
      { key: 'bonus', title: 'Bonus Konten!', lessons: [pres('bonus-konten', 'Bonus Konten!', 'Bonus Konten', 2)] }
    ]
  };

  // In this draft the answers are in the browser. The real portal grades on the server.
  var QUIZ = {
    title: 'Kuiz Akhir',
    passMark: 75,
    questions: [
      { id: 'q1', text: 'Apakah maksud singkatan "AI"?', answer: 'b',
        options: { a: 'Automasi Internet', b: 'Kecerdasan Buatan (Artificial Intelligence)', c: 'Aplikasi Interaktif', d: 'Analisis Imej' } },
      { id: 'q2', text: 'Antara berikut, yang manakah contoh penggunaan AI dalam kehidupan harian?', answer: 'c',
        options: { a: 'Kalkulator biasa', b: 'Lampu suluh', c: 'Cadangan video di aplikasi penstriman', d: 'Jam dinding' } },
      { id: 'q3', text: 'Apakah langkah terbaik untuk meningkatkan keyakinan digital?', answer: 'c',
        options: { a: 'Mengelak semua teknologi baharu', b: 'Berkongsi kata laluan dengan rakan', c: 'Belajar dan mencuba alat digital secara berperingkat', d: 'Hanya bergantung kepada orang lain' } },
      { id: 'q4', text: 'Pembelajaran mesin (machine learning) ialah...', answer: 'a',
        options: { a: 'Cabang AI di mana komputer belajar daripada data', b: 'Proses membaiki mesin rosak', c: 'Kursus menaip pantas', d: 'Perisian antivirus' } },
      { id: 'q5', text: 'Apakah yang diperlukan oleh model AI untuk belajar?', answer: 'b',
        options: { a: 'Elektrik sahaja', b: 'Data', c: 'Kertas', d: 'Tiada apa-apa' } },
      { id: 'q6', text: 'Apakah "prompt" dalam konteks alat AI generatif?', answer: 'a',
        options: { a: 'Arahan atau soalan yang diberikan kepada AI', b: 'Jenis virus komputer', c: 'Butang untuk menutup aplikasi', d: 'Kabel sambungan internet' } },
      { id: 'q7', text: 'Bagaimanakah cara terbaik untuk mendapatkan jawapan yang lebih tepat daripada AI?', answer: 'b',
        options: { a: 'Menulis arahan yang ringkas tetapi kabur', b: 'Memberikan arahan yang jelas dan konteks yang mencukupi', c: 'Menggunakan huruf besar sahaja', d: 'Bertanya soalan yang sama berulang kali tanpa perubahan' } },
      { id: 'q8', text: 'Bagaimanakah AI boleh membantu perniagaan mikro?', answer: 'b',
        options: { a: 'Menggantikan semua pelanggan', b: 'Menghasilkan kandungan pemasaran dan menjimatkan masa', c: 'Menghapuskan keperluan menyimpan rekod', d: 'Menjamin keuntungan setiap bulan' } },
      { id: 'q9', text: 'Apakah yang perlu dilakukan sebelum berkongsi maklumat yang dihasilkan oleh AI?', answer: 'c',
        options: { a: 'Terus kongsi tanpa semakan', b: 'Padam semua sumber', c: 'Semak ketepatan maklumat tersebut', d: 'Tukar fon tulisan' } },
      { id: 'q10', text: 'Antara berikut, yang manakah amalan AI yang bertanggungjawab?', answer: 'c',
        options: { a: 'Memasukkan data peribadi orang lain tanpa izin', b: 'Menggunakan AI untuk menyebarkan berita palsu', c: 'Melindungi privasi dan menggunakan AI secara beretika', d: 'Mempercayai semua output AI sepenuhnya' } }
    ]
  };

  var ISSUER = 'NADI';
  var MONTHS = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];

  // ---- Course helpers ----
  var LESSONS = [];
  COURSE.sections.forEach(function (s) {
    s.lessons.forEach(function (l) { l.sectionKey = s.key; LESSONS.push(l); });
  });
  function lessonByKey(key) {
    for (var i = 0; i < LESSONS.length; i++) if (LESSONS[i].key === key) return LESSONS[i];
    return null;
  }
  function nextLesson(key) {
    for (var i = 0; i < LESSONS.length; i++) if (LESSONS[i].key === key) return LESSONS[i + 1] || null;
    return null;
  }
  function quizLesson() {
    for (var i = 0; i < LESSONS.length; i++) if (LESSONS[i].type === 'quiz') return LESSONS[i];
    return null;
  }
  function lessonLabel(l) {
    return l.type === 'quiz' ? 'KUIZ - ' + QUIZ.questions.length + ' SOALAN' : 'PEMBENTANGAN';
  }

  // ---- State (localStorage) ----
  var STORE_KEY = 'nadi-lms-draft-v1';
  function emptyState() { return { learner: null, done: {}, attempts: [], completion: null }; }
  function load() {
    try {
      var raw = window.localStorage.getItem(STORE_KEY);
      if (!raw) return emptyState();
      var s = JSON.parse(raw);
      if (!s || typeof s !== 'object') return emptyState();
      return {
        learner: s.learner || null,
        done: s.done || {},
        attempts: Array.isArray(s.attempts) ? s.attempts : [],
        completion: s.completion || null
      };
    } catch (e) {
      return emptyState();
    }
  }
  var state = load();
  function save() {
    try { window.localStorage.setItem(STORE_KEY, JSON.stringify(state)); } catch (e) { /* private mode: keep in memory */ }
  }
  function resetState() {
    state = emptyState();
    try { window.localStorage.removeItem(STORE_KEY); } catch (e) { /* ignore */ }
  }

  // ---- Utilities ----
  function e(v) {
    return String(v === null || v === undefined ? '' : v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function todayIso() {
    var d = new Date();
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
  }
  function formatDateMs(iso) {
    var m = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso || '');
    if (!m) return iso;
    return parseInt(m[3], 10) + ' ' + MONTHS[parseInt(m[2], 10) - 1] + ' ' + m[1];
  }
  function randomCode() {
    var bytes = new Uint8Array(5);
    window.crypto.getRandomValues(bytes);
    var hex = '';
    for (var i = 0; i < bytes.length; i++) hex += (bytes[i] < 16 ? '0' : '') + bytes[i].toString(16);
    return 'NADI-' + hex.toUpperCase();
  }
  function fullName() { return state.learner ? state.learner.first_name + ' ' + state.learner.last_name : ''; }
  function courseHref(key, slide) {
    return '#/kursus?lesson=' + encodeURIComponent(key) + (slide ? '&slide=' + slide : '');
  }
  function go(hash) {
    if (window.location.hash === hash) render(); else window.location.hash = hash;
  }

  // ---- Routing ----
  function parseHash() {
    var h = window.location.hash.replace(/^#/, '');
    var qi = h.indexOf('?');
    var path = qi === -1 ? h : h.slice(0, qi);
    var params = {};
    if (qi !== -1) {
      h.slice(qi + 1).split('&').forEach(function (pair) {
        if (!pair) return;
        var kv = pair.split('=');
        params[decodeURIComponent(kv[0])] = decodeURIComponent(kv[1] || '');
      });
    }
    return { path: path || '/', params: params };
  }

  var app = document.getElementById('app');
  var viewerCtl = null; // slide viewer controller for keyboard navigation

  function render() {
    var r = parseHash();
    viewerCtl = null;
    document.body.className = '';
    if (r.path === '/daftar') return renderRegister();
    if (r.path === '/kursus') {
      if (!state.learner) return go('#/daftar');
      return renderCourse(r.params);
    }
    if (r.path === '/sijil') {
      if (!state.learner) return go('#/daftar');
      if (!state.completion) return go(courseHref(quizLesson().key));
      return renderCertificate();
    }
    return renderLanding();
  }

  function siteHeader() {
    return '<header class="site-header"><a class="site-logo" href="#/"><img src="assets/img/logo.svg" alt="NADI" height="36"></a></header>';
  }
  function setPage(title, html) {
    document.title = (title ? title + ' | ' : '') + 'NADI LMS (Draf)';
    app.innerHTML = html;
    window.scrollTo(0, 0);
  }

  // ---- Landing ----
  function renderLanding() {
    var list = COURSE.sections.map(function (s) {
      return '<li><strong>' + e(s.title) + '</strong><span class="muted small">' +
        s.lessons.map(function (l) { return e(lessonLabel(l)); }).join(' &middot; ') + '</span></li>';
    }).join('');
    var actions;
    if (state.learner) {
      actions = '<p>Selamat kembali, <strong>' + e(fullName()) + '</strong>.</p>' +
        '<div class="hero-actions"><a class="btn btn-primary btn-lg" href="#/kursus">Teruskan Kursus</a>' +
        '<button type="button" class="btn-link" id="reset-btn">Bukan anda? Mula semula</button></div>';
    } else {
      actions = '<div class="hero-actions"><a class="btn btn-primary btn-lg" href="#/daftar">Daftar Sekarang</a></div>' +
        '<p class="muted small">Tiada akaun atau kata laluan diperlukan. Hanya isi maklumat asas anda.</p>';
    }
    setPage('', siteHeader() +
      '<main><section class="hero"><div class="container">' +
      '<p class="eyebrow">Kursus Dalam Talian NADI</p>' +
      '<h1>' + e(COURSE.title) + '</h1>' +
      '<p class="lead">Pelajari asas kecerdasan buatan (AI) secara percuma mengikut kadar anda sendiri. ' +
      'Lengkapkan kelima-lima modul, lulus Kuiz Akhir dengan markah sekurang-kurangnya ' + QUIZ.passMark +
      '%, dan muat turun sijil penyelesaian anda serta-merta.</p>' + actions +
      '</div></section>' +
      '<section class="container"><h2>Kandungan Kursus</h2><ol class="section-list">' + list + '</ol></section></main>');
    var reset = document.getElementById('reset-btn');
    if (reset) reset.addEventListener('click', function () { resetState(); render(); });
  }

  // ---- Registration ----
  var FIELDS = [
    ['first_name', 'Nama Pertama', 'text', 'maxlength="100" autocomplete="given-name"'],
    ['last_name', 'Nama Akhir', 'text', 'maxlength="100" autocomplete="family-name"'],
    ['age', 'Umur', 'number', 'min="5" max="120" step="1" inputmode="numeric"'],
    ['email', 'E-mel', 'email', 'maxlength="190" autocomplete="email"'],
    ['phone', 'Nombor Telefon', 'tel', 'maxlength="20" autocomplete="tel"'],
    ['address', 'Alamat', 'text', 'maxlength="255" autocomplete="street-address"'],
    ['occupation', 'Pekerjaan', 'text', 'maxlength="100" autocomplete="organization-title"']
  ];

  // Same rules and messages as src/validation.php.
  function validateRegistration(input) {
    var c = {};
    FIELDS.forEach(function (f) { c[f[0]] = String(input[f[0]] || '').trim(); });
    var err = {};
    if (c.first_name === '') err.first_name = 'Sila masukkan nama pertama.';
    else if (c.first_name.length > 100) err.first_name = 'Nama pertama tidak boleh melebihi 100 aksara.';
    if (c.last_name === '') err.last_name = 'Sila masukkan nama akhir.';
    else if (c.last_name.length > 100) err.last_name = 'Nama akhir tidak boleh melebihi 100 aksara.';
    if (c.age === '') err.age = 'Sila masukkan umur.';
    else if (!/^[0-9]{1,3}$/.test(c.age) || +c.age < 5 || +c.age > 120) err.age = 'Umur mestilah nombor antara 5 hingga 120.';
    if (c.email === '') err.email = 'Sila masukkan alamat e-mel.';
    else if (c.email.length > 190 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(c.email)) err.email = 'Alamat e-mel tidak sah.';
    if (c.phone === '') err.phone = 'Sila masukkan nombor telefon.';
    else if (!/^\+?[0-9 \-]{7,20}$/.test(c.phone)) err.phone = 'Nombor telefon tidak sah (7 hingga 20 digit).';
    if (c.address === '') err.address = 'Sila masukkan alamat.';
    else if (c.address.length > 255) err.address = 'Alamat tidak boleh melebihi 255 aksara.';
    if (c.occupation === '') err.occupation = 'Sila masukkan pekerjaan.';
    else if (c.occupation.length > 100) err.occupation = 'Pekerjaan tidak boleh melebihi 100 aksara.';
    return { clean: c, errors: err };
  }

  function renderRegister(old, errors) {
    old = old || {};
    errors = errors || {};
    var hasErrors = Object.keys(errors).length > 0;
    var fields = FIELDS.map(function (f) {
      var name = f[0];
      return '<div class="field' + (errors[name] ? ' has-error' : '') + '">' +
        '<label for="f-' + name + '">' + e(f[1]) + ' <span class="req">*</span></label>' +
        '<input id="f-' + name + '" name="' + name + '" type="' + f[2] + '" value="' + e(old[name]) + '" required ' + f[3] + '>' +
        (errors[name] ? '<p class="field-error">' + e(errors[name]) + '</p>' : '') +
        '</div>';
    }).join('');
    setPage('Pendaftaran', siteHeader() +
      '<main class="container narrow"><div class="card">' +
      '<h1>Pendaftaran Peserta</h1>' +
      '<p class="muted">Isi maklumat di bawah untuk mula belajar. Nama anda akan dicetak pada sijil seperti yang ditulis di sini.</p>' +
      (hasErrors ? '<div class="alert alert-error" role="alert">Sila betulkan ralat di bawah.</div>' : '') +
      '<form class="form" id="register-form" novalidate>' + fields +
      '<button type="submit" class="btn btn-primary btn-block">Hantar &amp; Mula Kursus</button>' +
      '</form></div></main>');
    document.getElementById('register-form').addEventListener('submit', function (ev) {
      ev.preventDefault();
      var input = {};
      FIELDS.forEach(function (f) { input[f[0]] = ev.target.elements[f[0]].value; });
      var res = validateRegistration(input);
      if (Object.keys(res.errors).length > 0) {
        renderRegister(res.clean, res.errors);
        var firstBad = app.querySelector('.has-error input');
        if (firstBad) firstBad.focus();
        return;
      }
      resetState();
      res.clean.age = parseInt(res.clean.age, 10);
      state.learner = res.clean;
      save();
      go('#/kursus');
    });
  }

  // ---- Course viewer ----
  function renderCourse(params) {
    var lesson = params.lesson ? lessonByKey(params.lesson) : null;
    if (params.lesson && !lesson) {
      setPage('Ralat', siteHeader() + '<main class="container narrow"><div class="card"><h1>Ralat</h1>' +
        '<p>Pelajaran tidak dijumpai.</p><p><a href="#/kursus">Kembali ke kursus</a></p></div></main>');
      return;
    }
    if (!lesson) {
      for (var i = 0; i < LESSONS.length && !lesson; i++) if (!state.done[LESSONS[i].key]) lesson = LESSONS[i];
      if (!lesson) lesson = LESSONS[0];
    }
    var slideCount = lesson.type === 'presentation' ? lesson.slides.length : 0;
    var slide = 1;
    if (slideCount > 0) {
      var req = parseInt(params.slide, 10);
      slide = Math.max(1, Math.min(slideCount, isNaN(req) ? 1 : req));
    }
    var doneCount = LESSONS.filter(function (l) { return state.done[l.key]; }).length;
    var percent = Math.round(doneCount * 100 / LESSONS.length);

    var sidebar = COURSE.sections.map(function (s) {
      var sDone = s.lessons.filter(function (l) { return state.done[l.key]; }).length;
      var rows = s.lessons.map(function (l) {
        var isDone = !!state.done[l.key];
        var isCurrent = l.key === lesson.key;
        return '<li class="lesson-row' + (isCurrent ? ' is-current' : '') + (isDone ? ' is-done' : '') + '" data-title="' + e(l.title) + '">' +
          '<a href="' + courseHref(l.key) + '"' + (isCurrent ? ' aria-current="page"' : '') + '>' +
          '<span class="lesson-tick" aria-hidden="true">' + (isDone ? '&#10003;' : '') + '</span>' +
          '<span class="lesson-meta"><span class="lesson-title">' + e(l.title) + '</span>' +
          '<span class="lesson-label">' + e(lessonLabel(l)) + '</span></span>' +
          (isDone ? '<span class="sr-only">(selesai)</span>' : '') + '</a></li>';
      }).join('');
      return '<details class="sidebar-section" data-section="' + e(s.key) + '"' + (s.key === lesson.sectionKey ? ' open' : '') + '>' +
        '<summary><span class="section-title">' + e(s.title) + '</span>' +
        '<span class="section-count">' + sDone + '/' + s.lessons.length + '</span></summary>' +
        '<ul class="lesson-list">' + rows + '</ul></details>';
    }).join('');

    var main = lesson.type === 'presentation' ? presentationHtml(lesson, slide, slideCount) : quizHtml(lesson, params);

    document.body.className = 'course-page';
    setPage(lesson.title,
      '<header class="topbar"><a class="topbar-home" href="#/">&larr; Pergi ke Laman Utama</a>' +
      '<a class="topbar-logo" href="#/"><img src="assets/img/logo.svg" alt="NADI" height="32"></a>' +
      '<span class="topbar-user">' + e(fullName()) + '</span></header>' +
      '<div class="course-layout"><aside class="sidebar">' +
      '<h2 class="sidebar-title">' + e(COURSE.title) + '</h2>' +
      '<div class="progress"><div class="progress-text">' + percent + '% selesai</div>' +
      '<div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + percent + '">' +
      '<span style="width: ' + percent + '%"></span></div></div>' +
      '<input type="search" id="lesson-search" class="sidebar-search" placeholder="Cari mengikut tajuk pelajaran" aria-label="Cari mengikut tajuk pelajaran">' +
      '<nav class="sidebar-sections">' + sidebar + '</nav></aside>' +
      '<main class="content">' + main + '</main></div>');

    bindSearch();
    if (lesson.type === 'presentation') bindViewer(lesson, slide, slideCount);
    else bindQuiz(lesson);
  }

  function presentationHtml(lesson, slide, slideCount) {
    var slides = lesson.slides.map(function (s, i) {
      var n = i + 1;
      return '<article class="slide" data-slide="' + n + '"' + (n === slide ? '' : ' hidden') + '>' +
        '<h2>' + e(s.title) + '</h2><p>' + e(s.body) + '</p></article>';
    }).join('');
    return '<div class="lesson-header"><h1>' + e(lesson.title) + '</h1>' +
      '<button type="button" class="btn btn-outline" id="fullscreen-btn">Skrin Penuh</button></div>' +
      '<div class="viewer" id="viewer"><div class="slides">' + slides + '</div>' +
      '<div class="viewer-bar">' +
      '<a class="btn btn-nav" id="prev-btn">&lsaquo; SEBELUM</a>' +
      '<span class="slide-counter" id="slide-counter">' + slide + '/' + slideCount + '</span>' +
      '<a class="btn btn-nav" id="next-btn">SETERUSNYA &rsaquo;</a>' +
      '<form class="complete-form" id="complete-form">' +
      '<button type="submit" class="btn btn-primary" id="complete-btn">LENGKAP &amp; TERUSKAN</button></form>' +
      '</div></div>';
  }

  function bindViewer(lesson, startSlide, count) {
    var viewer = document.getElementById('viewer');
    var slides = viewer.querySelectorAll('[data-slide]');
    var prevBtn = document.getElementById('prev-btn');
    var nextBtn = document.getElementById('next-btn');
    var counter = document.getElementById('slide-counter');
    var completeBtn = document.getElementById('complete-btn');
    var current = startSlide;
    function setLink(el, enabled, n) {
      if (enabled) {
        el.classList.remove('is-disabled');
        el.removeAttribute('aria-disabled');
        el.setAttribute('href', courseHref(lesson.key, n));
      } else {
        el.classList.add('is-disabled');
        el.setAttribute('aria-disabled', 'true');
        el.removeAttribute('href');
      }
    }
    function show(n) {
      if (n < 1 || n > count) return;
      current = n;
      for (var i = 0; i < slides.length; i++) slides[i].hidden = parseInt(slides[i].getAttribute('data-slide'), 10) !== n;
      counter.textContent = n + '/' + count;
      setLink(prevBtn, n > 1, n - 1);
      setLink(nextBtn, n < count, n + 1);
      completeBtn.disabled = n !== count;
      if (window.history && window.history.replaceState) window.history.replaceState(null, '', courseHref(lesson.key, n));
    }
    prevBtn.addEventListener('click', function (ev) { ev.preventDefault(); show(current - 1); });
    nextBtn.addEventListener('click', function (ev) { ev.preventDefault(); show(current + 1); });
    document.getElementById('complete-form').addEventListener('submit', function (ev) {
      ev.preventDefault();
      if (current !== count) return;
      state.done[lesson.key] = true;
      save();
      var next = nextLesson(lesson.key);
      go(courseHref(next ? next.key : lesson.key));
    });
    document.getElementById('fullscreen-btn').addEventListener('click', function () {
      var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
      if (fsEl) (document.exitFullscreen || document.webkitExitFullscreen).call(document);
      else if (viewer.requestFullscreen) viewer.requestFullscreen();
      else if (viewer.webkitRequestFullscreen) viewer.webkitRequestFullscreen();
    });
    viewerCtl = { prev: function () { show(current - 1); }, next: function () { show(current + 1); } };
    show(startSlide);
  }

  document.addEventListener('keydown', function (ev) {
    if (!viewerCtl) return;
    var tag = (ev.target && ev.target.tagName) || '';
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
    if (ev.key === 'ArrowLeft') viewerCtl.prev();
    else if (ev.key === 'ArrowRight') viewerCtl.next();
  });

  function bindSearch() {
    var search = document.getElementById('lesson-search');
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

  // ---- Final quiz ----
  function missingForQuiz() {
    return LESSONS.filter(function (l) {
      return l.type === 'presentation' && COURSE.quizRequires.indexOf(l.sectionKey) !== -1 && !state.done[l.key];
    });
  }
  function formatPercent(p) { return String(Math.round(p * 100) / 100); }

  function quizHtml(lesson, params) {
    var head = '<div class="lesson-header"><h1>' + e(lesson.title) + '</h1>' +
      '<span class="lesson-label">' + e(lessonLabel(lesson)) + '</span></div>';
    var missing = missingForQuiz();
    if (missing.length) {
      return head + '<div class="card quiz-gate">' +
        '<p class="alert alert-warning">Sila lengkapkan semua modul (Modul 1 hingga Modul 5) sebelum menjawab Kuiz Akhir.</p>' +
        '<p>Pelajaran yang belum selesai:</p><ul>' +
        missing.map(function (m) { return '<li><a href="' + courseHref(m.key) + '">' + e(m.title) + '</a></li>'; }).join('') +
        '</ul></div>';
    }
    var html = head;
    if (state.completion) {
      html += '<div class="alert alert-success">Kursus ini telah selesai. <a href="#/sijil">Lihat sijil anda</a>.</div>';
    }
    var attempt = null;
    if (params.attempt) {
      var id = parseInt(params.attempt, 10);
      attempt = state.attempts.filter(function (a) { return a.id === id; })[0] || null;
    }
    if (attempt) {
      html += '<div class="card quiz-result ' + (attempt.passed ? 'is-pass' : 'is-fail') + '">' +
        '<p class="quiz-score">Markah anda: ' + attempt.correct + '/' + attempt.total + ' (' + formatPercent(attempt.percent) + '%)</p>' +
        '<p class="quiz-status">' + (attempt.passed ? 'LULUS' : 'GAGAL') + '</p>';
      if (!attempt.passed) {
        html += '<p>Markah lulus ialah ' + QUIZ.passMark + '%. Anda boleh mencuba lagi tanpa had.</p>' +
          '<a class="btn btn-primary" href="' + courseHref(lesson.key) + '">Cuba Lagi</a>';
      } else if (!state.completion) {
        html += '<p>Tahniah! Tekan butang di bawah untuk menghantar penyelesaian kursus dan mendapatkan sijil anda.</p>' +
          '<form id="finish-form"><button type="submit" class="btn btn-primary">LENGKAP &amp; TERUSKAN</button></form>';
      } else {
        html += '<a class="btn btn-primary" href="#/sijil">Lihat Sijil</a>';
      }
      return html + '</div>';
    }
    var qs = QUIZ.questions.map(function (q, qi) {
      var opts = Object.keys(q.options).map(function (k) {
        return '<label class="quiz-option"><input type="radio" name="' + e(q.id) + '" value="' + e(k) + '" required>' +
          '<span>' + e(q.options[k]) + '</span></label>';
      }).join('');
      return '<fieldset class="quiz-question"><legend>' + (qi + 1) + '. ' + e(q.text) + '</legend>' + opts + '</fieldset>';
    }).join('');
    return html + '<form class="card quiz-form" id="quiz-form">' +
      '<p class="muted">Markah lulus: ' + QUIZ.passMark + '% &middot; Percubaan ke-' + (state.attempts.length + 1) + '</p>' +
      qs + '<button type="submit" class="btn btn-primary">Hantar Jawapan</button></form>';
  }

  function bindQuiz(lesson) {
    var form = document.getElementById('quiz-form');
    if (form) {
      form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var correct = 0;
        QUIZ.questions.forEach(function (q) {
          var picked = form.querySelector('input[name="' + q.id + '"]:checked');
          if (picked && picked.value === q.answer) correct++;
        });
        var total = QUIZ.questions.length;
        var percent = correct * 100 / total;
        var id = state.attempts.length + 1;
        state.attempts.push({ id: id, correct: correct, total: total, percent: percent, passed: percent >= QUIZ.passMark });
        save();
        go(courseHref(lesson.key) + '&attempt=' + id);
      });
    }
    var finish = document.getElementById('finish-form');
    if (finish) {
      finish.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var passed = state.attempts.some(function (a) { return a.passed; });
        if (!passed) return go(courseHref(lesson.key));
        if (!state.completion) state.completion = { code: randomCode(), completedAt: todayIso() };
        state.done[lesson.key] = true;
        save();
        go('#/sijil');
      });
    }
  }

  // ---- Certificate ----
  function renderCertificate() {
    var c = state.completion;
    setPage('Sijil', siteHeader() +
      '<main class="container narrow"><div class="card certificate-card">' +
      '<h1>Tahniah!</h1>' +
      '<p>Anda telah berjaya menamatkan kursus ini. Sijil anda sudah sedia untuk dimuat turun.</p>' +
      '<dl class="certificate-details">' +
      '<dt>Nama</dt><dd>' + e(fullName()) + '</dd>' +
      '<dt>Kursus</dt><dd>' + e(COURSE.title) + '</dd>' +
      '<dt>Tarikh</dt><dd>' + e(formatDateMs(c.completedAt)) + '</dd>' +
      '<dt>No. Sijil</dt><dd>' + e(c.code) + '</dd></dl>' +
      '<p><button type="button" class="btn btn-primary btn-lg" id="download-btn">Muat Turun Sijil (PDF)</button></p>' +
      '<p class="muted small">Ini sijil contoh daripada draf pratonton. Pengesahan sijil hanya tersedia dalam portal sebenar.</p>' +
      '<p><a href="' + courseHref(quizLesson().key) + '">Kembali ke kursus</a></p>' +
      '</div></main>');
    document.getElementById('download-btn').addEventListener('click', function () {
      var data = {
        first_name: state.learner.first_name,
        last_name: state.learner.last_name,
        course_title: COURSE.title,
        date_text: formatDateMs(c.completedAt),
        code: c.code,
        issuer: ISSUER
      };
      var blob = certificatePdf(data);
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      a.download = 'sijil-' + c.code + '.pdf';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      setTimeout(function () { URL.revokeObjectURL(url); }, 10000);
    });
  }

  // Draw the certificate on a canvas, using the same layout (in mm) as src/CertificatePdf.php.
  function drawCertificate(d) {
    var W = 297, H = 210, S = 8; // A4 landscape, 8 px per mm
    var canvas = document.createElement('canvas');
    canvas.width = W * S;
    canvas.height = H * S;
    var g = canvas.getContext('2d');
    var PT = 25.4 / 72; // mm per point
    g.fillStyle = '#ffffff';
    g.fillRect(0, 0, canvas.width, canvas.height);
    g.strokeStyle = 'rgb(31,78,140)';
    g.lineWidth = 1.5 * S;
    g.strokeRect(10 * S, 10 * S, (W - 20) * S, (H - 20) * S);
    g.lineWidth = 0.5 * S;
    g.strokeRect(14 * S, 14 * S, (W - 28) * S, (H - 28) * S);
    g.textAlign = 'center';
    g.textBaseline = 'middle';
    function cell(y, h, text, family, weight, sizePt, color) {
      g.fillStyle = color;
      g.font = weight + ' ' + (sizePt * PT * S) + 'px ' + family;
      g.fillText(text, (W / 2) * S, (y + h / 2) * S, (W - 40) * S);
    }
    var sans = 'Helvetica, Arial, sans-serif';
    var serif = '"Times New Roman", Times, serif';
    var ink = 'rgb(29,36,51)';
    cell(26, 10, d.issuer, sans, 'bold', 22, 'rgb(31,78,140)');
    cell(48, 14, 'SIJIL PENYELESAIAN', sans, 'bold', 30, ink);
    cell(72, 8, 'Dengan ini disahkan bahawa', sans, 'normal', 14, ink);
    cell(86, 16, d.first_name + ' ' + d.last_name, serif, 'bold', 32, ink);
    g.strokeStyle = 'rgb(180,180,180)';
    g.lineWidth = 0.3 * S;
    g.beginPath();
    g.moveTo((W / 2 - 70) * S, 104 * S);
    g.lineTo((W / 2 + 70) * S, 104 * S);
    g.stroke();
    cell(112, 8, 'telah berjaya menamatkan kursus', sans, 'normal', 14, ink);
    cell(124, 12, d.course_title, sans, 'bold', 20, ink);
    cell(156, 7, 'Tarikh: ' + d.date_text, sans, 'normal', 12, ink);
    cell(164, 7, 'No. Sijil: ' + d.code, sans, 'normal', 12, ink);
    cell(186, 6, 'DRAF / PRATONTON', sans, 'normal', 9, 'rgb(150,150,150)');
    return canvas;
  }

  // Wrap the canvas as a JPEG image in a one-page PDF (A4 landscape).
  function certificatePdf(d) {
    var canvas = drawCertificate(d);
    var b64 = canvas.toDataURL('image/jpeg', 0.92).split(',')[1];
    var bin = atob(b64);
    var jpeg = new Uint8Array(bin.length);
    for (var i = 0; i < bin.length; i++) jpeg[i] = bin.charCodeAt(i);

    function utf16Hex(s) {
      var out = 'FEFF';
      for (var k = 0; k < s.length; k++) out += ('000' + s.charCodeAt(k).toString(16).toUpperCase()).slice(-4);
      return '<' + out + '>';
    }
    var parts = [];
    var offsets = [];
    var length = 0;
    function push(chunk) {
      if (typeof chunk === 'string') {
        var bytes = new Uint8Array(chunk.length);
        for (var k = 0; k < chunk.length; k++) bytes[k] = chunk.charCodeAt(k) & 0xff;
        chunk = bytes;
      }
      parts.push(chunk);
      length += chunk.length;
    }
    function obj(n, body) {
      offsets[n] = length;
      push(n + ' 0 obj\n' + body + '\nendobj\n');
    }
    var pw = 841.89, ph = 595.28;
    var content = 'q ' + pw + ' 0 0 ' + ph + ' 0 0 cm /Im0 Do Q';
    push('%PDF-1.4\n%\xE2\xE3\xCF\xD3\n');
    obj(1, '<< /Type /Catalog /Pages 2 0 R >>');
    obj(2, '<< /Type /Pages /Kids [3 0 R] /Count 1 >>');
    obj(3, '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' + pw + ' ' + ph + '] ' +
      '/Resources << /XObject << /Im0 5 0 R >> >> /Contents 4 0 R >>');
    obj(4, '<< /Length ' + content.length + ' >>\nstream\n' + content + '\nendstream');
    offsets[5] = length;
    push('5 0 obj\n<< /Type /XObject /Subtype /Image /Width ' + canvas.width + ' /Height ' + canvas.height +
      ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ' + jpeg.length + ' >>\nstream\n');
    push(jpeg);
    push('\nendstream\nendobj\n');
    obj(6, '<< /Title ' + utf16Hex('Sijil ' + d.code) + ' /Author ' + utf16Hex(d.issuer) +
      ' /Subject ' + utf16Hex(d.first_name + ' ' + d.last_name + ' - ' + d.course_title + ' - ' + d.date_text) +
      ' /Creator ' + utf16Hex('NADI LMS (draf)') + ' >>');
    var xref = length;
    var x = 'xref\n0 7\n0000000000 65535 f \n';
    for (var n = 1; n <= 6; n++) x += ('000000000' + offsets[n]).slice(-10) + ' 00000 n \n';
    push(x + 'trailer\n<< /Size 7 /Root 1 0 R /Info 6 0 R >>\nstartxref\n' + xref + '\n%%EOF\n');
    return new Blob(parts, { type: 'application/pdf' });
  }

  window.addEventListener('hashchange', render);
  render();
})();
