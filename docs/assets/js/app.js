/*
 * NADI LMS static preview (GitHub Pages draft).
 * Mirrors the PHP portal's flow with no backend: state lives in the browser only
 * (sessionStorage, or localStorage when "Ingat saya" is ticked).
 * Content below is copied from content/course.php and content/quiz.php; slide images
 * are copies of public/assets/slides/ in docs/assets/slides/ (relative paths, so /nadi/ works).
 */
(function () {
  'use strict';
  // The drawer CSS (course sidebar below 960px) applies only under html.js, as in views/layout.php.
  document.documentElement.classList.add('js');

  // ---- Content (keep in sync with content/course.php and content/quiz.php) ----
  var COURSE = {
    key: 'ai-essentials',
    title: 'Malaysia AWS AI Essentials',
    sections: [
      { key: 'pengenalan', title: 'Pengenalan', lessons: [
        { key: 'pengenalan', title: 'Pengenalan', type: 'presentation', slides: [
          { title: 'AI Untuk Komuniti', image: 'assets/slides/page-01.webp' },
          { title: 'Rakan Strategik Program: AWS', image: 'assets/slides/page-07.webp' }
        ] }
      ] },
      { key: 'modul-1', title: 'Modul 1: Kesedaran AI & Keyakinan Digital', lessons: [
        { key: 'modul-1-pengenalan', title: 'Pengenalan AI', type: 'presentation', slides: [
          { title: 'Modul 1: Kesedaran AI & Keyakinan Digital', image: 'assets/slides/page-08.webp' },
          { title: 'Apakah Itu Kecerdasan Buatan (AI)?', image: 'assets/slides/page-09.webp' },
          { title: 'AI Bukan Pengganti Kita, Tapi Pembantu Kita', image: 'assets/slides/page-10.webp' }
        ] },
        { key: 'kuiz-modul-1', title: 'Kuiz Modul 1', type: 'quiz', quiz: 'modul-1' }
      ] },
      { key: 'modul-2', title: 'Modul 2: Asas AI', lessons: [
        { key: 'modul-2-pengenalan', title: 'Asas AI', type: 'presentation', slides: [
          { title: 'Modul 2: Asas AI', image: 'assets/slides/page-11.webp' },
          { title: 'Macam Mana AI Belajar?', image: 'assets/slides/page-12.webp' },
          { title: 'Apakah Itu AI Generatif?', image: 'assets/slides/page-13.webp' },
          { title: '\'Perangai\' Pekerja Baru AI Kita', image: 'assets/slides/page-14.webp' }
        ] },
        { key: 'kuiz-modul-2', title: 'Kuiz Modul 2', type: 'quiz', quiz: 'modul-2' }
      ] },
      { key: 'modul-3', title: 'Modul 3: Kemahiran Praktikal AI', lessons: [
        { key: 'modul-3-pengenalan', title: 'Kemahiran Praktikal AI', type: 'presentation', slides: [
          { title: 'Modul 3: Kemahiran Praktikal AI untuk Pembelajaran & Kerjaya', image: 'assets/slides/page-15.webp' },
          { title: 'Apa Itu "AI Prompt"?', image: 'assets/slides/page-16.webp' },
          { title: 'Mengapa Arahan yang Jelas Sangat Penting?', image: 'assets/slides/page-17.webp' },
          { title: 'Selesaikan Masalah dengan Formula KOPI!', image: 'assets/slides/page-18.webp' },
          { title: '4 Ramuan Rahsia dalam Secawan KOPI', image: 'assets/slides/page-19.webp' },
          { title: 'Transformasi Prompt: Pemasaran Tudung Raya', image: 'assets/slides/page-20.webp' },
          { title: '3 Peranan Utama AI untuk Bisnes Anda', image: 'assets/slides/page-21.webp' },
          { title: 'Latihan Berkumpulan: Selesaikan Krisis \'Sales Turun\' (bisnes.ai)', image: 'assets/slides/page-22.webp' },
          { title: 'Latihan Berkumpulan: Selesaikan Krisis \'Sales Turun\' (3 Langkah)', image: 'assets/slides/page-23.webp' }
        ] },
        { key: 'kuiz-modul-3', title: 'Kuiz Modul 3', type: 'quiz', quiz: 'modul-3' }
      ] },
      { key: 'modul-4', title: 'Modul 4: AI untuk Perniagaan Mikro & Produktiviti', lessons: [
        { key: 'modul-4-pengenalan', title: 'AI untuk Perniagaan Mikro', type: 'presentation', slides: [
          { title: 'Modul 4: AI untuk Perniagaan Mikro & Produktiviti', image: 'assets/slides/page-24.webp' },
          { title: 'Janji 45 Minit Anda', image: 'assets/slides/page-25.webp' },
          { title: 'Langkah 1: Jana Idea Perniagaan', image: 'assets/slides/page-26.webp' },
          { title: 'Langkah 1: Jana Idea Perniagaan (Kanvas Model Perniagaan)', image: 'assets/slides/page-27.webp' },
          { title: 'Langkah 2: Tulis Copywriting Media Sosial', image: 'assets/slides/page-28.webp' },
          { title: 'Langkah 2: Tulis Copywriting Media Sosial (Prompt KOPI)', image: 'assets/slides/page-29.webp' },
          { title: 'Langkah 4: Visual Poster', image: 'assets/slides/page-30.webp' },
          { title: 'Langkah 4: Visual Poster (Contoh Prompt)', image: 'assets/slides/page-31.webp' },
          { title: 'Langkah 5: Komunikasi Pelanggan Profesional', image: 'assets/slides/page-32.webp' },
          { title: 'Langkah 6: Skrip Video Promosi (30 Saat)', image: 'assets/slides/page-33.webp' },
          { title: 'Langkah 6: Skrip Video Promosi (Prompt KOPI)', image: 'assets/slides/page-34.webp' },
          { title: 'Jom Hasilkan Aplikasi (PartyRock)', image: 'assets/slides/page-35.webp' }
        ] },
        { key: 'kuiz-modul-4', title: 'Kuiz Modul 4', type: 'quiz', quiz: 'modul-4' }
      ] },
      { key: 'modul-5', title: 'Modul 5: AI Bertanggungjawab & Kesediaan Masa Hadapan', lessons: [
        { key: 'modul-5-pengenalan', title: 'AI Bertanggungjawab', type: 'presentation', slides: [
          { title: 'Modul 5: AI Bertanggungjawab & Kesediaan Masa Hadapan', image: 'assets/slides/page-36.webp' },
          { title: '4 Tanda Amaran: Gunakan S.T.O.P', image: 'assets/slides/page-37.webp' },
          { title: 'Berhenti. Semak. Sahkan.', image: 'assets/slides/page-38.webp' },
          { title: 'Jana Pendapatan Tambahan dengan Bantuan AI', image: 'assets/slides/page-39.webp' },
          { title: 'Apa Maksud \'Kerja Freelance\'?', image: 'assets/slides/page-40.webp' },
          { title: 'Kenali 4 Alat AI yang Akan Membantu Anda', image: 'assets/slides/page-41.webp' },
          { title: 'Teruskan Pembelajaran Anda Selepas Bengkel Ini', image: 'assets/slides/page-42.webp' }
        ] },
        { key: 'kuiz-modul-5', title: 'Kuiz Modul 5', type: 'quiz', quiz: 'modul-5' }
      ] },
      { key: 'penutup', title: 'Penutup', lessons: [
        { key: 'penutup', title: 'Penutup', type: 'presentation', slides: [
          { title: 'Terima Kasih', image: 'assets/slides/page-45.webp' }
        ] }
      ] }
    ]
  };

  // In this draft the answers are in the browser. The real portal grades on the server.
  var QUIZ = {
    passMark: 75,
    quizzes: {
      'modul-1': { title: 'Kuiz Modul 1', questions: [
        { id: 'q1', text: 'Apakah maksud singkatan "AI"?', answer: 'b',
          options: { a: 'Automasi Internet', b: 'Kecerdasan Buatan (Artificial Intelligence)', c: 'Aplikasi Interaktif', d: 'Analisis Imej' } },
        { id: 'q2', text: 'Antara berikut, yang manakah contoh penggunaan AI dalam kehidupan harian?', answer: 'c',
          options: { a: 'Kalkulator biasa', b: 'Lampu suluh', c: 'Cadangan video di aplikasi penstriman', d: 'Jam dinding' } },
        { id: 'q3', text: 'Apakah langkah terbaik untuk meningkatkan keyakinan digital?', answer: 'c',
          options: { a: 'Mengelak semua teknologi baharu', b: 'Berkongsi kata laluan dengan rakan', c: 'Belajar dan mencuba alat digital secara berperingkat', d: 'Hanya bergantung kepada orang lain' } }
      ] },
      'modul-2': { title: 'Kuiz Modul 2', questions: [
        { id: 'q4', text: 'Pembelajaran mesin (machine learning) ialah...', answer: 'a',
          options: { a: 'Cabang AI di mana komputer belajar daripada data', b: 'Proses membaiki mesin rosak', c: 'Kursus menaip pantas', d: 'Perisian antivirus' } },
        { id: 'q5', text: 'Apakah yang diperlukan oleh model AI untuk belajar?', answer: 'b',
          options: { a: 'Elektrik sahaja', b: 'Data', c: 'Kertas', d: 'Tiada apa-apa' } }
      ] },
      'modul-3': { title: 'Kuiz Modul 3', questions: [
        { id: 'q6', text: 'Apakah "prompt" dalam konteks alat AI generatif?', answer: 'a',
          options: { a: 'Arahan atau soalan yang diberikan kepada AI', b: 'Jenis virus komputer', c: 'Butang untuk menutup aplikasi', d: 'Kabel sambungan internet' } },
        { id: 'q7', text: 'Bagaimanakah cara terbaik untuk mendapatkan jawapan yang lebih tepat daripada AI?', answer: 'b',
          options: { a: 'Menulis arahan yang ringkas tetapi kabur', b: 'Memberikan arahan yang jelas dan konteks yang mencukupi', c: 'Menggunakan huruf besar sahaja', d: 'Bertanya soalan yang sama berulang kali tanpa perubahan' } }
      ] },
      'modul-4': { title: 'Kuiz Modul 4', questions: [
        { id: 'q8', text: 'Bagaimanakah AI boleh membantu perniagaan mikro?', answer: 'b',
          options: { a: 'Menggantikan semua pelanggan', b: 'Menghasilkan kandungan pemasaran dan menjimatkan masa', c: 'Menghapuskan keperluan menyimpan rekod', d: 'Menjamin keuntungan setiap bulan' } }
      ] },
      'modul-5': { title: 'Kuiz Modul 5', questions: [
        { id: 'q9', text: 'Apakah yang perlu dilakukan sebelum berkongsi maklumat yang dihasilkan oleh AI?', answer: 'c',
          options: { a: 'Terus kongsi tanpa semakan', b: 'Padam semua sumber', c: 'Semak ketepatan maklumat tersebut', d: 'Tukar fon tulisan' } },
        { id: 'q10', text: 'Antara berikut, yang manakah amalan AI yang bertanggungjawab?', answer: 'c',
          options: { a: 'Memasukkan data peribadi orang lain tanpa izin', b: 'Menggunakan AI untuk menyebarkan berita palsu', c: 'Melindungi privasi dan menggunakan AI secara beretika', d: 'Mempercayai semua output AI sepenuhnya' } }
      ] }
    }
  };

  var ISSUER = 'NADI'; // PDF author only

  // Certificate template and field positions (mm on A4 landscape, font sizes in pt).
  // Keep in sync with content/certificate.php (template.png is a copy of
  // public/assets/certificate/template.png).
  var CERT = {
    template: 'assets/certificate/template.png',
    name: {
      patch: [104, 70, 88, 12],
      patch_color: [255, 255, 255],
      center_y: 76,
      max_width: 250,
      max_size: 26,
      min_size: 12,
      color: [11, 60, 110]
    },
    details: { x: 62, y: 155.5, line_height: 5, size: 8.5, color: [80, 80, 80] },
    date_label: 'Tarikh',
    code_label: 'No. Sijil'
  };
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
  function sectionLessons(key) {
    return LESSONS.filter(function (l) { return l.sectionKey === key; });
  }
  function sectionIndex(key) {
    for (var i = 0; i < COURSE.sections.length; i++) if (COURSE.sections[i].key === key) return i;
    return -1;
  }
  var QUIZ_LESSONS = LESSONS.filter(function (l) { return l.type === 'quiz'; });
  var LAST_QUIZ = QUIZ_LESSONS[QUIZ_LESSONS.length - 1];
  // Attempts saved before the module quizzes have no quiz key: they were the old Kuiz Akhir (all 10 questions).
  var LEGACY_QUIZ_KEY = 'kuiz-akhir';
  function quizOf(lesson) { return QUIZ.quizzes[lesson.quiz]; }
  function lessonLabel(l) {
    return l.type === 'quiz' ? 'KUIZ - ' + quizOf(l).questions.length + ' SOALAN' : 'PEMBENTANGAN';
  }
  // Passed quiz lessons (key -> true). A completion or a passed legacy attempt counts as all passed
  // (Course::effectivePassed in src/Course.php).
  function passedSet() {
    var legacy = !!state.completion || state.attempts.some(function (a) {
      return a.passed && (!a.quiz || a.quiz === LEGACY_QUIZ_KEY);
    });
    var out = {};
    QUIZ_LESSONS.forEach(function (l) {
      if (legacy || state.attempts.some(function (a) { return a.passed && a.quiz === l.key; })) out[l.key] = true;
    });
    return out;
  }
  // Completed lessons; a passed quiz counts as completed (as in public/course.php).
  function doneSet(passed) {
    var out = {};
    Object.keys(state.done).forEach(function (k) { if (state.done[k]) out[k] = true; });
    Object.keys(passed).forEach(function (k) { out[k] = true; });
    return out;
  }
  function firstUnpassedQuiz(passed) {
    for (var i = 0; i < QUIZ_LESSONS.length; i++) if (!passed[QUIZ_LESSONS[i].key]) return QUIZ_LESSONS[i];
    return null;
  }
  function allQuizzesPassed(passed) { return firstUnpassedQuiz(passed) === null; }
  // Why a lesson is locked ({ message, links }) or null when open. Same rules as Course::lockReason().
  function lockReason(lesson, done, passed) {
    if (!QUIZ_LESSONS.length) return null;
    var own = sectionIndex(lesson.sectionKey);
    var prevQuiz = null;
    QUIZ_LESSONS.forEach(function (q) { if (sectionIndex(q.sectionKey) < own) prevQuiz = q; });
    if (own > sectionIndex(LAST_QUIZ.sectionKey)) {
      var first = firstUnpassedQuiz(passed);
      return first ? { message: 'Penutup dibuka selepas anda lulus kesemua kuiz modul (Modul 1 hingga Modul 5).', links: [first] } : null;
    }
    if (prevQuiz && !passed[prevQuiz.key]) {
      return { message: 'Modul ini dikunci. Sila lulus ' + prevQuiz.title + ' terlebih dahulu.', links: [prevQuiz] };
    }
    if (lesson.type === 'quiz') {
      var missing = sectionLessons(lesson.sectionKey).filter(function (l) { return l.type !== 'quiz' && !done[l.key]; });
      if (missing.length) {
        return {
          message: 'Sila lengkapkan semua pelajaran dalam ' + COURSE.sections[own].title + ' sebelum menjawab kuiz ini.',
          links: missing
        };
      }
    }
    return null;
  }
  function currentLock(lesson) {
    var passed = passedSet();
    return lockReason(lesson, doneSet(passed), passed);
  }

  // ---- State ----
  // Same rules as the PHP portal: by default the learner is kept only for this browser session
  // (sessionStorage) and forgotten after IDLE_MS without activity. Only when "Ingat saya" was ticked
  // at registration (own device) is the state kept in localStorage, for REMEMBER_MS from registration.
  // There is no server, so "Sambung Semula" (resume by e-mail on another device) is not possible here.
  var STORE_KEY = 'nadi-lms-draft-v1';
  var IDLE_MS = 30 * 60 * 1000; // LEARNER_IDLE_MINUTES in src/helpers.php
  var REMEMBER_MS = 30 * 86400 * 1000; // LEARNER_COOKIE_DAYS in src/helpers.php
  function emptyState() {
    return { learner: null, done: {}, attempts: [], completion: null, remember: false, rememberUntil: 0, lastActive: 0 };
  }
  function storage(remember) {
    try { return remember ? window.localStorage : window.sessionStorage; } catch (e) { return null; }
  }
  function read(store) {
    try {
      var raw = store ? store.getItem(STORE_KEY) : null;
      var s = raw ? JSON.parse(raw) : null;
      return s && typeof s === 'object' ? s : null;
    } catch (e) {
      return null;
    }
  }
  function load() {
    var s = read(storage(false));
    var remember = false;
    if (!s) {
      var local = storage(true);
      s = read(local);
      remember = true;
      // Drop localStorage state saved before "Ingat saya" existed (no remember flag), and remembered
      // state past its 30 days, so a shared computer does not keep an earlier learner.
      if (s && (s.remember !== true || typeof s.rememberUntil !== 'number' || Date.now() > s.rememberUntil)) {
        try { local.removeItem(STORE_KEY); } catch (e) { /* ignore */ }
        s = null;
      }
    }
    if (!s) return emptyState();
    return {
      learner: s.learner || null,
      done: s.done || {},
      attempts: Array.isArray(s.attempts) ? s.attempts : [],
      completion: s.completion || null,
      remember: remember,
      rememberUntil: remember ? s.rememberUntil : 0,
      lastActive: typeof s.lastActive === 'number' ? s.lastActive : 0
    };
  }
  var state = load();
  function save() {
    state.lastActive = Date.now();
    var keep = storage(state.remember);
    var other = storage(!state.remember);
    try { if (keep) keep.setItem(STORE_KEY, JSON.stringify(state)); } catch (e) { /* private mode: keep in memory */ }
    try { if (other) other.removeItem(STORE_KEY); } catch (e) { /* ignore */ }
  }
  function resetState() {
    state = emptyState();
    [storage(true), storage(false)].forEach(function (store) {
      try { if (store) store.removeItem(STORE_KEY); } catch (e) { /* ignore */ }
    });
  }
  // Forget a session-only learner who has been idle too long, or a remembered learner after 30 days.
  // Returns true when that happened.
  function expireIfIdle() {
    if (!state.learner) return false;
    var expired = state.remember ? Date.now() > state.rememberUntil : Date.now() - state.lastActive > IDLE_MS;
    if (expired) {
      resetState();
      return true;
    }
    return false;
  }
  // Activity without a route change (slide navigation, answering the quiz) also keeps the learner
  // active, like the keep-alive in public/assets/js/course.js.
  function touch() {
    if (!state.learner || Date.now() - state.lastActive < 60 * 1000) return;
    if (expireIfIdle()) { go('#/daftar'); return; }
    save();
  }
  ['click', 'keydown', 'pointerdown', 'touchstart', 'scroll', 'wheel', 'change'].forEach(function (type) {
    document.addEventListener(type, touch, { passive: true, capture: true });
  });
  // The real portal lets a learner continue on any device by entering their e-mail ("Sambung Semula").
  // This draft has no server, so it says so instead of pretending.
  var RESUME_DRAFT_NOTE = '"Sambung Semula" dengan e-mel hanya berfungsi dalam portal sebenar. ' +
    'Draf ini tiada pelayan: kemajuan anda disimpan dalam pelayar ini sahaja dan tidak boleh disambung ' +
    'pada peranti atau pelayar lain.';
  var justSignedOut = false;
  function signOut() {
    resetState();
    justSignedOut = true;
    go('#/');
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
    if (!expireIfIdle() && state.learner) save();
    if (r.path === '/daftar') return renderRegister();
    if (r.path === '/kursus') {
      if (!state.learner) return go('#/daftar');
      return renderCourse(r.params);
    }
    if (r.path === '/sijil') {
      if (!state.learner) return go('#/daftar');
      if (!state.completion) return go(courseHref((firstUnpassedQuiz(passedSet()) || LAST_QUIZ).key));
      return renderCertificate();
    }
    return renderLanding();
  }

  // Inline SVG icons (decorative). Same paths and markup as icon() in views/icons.php.
  var ICONS = {
    alert: '<circle cx="12" cy="12" r="9.5"/><path d="M12 7.5v5.5M12 16.5h.01"/>',
    arrow: '<path d="M5 12h14M13 6l6 6-6 6"/>',
    award: '<circle cx="12" cy="9" r="6"/><path d="m8.5 13.8-1.5 7.7 5-3 5 3-1.5-7.7"/>',
    back: '<path d="M19 12H5M11 6l-6 6 6 6"/>',
    book: '<path d="M2 5a2 2 0 0 1 2-2h5a3 3 0 0 1 3 3v15a2 2 0 0 0-2-2H2z"/><path d="M22 5a2 2 0 0 0-2-2h-5a3 3 0 0 0-3 3v15a2 2 0 0 1 2-2h8z"/>',
    check: '<path d="M20 6 9 17l-5-5"/>',
    clipboard: '<rect x="5" y="4" width="14" height="18" rx="2"/><path d="M9 4V2.5h6V4"/><path d="m9 13 2 2 4-4"/>',
    download: '<path d="M12 3v12M7 10l5 5 5-5"/><path d="M5 21h14"/>',
    expand: '<path d="M8 3H5a2 2 0 0 0-2 2v3M21 8V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3M16 21h3a2 2 0 0 0 2-2v-3"/>',
    flag: '<path d="M5 21V4"/><path d="M5 4h12l-2.5 4.5L17 13H5"/>',
    gift: '<rect x="3" y="8" width="18" height="5" rx="1"/><path d="M19 13v8H5v-8M12 8v13"/><path d="M12 8H8a2.5 2.5 0 1 1 0-5c3 0 4 5 4 5zM12 8h4a2.5 2.5 0 1 0 0-5c-3 0-4 5-4 5z"/>',
    home: '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/>',
    info: '<circle cx="12" cy="12" r="9.5"/><path d="M12 11v6M12 7.5h.01"/>',
    list: '<path d="M9 6h12M9 12h12M9 18h12"/><path d="M4 6h.01M4 12h.01M4 18h.01"/>',
    lock: '<rect x="4" y="11" width="16" height="10" rx="2.5"/><path d="M8 11V7.5a4 4 0 0 1 8 0V11"/><path d="M12 15v2"/>',
    mail: '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3.5 7 8.5 6 8.5-6"/>',
    next: '<path d="m9 18 6-6-6-6"/>',
    prev: '<path d="m15 18-6-6 6-6"/>',
    quiz: '<circle cx="12" cy="12" r="9.5"/><path d="M9.2 9.2a2.9 2.9 0 0 1 5.6 1c0 1.9-2.8 2.6-2.8 2.6"/><path d="M12 16.8h.01"/>',
    refresh: '<path d="M3 12a9 9 0 0 1 15.4-6.4L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15.4 6.4L3 16"/><path d="M3 21v-5h5"/>',
    search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/>',
    shield: '<path d="M12 3 4.5 6v5.5c0 4.6 3.2 8 7.5 9.5 4.3-1.5 7.5-4.9 7.5-9.5V6z"/><path d="m9 12 2.2 2.2L15.5 10"/>',
    slides: '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M12 16v4M8 20h8"/><path d="m10.5 8 4 2-4 2z"/>',
    user: '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M19 8v6M16 11h6"/>'
  };
  function icon(name, cls) {
    return '<svg class="icon' + (cls ? ' ' + cls : '') + '" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"' +
      ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' +
      (ICONS[name] || '') + '</svg>';
  }
  function initials() {
    if (!state.learner) return '';
    return (String(state.learner.first_name).trim().charAt(0) + String(state.learner.last_name).trim().charAt(0)).toUpperCase();
  }
  var BLOBS = '<img class="blob blob-tr" src="assets/img/blob-orange.svg" alt="" aria-hidden="true" width="487" height="354">' +
    '<img class="blob blob-bl" src="assets/img/blob-amber.svg" alt="" aria-hidden="true" width="425" height="309">';
  var CONFETTI = '<div class="confetti" aria-hidden="true">' + new Array(15).join('<i></i>') + '</div>';
  // "Dengan kerjasama" partner lockup (views/partners.php).
  function partners() {
    return '<div class="partner-lockup"><span class="partner-label">Dengan kerjasama</span>' +
      '<img class="partner-logo" src="assets/img/logo-aws.webp" alt="AWS" width="50" height="32">' +
      '<img class="partner-logo" src="assets/img/logo-pepperlabs.webp" alt="Pepper Labs" width="89" height="32"></div>';
  }
  function siteHeader() {
    return '<header class="site-header"><div class="site-header-inner">' +
      '<a class="site-logo" href="#/"><img src="assets/img/logo.svg" alt="NADI" width="108" height="36"></a>' +
      partners() + '</div></header>';
  }
  // Footer of views/layout.php. The draft has no verification page, so it has no verify link.
  function siteFooter() {
    return '<footer class="site-footer"><div class="container site-footer-inner">' + partners() +
      '<div class="site-footer-meta"><p class="site-footer-name">NADI LMS</p>' +
      '<p class="small">Kursus AI percuma untuk komuniti.</p></div></div></footer>';
  }
  function setPage(title, html) {
    document.title = (title ? title + ' | ' : '') + 'NADI LMS (Draf)';
    app.innerHTML = html;
    window.scrollTo(0, 0);
  }

  // ---- Landing ----
  // Same markup as views/landing.php. The draft has no "Sambung Semula" form: its card explains why.
  var STEPS = [
    ['user', 'Daftar', 'Isi maklumat asas anda. Tiada akaun atau kata laluan.'],
    ['book', 'Belajar', 'Ikuti slaid setiap modul mengikut kadar anda sendiri, di telefon atau komputer.'],
    ['clipboard', 'Kuiz', 'Lulus kuiz modul untuk membuka modul yang seterusnya.'],
    ['award', 'Sijil', 'Muat turun sijil penyelesaian (PDF) dengan nama anda.']
  ];
  function renderLanding() {
    var moduleCount = COURSE.sections.filter(function (s) { return s.key.indexOf('modul') === 0; }).length;
    var modules = COURSE.sections.map(function (s) {
      var isModule = s.key.indexOf('modul-') === 0;
      var isIntro = s.key === 'pengenalan';
      return '<li class="module-card' + (isModule ? '' : (isIntro ? ' is-intro' : ' is-outro')) + '">' +
        '<span class="module-badge" aria-hidden="true">' + (isModule ? e(s.key.slice(6)) : (isIntro ? 'Mula' : icon('flag'))) + '</span>' +
        '<div class="module-body"><h3 class="module-title">' + e(s.title) + '</h3><ul class="chip-list">' +
        s.lessons.map(function (l) {
          return '<li class="chip' + (l.type === 'quiz' ? ' chip-quiz' : '') + '">' + icon(l.type === 'quiz' ? 'quiz' : 'slides') + e(lessonLabel(l)) + '</li>';
        }).join('') + '</ul></div></li>';
    }).join('');
    var steps = STEPS.map(function (s, i) {
      return '<li class="step"><span class="step-icon">' + icon(s[0]) + '<span class="step-num">' + (i + 1) + '</span></span>' +
        '<h3>' + e(s[1]) + '</h3><p>' + e(s[2]) + '</p></li>';
    }).join('');
    var actions;
    if (state.learner) {
      actions = '<div class="greeting-card"><span class="avatar" aria-hidden="true">' + e(initials()) + '</span>' +
        '<p>Selamat kembali, <strong>' + e(fullName()) + '</strong>.</p></div>' +
        '<div class="hero-actions"><a class="btn btn-primary btn-lg" href="#/kursus">Teruskan Kursus ' + icon('arrow') + '</a>' +
        '<button type="button" class="btn-link" id="reset-btn">Bukan anda? Mula semula</button></div>';
    } else {
      actions = (justSignedOut ? '<div class="alert alert-success" role="status">Anda telah keluar. Maklumat anda tidak lagi dipaparkan pada peranti ini.</div>' : '') +
        '<div class="hero-actions"><a class="btn btn-primary btn-lg" href="#/daftar">Daftar Sekarang ' + icon('arrow') + '</a></div>' +
        '<p class="hero-note">Tiada akaun atau kata laluan diperlukan. Hanya isi maklumat asas anda.</p>';
    }
    var resumeCard = state.learner ? '' :
      '<section class="section" id="sambung-semula"><div class="container"><div class="card resume-card">' +
      '<div class="resume-copy"><span class="resume-icon">' + icon('refresh') + '</span><h2>Sambung Semula</h2>' +
      '<p class="muted">Dalam portal sebenar, anda boleh menyambung kemajuan, kuiz dan sijil anda dengan e-mel anda sahaja.</p></div>' +
      '<div class="resume-body"><p class="alert alert-info">' + RESUME_DRAFT_NOTE + '</p></div>' +
      '</div></div></section>';
    setPage('', siteHeader() +
      '<main class="landing"><section class="hero">' + BLOBS +
      '<div class="container hero-grid"><div class="hero-copy">' +
      '<p class="eyebrow">Kursus Dalam Talian NADI</p>' +
      '<h1>' + e(COURSE.title) + '</h1>' +
      '<span class="dots" aria-hidden="true"></span>' +
      '<p class="lead">Pelajari asas kecerdasan buatan (AI) secara percuma mengikut kadar anda sendiri. ' +
      'Lengkapkan kelima-lima modul dan lulus kuiz di hujung setiap modul dengan markah sekurang-kurangnya ' + QUIZ.passMark +
      '% untuk muat turun sijil penyelesaian anda.</p>' + actions +
      '<ul class="stat-strip">' +
      '<li><span class="stat-num">' + moduleCount + '</span> <span class="stat-label">modul</span></li>' +
      '<li><span class="stat-num">' + QUIZ_LESSONS.length + '</span> <span class="stat-label">kuiz</span></li>' +
      '<li><span class="stat-num">' + QUIZ.passMark + '%</span> <span class="stat-label">markah lulus</span></li>' +
      '<li><span class="stat-num stat-icon">' + icon('award') + '</span> <span class="stat-label">sijil percuma</span></li>' +
      '</ul></div>' +
      '<div class="hero-art"><div class="hero-mascot-wrap"><img class="hero-mascot" src="assets/img/mascot-wave-640.webp"' +
      ' srcset="assets/img/mascot-wave-320.webp 320w, assets/img/mascot-wave-640.webp 640w"' +
      ' sizes="(min-width: 900px) 360px, 220px" width="640" height="589" alt="Maskot harimau robot NADI"></div>' +
      partners() + '</div></div></section>' +
      '<section class="section steps-section" aria-labelledby="cara-heading"><div class="container">' +
      '<div class="section-head"><h2 id="cara-heading">Cara ia berfungsi</h2><span class="dots" aria-hidden="true"></span></div>' +
      '<ol class="steps">' + steps + '</ol></div></section>' +
      resumeCard +
      '<section class="section modules-section" aria-labelledby="kandungan-heading"><div class="container">' +
      '<div class="section-head"><h2 id="kandungan-heading">Kandungan Kursus</h2><span class="dots" aria-hidden="true"></span></div>' +
      '<ol class="module-grid">' + modules + '</ol></div></section></main>' + siteFooter());
    var reset = document.getElementById('reset-btn');
    if (reset) reset.addEventListener('click', signOut);
    justSignedOut = false;
  }

  // ---- Registration ----
  var FIELDS = [
    ['first_name', 'Nama Pertama', 'text', 'maxlength="100" autocomplete="given-name"'],
    ['last_name', 'Nama Akhir', 'text', 'maxlength="100" autocomplete="family-name"'],
    ['age', 'Umur', 'number', 'min="5" max="120" step="1" inputmode="numeric"'],
    ['phone', 'Nombor Telefon', 'tel', 'maxlength="20" autocomplete="tel"'],
    ['email', 'E-mel', 'email', 'maxlength="190" autocomplete="email"'],
    ['address', 'Alamat', 'text', 'maxlength="255" autocomplete="street-address"'],
    ['occupation', 'Pekerjaan', 'text', 'maxlength="100" autocomplete="organization-title"']
  ];
  // Fields that share a row on wider screens (pairs are adjacent above, as in views/register.php).
  var HALF_FIELDS = { first_name: true, last_name: true, age: true, phone: true };

  // Navy brand panel beside the form (views/auth-brand.php). The draft cannot resume by e-mail,
  // so its second point says what it does instead.
  function authBrand() {
    return '<aside class="auth-brand">' + BLOBS + '<div class="auth-brand-inner">' +
      '<p class="auth-kicker">Kursus Dalam Talian NADI</p>' +
      '<p class="auth-brand-title">Belajar AI, langkah demi langkah</p><ul class="auth-points">' +
      '<li><span class="auth-point-icon">' + icon('gift') + '</span><span><strong>Percuma</strong> Semua modul, kuiz dan sijil.</span></li>' +
      '<li><span class="auth-point-icon">' + icon('mail') + '</span><span><strong>Tiada kata laluan</strong> Hanya isi maklumat asas anda.</span></li>' +
      '<li><span class="auth-point-icon">' + icon('award') + '</span><span><strong>Sijil digital</strong> Muat turun PDF selepas lulus kesemua kuiz.</span></li>' +
      '</ul><img class="auth-mascot" src="assets/img/mascot-wave-320.webp" alt="" width="320" height="294"></div></aside>';
  }

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

  function renderRegister(old, errors, remember) {
    old = old || {};
    errors = errors || {};
    var hasErrors = Object.keys(errors).length > 0;
    var fields = FIELDS.map(function (f) {
      var name = f[0];
      return '<div class="field' + (HALF_FIELDS[name] ? ' field-half' : '') + (errors[name] ? ' has-error' : '') + '">' +
        '<label for="f-' + name + '">' + e(f[1]) + ' <span class="req">*</span></label>' +
        '<input id="f-' + name + '" name="' + name + '" type="' + f[2] + '" value="' + e(old[name]) + '" required ' + f[3] + '>' +
        (errors[name] ? '<p class="field-error">' + icon('alert') + e(errors[name]) + '</p>' : '') +
        '</div>';
    }).join('');
    setPage('Pendaftaran', siteHeader() +
      '<main class="auth-shell"><div class="auth-grid">' + authBrand() +
      '<div class="card auth-card">' +
      '<h1>Pendaftaran Peserta</h1>' +
      '<p class="muted">Isi maklumat di bawah untuk mula belajar. Nama anda akan dicetak pada sijil seperti yang ditulis di sini.</p>' +
      '<div class="alert alert-info" id="resume-draft-note">' + RESUME_DRAFT_NOTE + '</div>' +
      (hasErrors ? '<div class="alert alert-error" role="alert">Sila betulkan ralat di bawah.</div>' : '') +
      '<form class="form" id="register-form" novalidate><div class="form-grid">' + fields + '</div>' +
      '<div class="field field-check"><label for="f-remember">' +
      '<input id="f-remember" name="remember" type="checkbox" value="1"' + (remember ? ' checked' : '') + '> ' +
      'Ingat saya pada peranti ini selama 30 hari</label>' +
      '<p class="muted small field-help">' + icon('shield') + 'Tandakan hanya jika ini telefon atau komputer anda sendiri. ' +
      'Di komputer awam atau dikongsi (contohnya di pusat komuniti atau kafe), biarkan kosong: ' +
      'tekan "Selesai / Keluar" sebelum meninggalkan komputer. Menutup pelayar sahaja tidak ' +
      'semestinya mengeluarkan anda. Anda juga akan dikeluarkan selepas 30 minit tanpa aktiviti.</p></div>' +
      '<button type="submit" class="btn btn-primary btn-lg btn-block">Hantar &amp; Mula Kursus ' + icon('arrow') + '</button>' +
      '</form></div></div></main>' + siteFooter());
    document.getElementById('register-form').addEventListener('submit', function (ev) {
      ev.preventDefault();
      var input = {};
      FIELDS.forEach(function (f) { input[f[0]] = ev.target.elements[f[0]].value; });
      var remember = ev.target.elements.remember.checked;
      var res = validateRegistration(input);
      if (Object.keys(res.errors).length > 0) {
        renderRegister(res.clean, res.errors, remember);
        var firstBad = app.querySelector('.has-error input');
        if (firstBad) firstBad.focus();
        return;
      }
      resetState();
      res.clean.age = parseInt(res.clean.age, 10);
      state.learner = res.clean;
      state.remember = remember;
      state.rememberUntil = remember ? Date.now() + REMEMBER_MS : 0;
      save();
      go('#/kursus');
    });
  }

  // ---- Course viewer ----
  function renderCourse(params) {
    var lesson = params.lesson ? lessonByKey(params.lesson) : null;
    if (params.lesson && !lesson) {
      // Styled like views/error.php (the portal answers 404 here).
      setPage('Ralat', siteHeader() + '<main class="container narrow page-pad"><div class="card error-card">' +
        '<div class="error-art" aria-hidden="true"><img class="error-blob" src="assets/img/blob-amber.svg" alt="" width="425" height="309">' +
        '<span class="error-code">404</span>' +
        '<img class="error-mascot" src="assets/img/mascot-wave-320.webp" alt="" width="320" height="294"></div>' +
        '<h1>Ralat</h1><p>Pelajaran tidak dijumpai.</p>' +
        '<p><a class="btn btn-primary btn-lg" href="#/kursus">' + icon('back') + 'Kembali ke kursus</a></p></div></main>' + siteFooter());
      return;
    }
    var passed = passedSet();
    var done = doneSet(passed);
    if (!lesson) {
      for (var i = 0; i < LESSONS.length && !lesson; i++) if (!done[LESSONS[i].key]) lesson = LESSONS[i];
      if (!lesson) lesson = LESSONS[0];
    }
    var slideCount = lesson.type === 'presentation' ? lesson.slides.length : 0;
    var slide = 1;
    if (slideCount > 0) {
      var req = parseInt(params.slide, 10);
      slide = Math.max(1, Math.min(slideCount, isNaN(req) ? 1 : req));
    }
    var doneCount = LESSONS.filter(function (l) { return done[l.key]; }).length;
    var percent = Math.round(doneCount * 100 / LESSONS.length);

    var sidebar = COURSE.sections.map(function (s) {
      var sLessons = sectionLessons(s.key);
      var sDone = sLessons.filter(function (l) { return done[l.key]; }).length;
      var sLocked = sLessons.length > 0 && lockReason(sLessons[0], done, passed) !== null;
      var sComplete = sLessons.length > 0 && sDone === sLessons.length;
      var rows = sLessons.map(function (l) {
        var isDone = !!done[l.key];
        var isCurrent = l.key === lesson.key;
        var isLocked = lockReason(l, done, passed) !== null;
        var isPassed = l.type === 'quiz' && !!passed[l.key];
        return '<li class="lesson-row' + (isCurrent ? ' is-current' : '') + (isDone ? ' is-done' : '') + (isLocked ? ' is-locked' : '') +
          '" data-title="' + e(l.title) + '">' +
          '<a href="' + courseHref(l.key) + '"' + (isCurrent ? ' aria-current="page"' : '') + '>' +
          '<span class="lesson-tick" aria-hidden="true">' + (isDone ? icon('check') : (isLocked ? icon('lock') : '')) + '</span>' +
          '<span class="lesson-meta"><span class="lesson-title">' + e(l.title) + '</span>' +
          '<span class="lesson-tags"><span class="lesson-label">' + icon(l.type === 'quiz' ? 'quiz' : 'slides') + e(lessonLabel(l)) + '</span>' +
          (l.type === 'quiz' ? '<span class="lesson-status' + (isPassed ? ' is-pass' : '') + '">' + (isPassed ? 'LULUS' : 'BELUM LULUS') + '</span>' : '') +
          '</span></span>' +
          (isDone ? '<span class="sr-only">(selesai)</span>' : '') +
          (isLocked ? '<span class="sr-only">(dikunci)</span>' : '') + '</a></li>';
      }).join('');
      return '<details class="sidebar-section' + (sComplete ? ' is-complete' : '') + (sLocked ? ' is-section-locked' : '') +
        '" data-section="' + e(s.key) + '"' + (s.key === lesson.sectionKey ? ' open' : '') + '>' +
        '<summary><span class="section-title">' + e(s.title) +
        (sLocked ? '<span class="section-lock" aria-hidden="true">' + icon('lock') + '</span><span class="sr-only">(dikunci)</span>' : '') + '</span>' +
        '<span class="section-count">' + sDone + '/' + s.lessons.length + '</span>' +
        '<span class="section-chevron" aria-hidden="true"></span></summary>' +
        '<ul class="lesson-list">' + rows + '</ul></details>';
    }).join('');

    var lock = lockReason(lesson, done, passed);
    var main = lock ? lockHtml(lesson, lock)
      : (lesson.type === 'presentation' ? presentationHtml(lesson, slide, slideCount) : quizHtml(lesson, params, passed));

    document.body.className = 'course-page';
    setPage(lesson.title,
      '<header class="topbar"><div class="topbar-left">' +
      '<button type="button" class="sidebar-toggle" id="sidebar-toggle" aria-controls="course-sidebar" aria-expanded="false">' +
      icon('list') + '<span class="sidebar-toggle-text">Kandungan</span><span class="sidebar-toggle-pct">' + percent + '%</span></button>' +
      '<a class="topbar-home" href="#/">' + icon('home', 'topbar-home-icon') + '<span class="topbar-home-text">&larr; Pergi ke Laman Utama</span></a></div>' +
      '<a class="topbar-logo" href="#/"><img src="assets/img/logo.svg" alt="NADI" width="96" height="32"></a>' +
      '<div class="topbar-right"><span class="avatar avatar-sm" aria-hidden="true">' + e(initials()) + '</span>' +
      '<span class="topbar-user">' + e(fullName()) + '</span>' +
      '<button type="button" class="btn btn-outline btn-sm" id="topbar-signout-btn">Selesai / Keluar</button></div></header>' +
      '<div class="course-layout"><aside class="sidebar" id="course-sidebar" aria-label="Kandungan kursus">' +
      '<h2 class="sidebar-title">' + e(COURSE.title) + '</h2>' +
      '<div class="progress' + (percent >= 100 ? ' is-complete' : '') + '"><div class="progress-text">' + percent + '% selesai</div>' +
      '<div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + percent + '">' +
      '<span style="width: ' + percent + '%"></span></div></div>' +
      '<div class="search-field">' + icon('search', 'search-icon') +
      '<input type="search" id="lesson-search" class="sidebar-search" placeholder="Cari mengikut tajuk pelajaran" aria-label="Cari mengikut tajuk pelajaran"></div>' +
      '<nav class="sidebar-sections">' + sidebar + '</nav></aside>' +
      '<button type="button" class="sidebar-backdrop" aria-label="Tutup kandungan kursus" tabindex="-1"></button>' +
      '<main class="content">' + main + '</main></div>');

    document.getElementById('topbar-signout-btn').addEventListener('click', signOut);
    bindSearch();
    bindDrawer();
    if (lock) return;
    if (lesson.type === 'presentation') bindViewer(lesson, slide, slideCount);
    else bindQuiz(lesson);
  }

  // A locked lesson (the portal answers 403 with this card).
  // Lesson title with its section title as eyebrow (views/course.php .lesson-heading).
  function lessonHeading(lesson) {
    return '<div class="lesson-heading"><p class="lesson-eyebrow">' + e(COURSE.sections[sectionIndex(lesson.sectionKey)].title) + '</p>' +
      '<h1>' + e(lesson.title) + '</h1></div>';
  }
  function lessonLabelHtml(lesson) {
    return '<span class="lesson-label">' + icon(lesson.type === 'quiz' ? 'quiz' : 'slides') + e(lessonLabel(lesson)) + '</span>';
  }

  function lockHtml(lesson, lock) {
    return '<div class="lesson-header">' + lessonHeading(lesson) + lessonLabelHtml(lesson) + '</div>' +
      '<div class="card quiz-gate lock-card" id="lock-card">' +
      '<div class="lock-art" aria-hidden="true"><img class="lock-blob" src="assets/img/blob-amber.svg" alt="" width="425" height="309">' +
      '<span class="lock-icon">' + icon('lock') + '</span>' +
      '<img class="lock-mascot" src="assets/img/mascot-wave-320.webp" alt="" width="320" height="294"></div>' +
      '<div class="lock-body"><h2 class="lock-title">Belum dibuka</h2>' +
      '<p class="alert alert-warning">' + e(lock.message) + '</p><ul class="lock-links">' +
      lock.links.map(function (m) {
        return '<li><a class="lock-link" href="' + courseHref(m.key) + '"><span>' + e(m.title) + '</span>' + icon('arrow') + '</a></li>';
      }).join('') +
      '</ul></div></div>';
  }

  function presentationHtml(lesson, slide, slideCount) {
    var slides = lesson.slides.map(function (s, i) {
      var n = i + 1;
      return '<article class="slide" data-slide="' + n + '"' + (n === slide ? '' : ' hidden') + '>' +
        (s.image
          ? '<img src="' + e(s.image) + '" alt="' + e(s.title) + '"' + (n === slide ? '' : ' loading="lazy"') + '>'
          : '<h2>' + e(s.title) + '</h2><p>' + e(s.body) + '</p>') +
        '</article>';
    }).join('');
    return '<div class="lesson-header">' + lessonHeading(lesson) +
      '<button type="button" class="btn btn-outline btn-fullscreen" id="fullscreen-btn">' + icon('expand') + 'Skrin Penuh</button></div>' +
      '<div class="viewer" id="viewer" style="--slide-progress: ' + (Math.round(slide * 10000 / slideCount) / 100) + '%">' +
      '<div class="slide-progress" aria-hidden="true"><span></span></div>' +
      '<div class="slides">' + slides + '</div>' +
      '<div class="viewer-bar"><div class="viewer-nav">' +
      '<a class="btn btn-nav" id="prev-btn">' + icon('prev') + 'SEBELUM</a>' +
      '<span class="slide-counter" id="slide-counter">' + slide + '/' + slideCount + '</span>' +
      '<a class="btn btn-nav" id="next-btn">SETERUSNYA' + icon('next') + '</a></div>' +
      '<form class="complete-form" id="complete-form">' +
      '<button type="submit" class="btn btn-primary" id="complete-btn">LENGKAP &amp; TERUSKAN</button>' +
      '<span class="complete-hint">Sampai ke slaid terakhir untuk teruskan</span></form>' +
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
      viewer.style.setProperty('--slide-progress', (n * 100 / count) + '%');
      if (window.history && window.history.replaceState) window.history.replaceState(null, '', courseHref(lesson.key, n));
    }
    prevBtn.addEventListener('click', function (ev) { ev.preventDefault(); show(current - 1); });
    nextBtn.addEventListener('click', function (ev) { ev.preventDefault(); show(current + 1); });
    document.getElementById('complete-form').addEventListener('submit', function (ev) {
      ev.preventDefault();
      if (current !== count) return;
      if (currentLock(lesson)) return go(courseHref(lesson.key));
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

  // Below 960px the sidebar is an off-canvas drawer (CSS, only under html.js). Same behaviour as the
  // Drawer block in public/assets/js/course.js. render() resets body.className, so a route change closes it.
  var closeDrawer = null; // set while a course page with a drawer is shown (Escape closes it)
  function bindDrawer() {
    var toggle = document.getElementById('sidebar-toggle');
    var drawer = document.getElementById('course-sidebar');
    closeDrawer = null;
    if (!toggle || !drawer) return;
    var backdrop = document.querySelector('.sidebar-backdrop');
    var search = document.getElementById('lesson-search');
    function isOpen() { return document.body.classList.contains('sidebar-open'); }
    function setDrawer(open) {
      if (open === isOpen()) return;
      document.body.classList.toggle('sidebar-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) {
        if (search) search.focus();
      } else {
        toggle.focus();
      }
    }
    toggle.addEventListener('click', function () { setDrawer(!isOpen()); });
    if (backdrop) backdrop.addEventListener('click', function () { setDrawer(false); });
    drawer.addEventListener('click', function (ev) {
      var link = ev.target && ev.target.closest ? ev.target.closest('.lesson-row a') : null;
      if (link && isOpen()) {
        document.body.classList.remove('sidebar-open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
    closeDrawer = function () { if (isOpen()) setDrawer(false); };
  }
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && closeDrawer && document.getElementById('course-sidebar')) closeDrawer();
  });

  // ---- Module quizzes ----
  function formatPercent(p) { return String(Math.round(p * 100) / 100); }

  function quizHtml(lesson, params, passed) {
    var quiz = quizOf(lesson);
    var html = '<div class="lesson-header">' + lessonHeading(lesson) + lessonLabelHtml(lesson) + '</div>';
    var mine = state.attempts.filter(function (a) { return a.quiz === lesson.key; });
    if (state.completion) {
      html += '<div class="alert alert-success">Kursus ini telah selesai. <a href="#/sijil">Lihat sijil anda</a>.</div>';
    }
    var attempt = null;
    if (params.attempt) {
      var id = parseInt(params.attempt, 10);
      attempt = mine.filter(function (a) { return a.id === id; })[0] || null;
    }
    if (attempt) {
      var allPassedNow = allQuizzesPassed(passed);
      var isFinal = attempt.passed && !state.completion && allPassedNow;
      var pct = formatPercent(attempt.percent);
      html += '<div class="card quiz-result ' + (attempt.passed ? 'is-pass' : 'is-fail') + (isFinal ? ' is-final' : '') + '">' +
        (attempt.passed ? CONFETTI : '') +
        '<div class="result-hero"><div class="score-ring" style="--score: ' + pct + '" aria-hidden="true"><span>' + Math.round(attempt.percent) + '%</span></div>' +
        (attempt.passed
          ? '<img class="result-mascot" src="assets/img/mascot-cheer.webp" alt="" width="315" height="288">'
          : '<img class="result-mascot" src="assets/img/mascot-wave-320.webp" alt="" width="320" height="294">') + '</div>' +
        '<p class="quiz-score">Markah anda: ' + attempt.correct + '/' + attempt.total + ' (' + pct + '%)</p>' +
        '<p class="quiz-status">' + (attempt.passed ? 'LULUS' : 'GAGAL') + '</p>';
      if (!attempt.passed) {
        html += '<p>Markah lulus ialah ' + QUIZ.passMark + '%. Anda boleh mencuba lagi tanpa had.</p>' +
          '<a class="btn btn-primary btn-lg" href="' + courseHref(lesson.key) + '">' + icon('refresh') + 'Cuba Lagi</a>';
      } else if (state.completion) {
        html += '<a class="btn btn-primary btn-lg" href="#/sijil">' + icon('award') + 'Lihat Sijil</a>';
      } else if (allPassedNow) {
        html += '<p>Tahniah! Tekan butang di bawah untuk menghantar penyelesaian kursus dan mendapatkan sijil anda.</p>' +
          '<form id="finish-form"><button type="submit" class="btn btn-primary btn-lg btn-block">LENGKAP &amp; TERUSKAN</button></form>';
      } else {
        var next = nextLesson(lesson.key);
        html += '<p>Tahniah! Anda lulus kuiz ini.</p>' +
          (next ? '<a class="btn btn-primary btn-lg" id="next-lesson-btn" href="' + courseHref(next.key) + '">Teruskan ke ' + e(next.title) + '</a>' : '');
      }
      return html + '</div>';
    }
    var qs = quiz.questions.map(function (q, qi) {
      var opts = Object.keys(q.options).map(function (k) {
        return '<label class="quiz-option"><input type="radio" name="' + e(q.id) + '" value="' + e(k) + '" required>' +
          '<span>' + e(q.options[k]) + '</span></label>';
      }).join('');
      return '<fieldset class="quiz-question"><legend><span class="q-num">' + (qi + 1) + '<span class="q-dot">.</span></span> ' + e(q.text) + '</legend>' +
        '<div class="quiz-options">' + opts + '</div></fieldset>';
    }).join('');
    return html + '<form class="card quiz-form" id="quiz-form" data-quiz="' + e(lesson.key) + '">' +
      '<p class="quiz-meta">Markah lulus: ' + QUIZ.passMark + '% &middot; Percubaan ke-' + (mine.length + 1) + '</p>' +
      qs + '<button type="submit" class="btn btn-primary btn-lg quiz-submit">Hantar Jawapan ' + icon('arrow') + '</button></form>';
  }

  function bindQuiz(lesson) {
    var form = document.getElementById('quiz-form');
    if (form) {
      form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        if (currentLock(lesson)) return go(courseHref(lesson.key));
        var questions = quizOf(lesson).questions;
        var correct = 0;
        questions.forEach(function (q) {
          var picked = form.querySelector('input[name="' + q.id + '"]:checked');
          if (picked && picked.value === q.answer) correct++;
        });
        var total = questions.length;
        var percent = Math.round(correct * 10000 / total) / 100;
        var id = state.attempts.length + 1;
        var passedNow = percent >= QUIZ.passMark;
        state.attempts.push({ id: id, quiz: lesson.key, correct: correct, total: total, percent: percent, passed: passedNow });
        if (passedNow) state.done[lesson.key] = true;
        save();
        go(courseHref(lesson.key) + '&attempt=' + id);
      });
    }
    var finish = document.getElementById('finish-form');
    if (finish) {
      finish.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var first = firstUnpassedQuiz(passedSet());
        if (first) return go(courseHref(first.key));
        if (!state.completion) state.completion = { code: randomCode(), completedAt: todayIso() };
        save();
        go('#/sijil');
      });
    }
  }

  // ---- Certificate ----
  function renderCertificate() {
    var c = state.completion;
    setPage('Sijil', siteHeader() +
      '<main class="cert-page"><section class="celebrate">' + BLOBS + CONFETTI +
      '<div class="container celebrate-inner">' +
      '<img class="celebrate-mascot" src="assets/img/mascot-cheer.webp" alt="" width="315" height="288">' +
      '<div class="celebrate-copy"><p class="eyebrow">Kursus selesai</p><h1>Tahniah!</h1>' +
      '<p class="lead">Anda telah berjaya menamatkan kursus ini. Sijil anda sudah sedia untuk dimuat turun.</p></div></div></section>' +
      '<div class="container cert-container"><div class="card certificate-card">' +
      '<div class="ticket"><p class="ticket-kicker">' + icon('award') + 'Sijil Penyelesaian</p>' +
      '<dl class="certificate-details">' +
      '<dt>Nama</dt><dd>' + e(fullName()) + '</dd>' +
      '<dt>Kursus</dt><dd>' + e(COURSE.title) + '</dd>' +
      '<dt>Tarikh</dt><dd>' + e(formatDateMs(c.completedAt)) + '</dd>' +
      '<dt>No. Sijil</dt><dd><span class="cert-code">' + e(c.code) + '</span></dd></dl></div>' +
      '<div class="certificate-actions">' +
      '<button type="button" class="btn btn-primary btn-lg" id="download-btn">' + icon('download') + 'Muat Turun Sijil (PDF)</button>' +
      '<button type="button" class="btn btn-outline btn-lg" id="signout-btn">Selesai / Keluar</button></div>' +
      '<p class="alert alert-info">Selepas memuat turun sijil, tekan "Selesai / Keluar" supaya nama, kemajuan dan sijil anda ' +
      'tidak dipaparkan kepada pengguna seterusnya pada peranti ini. Di komputer awam, simpan fail sijil ' +
      'ke telefon atau e-mel anda dan padamkan salinan daripada komputer tersebut.</p>' +
      '<p class="muted small">' + icon('shield') + 'Ini sijil contoh daripada draf pratonton. Pengesahan sijil hanya tersedia dalam portal sebenar.</p>' +
      '<p><a class="back-link" href="' + courseHref(LAST_QUIZ.key) + '">' + icon('back') + 'Kembali ke kursus</a></p>' +
      '</div></div></main>' + siteFooter());
    document.getElementById('signout-btn').addEventListener('click', signOut);
    document.getElementById('download-btn').addEventListener('click', function () {
      if (expireIfIdle() || !state.completion) return go('#/daftar');
      save();
      var data = {
        first_name: state.learner.first_name,
        last_name: state.learner.last_name,
        course_title: COURSE.title,
        date_text: formatDateMs(c.completedAt),
        code: c.code,
        issuer: ISSUER
      };
      certificatePdf(data).then(function (blob) {
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'sijil-' + c.code + '.pdf';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(function () { URL.revokeObjectURL(url); }, 10000);
      }).catch(function () {
        if (document.getElementById('download-error')) return;
        var p = document.createElement('p');
        p.className = 'alert alert-error';
        p.id = 'download-error';
        p.setAttribute('role', 'alert');
        p.textContent = 'Sijil tidak dapat dijana. Sila cuba lagi.';
        var actions = document.querySelector('.certificate-card .certificate-actions');
        if (actions) actions.parentNode.insertBefore(p, actions);
      });
    });
  }

  // Load one image; resolves with the loaded <img>.
  function loadImage(src) {
    return new Promise(function (resolve, reject) {
      var img = new Image();
      img.onload = function () { resolve(img); };
      img.onerror = function () { reject(new Error('Cannot load ' + src)); };
      img.src = src;
    });
  }

  // Same algorithm as certificate_name_layout() in src/CertificatePdf.php.
  // measure(text, sizePt) returns the width in mm. Returns { size: pt, lines: [..] }.
  function nameLayout(measure, name, cfg) {
    var size;
    for (size = cfg.max_size; size >= cfg.min_size; size -= 1) {
      if (measure(name, size) <= cfg.max_width) return { size: size, lines: [name] };
    }
    var chars = Array.from(name);
    var mid = Math.floor(chars.length / 2);
    var split = null;
    chars.forEach(function (ch, i) {
      if (ch === ' ' && (split === null || Math.abs(i - mid) < Math.abs(split - mid))) split = i;
    });
    var lines = split !== null
      ? [chars.slice(0, split).join(''), chars.slice(split + 1).join('')]
      : [chars.slice(0, mid).join(''), chars.slice(mid).join('')];
    size = cfg.min_size;
    while (size > 4 && Math.max(measure(lines[0], size), measure(lines[1], size)) > cfg.max_width) size -= 0.5;
    return { size: size, lines: lines };
  }

  // Draw the certificate on a canvas, using the same layout (in mm) as src/CertificatePdf.php:
  // the template fills the page, the placeholder is covered, then name, date and code are printed.
  // Returns a Promise that resolves with the canvas once the template is drawn.
  function drawCertificate(d) {
    return loadImage(CERT.template).then(function (tpl) {
      var W = 297, H = 210, S = 8; // A4 landscape, 8 px per mm
      var canvas = document.createElement('canvas');
      canvas.width = W * S;
      canvas.height = H * S;
      var g = canvas.getContext('2d');
      var PT = 25.4 / 72; // mm per point
      var sans = 'Helvetica, Arial, sans-serif';
      function rgb(c) { return 'rgb(' + c.join(',') + ')'; }
      g.fillStyle = '#ffffff';
      g.fillRect(0, 0, canvas.width, canvas.height);
      g.imageSmoothingQuality = 'high';
      g.drawImage(tpl, 0, 0, W * S, H * S);

      var n = CERT.name;
      g.fillStyle = rgb(n.patch_color);
      g.fillRect(n.patch[0] * S, n.patch[1] * S, n.patch[2] * S, n.patch[3] * S);

      g.textBaseline = 'middle';
      function font(weight, sizePt) {
        g.font = weight + ' ' + (sizePt * PT * S) + 'px ' + sans;
      }
      // One line of text: centred on the page, or left-aligned at x (mm) when x is given.
      function cell(y, h, text, weight, sizePt, color, x) {
        g.fillStyle = color;
        font(weight, sizePt);
        g.textAlign = x === undefined ? 'center' : 'left';
        g.fillText(text, (x === undefined ? W / 2 : x) * S, (y + h / 2) * S);
      }

      var layout = nameLayout(function (t, sizePt) {
        font('bold', sizePt);
        return g.measureText(t).width / S;
      }, d.first_name + ' ' + d.last_name, n);
      var lineH = layout.size * 0.3528 * 1.15;
      var top = n.center_y - layout.lines.length * lineH / 2;
      layout.lines.forEach(function (line, i) {
        cell(top + i * lineH, lineH, line, 'bold', layout.size, rgb(n.color));
      });

      var t = CERT.details;
      cell(t.y, t.line_height, CERT.date_label + ': ' + d.date_text, 'normal', t.size, rgb(t.color), t.x);
      cell(t.y + t.line_height, t.line_height, CERT.code_label + ': ' + d.code, 'normal', t.size, rgb(t.color), t.x);
      cell(165.5, 5, 'DRAF / PRATONTON', 'normal', 8, 'rgb(150,150,150)', 62);
      return canvas;
    });
  }
  window.NADI_DRAFT = { drawCertificate: drawCertificate };

  // Build the certificate PDF. Returns a Promise that resolves with a Blob.
  function certificatePdf(d) {
    return drawCertificate(d).then(function (canvas) { return canvasPdf(canvas, d); });
  }

  // Wrap the canvas as a JPEG image in a one-page PDF (A4 landscape).
  function canvasPdf(canvas, d) {
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
