// Headless-browser check of the docs/ GitHub Pages draft, served under the /nadi/ subpath like
// https://raimi-akasyah.github.io/nadi/. Needs Node 22+ (global WebSocket) and Chrome or Edge.
// Run from the project folder: node tests/docs-check.mjs
// Fails on any console error, uncaught exception or failed request.
import { spawn } from 'node:child_process';
import { createServer } from 'node:http';
import { mkdtempSync, readFileSync, rmSync, statSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, extname, join, normalize, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const docsRoot = join(dirname(fileURLToPath(import.meta.url)), '..', 'docs');
const browsers = [
  process.env.CHROME_PATH,
  'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
  'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
  '/usr/bin/google-chrome',
  '/usr/bin/chromium',
].filter(Boolean);
const browserPath = browsers.find((p) => { try { return statSync(p).isFile(); } catch { return false; } });
if (!browserPath) {
  console.log('FAIL no Chrome or Edge found (set CHROME_PATH)');
  process.exit(1);
}

// Static server: /nadi/... -> docs/..., everything else 404.
const TYPES = { '.html': 'text/html; charset=utf-8', '.js': 'text/javascript; charset=utf-8', '.css': 'text/css; charset=utf-8', '.svg': 'image/svg+xml' };
const server = createServer((req, res) => {
  const path = decodeURIComponent(new URL(req.url, 'http://x').pathname);
  if (path === '/nadi') { res.writeHead(301, { Location: '/nadi/' }); return res.end(); }
  if (!path.startsWith('/nadi/')) { res.writeHead(404); return res.end('not found'); }
  let file = normalize(join(docsRoot, path.slice('/nadi/'.length) || 'index.html'));
  if (!file.startsWith(normalize(docsRoot) + sep) && file !== normalize(join(docsRoot, 'index.html'))) { res.writeHead(403); return res.end(); }
  try {
    if (statSync(file).isDirectory()) file = join(file, 'index.html');
    const body = readFileSync(file);
    res.writeHead(200, { 'Content-Type': TYPES[extname(file)] || 'application/octet-stream' });
    res.end(body);
  } catch {
    res.writeHead(404); res.end('not found');
  }
});
await new Promise((r) => server.listen(0, '127.0.0.1', r));
const base = `http://127.0.0.1:${server.address().port}/nadi/`;

const debugPort = 9300 + Math.floor(Math.random() * 600);
const profile = mkdtempSync(join(tmpdir(), 'nadi-docs-'));
const browser = spawn(browserPath, ['--headless=new', `--remote-debugging-port=${debugPort}`, `--user-data-dir=${profile}`,
  '--no-first-run', '--no-default-browser-check', 'about:blank'], { stdio: 'ignore' });
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

let failures = 0;
function step(label, ok, detail) {
  if (!ok) failures++;
  console.log((ok ? 'PASS ' : 'FAIL ') + label + (ok || !detail ? '' : ' (' + detail + ')'));
}

async function json(path, method = 'GET') {
  for (let i = 0; i < 50; i++) {
    try { return await (await fetch(`http://127.0.0.1:${debugPort}${path}`, { method })).json(); } catch { await sleep(200); }
  }
  throw new Error('browser did not start');
}

const consoleErrors = [];
async function openTab(url) {
  const t = await json('/json/new?about:blank', 'PUT');
  const ws = new WebSocket(t.webSocketDebuggerUrl);
  await new Promise((r) => ws.addEventListener('open', r, { once: true }));
  let id = 0;
  const pending = new Map();
  ws.addEventListener('message', (m) => {
    const d = JSON.parse(m.data);
    if (d.id && pending.has(d.id)) { pending.get(d.id)(d); pending.delete(d.id); return; }
    if (d.method === 'Runtime.exceptionThrown') consoleErrors.push('exception: ' + (d.params.exceptionDetails.exception?.description || d.params.exceptionDetails.text));
    if (d.method === 'Runtime.consoleAPICalled' && d.params.type === 'error') consoleErrors.push('console.error: ' + d.params.args.map((a) => a.value ?? a.description).join(' '));
    if (d.method === 'Log.entryAdded' && d.params.entry.level === 'error') consoleErrors.push('log: ' + d.params.entry.text + ' ' + (d.params.entry.url || ''));
  });
  const send = (method, params = {}) => new Promise((r) => { const n = ++id; pending.set(n, r); ws.send(JSON.stringify({ id: n, method, params })); });
  await send('Runtime.enable');
  await send('Log.enable');
  await send('Page.enable');
  await send('Page.navigate', { url });
  await sleep(800);
  const evaluate = async (expr) => {
    const res = await send('Runtime.evaluate', { expression: `(async () => { ${expr} })()`, awaitPromise: true, returnByValue: true });
    if (res.result.exceptionDetails) throw new Error(JSON.stringify(res.result.exceptionDetails));
    return res.result.result.value;
  };
  return { evaluate, close: async () => { ws.close(); await fetch(`http://127.0.0.1:${debugPort}/json/close/${t.id}`); } };
}

const KEY = 'nadi-lms-draft-v1';
const ANSWERS = { q1: 'b', q2: 'c', q3: 'c', q4: 'a', q5: 'b', q6: 'a', q7: 'b', q8: 'b', q9: 'c', q10: 'c' };
const helpers = `
  const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
  const text = () => document.getElementById('app').innerText;
  async function go(h) { location.hash = h; await sleep(150); }
  // Choose Negeri and Daerah like a user (change events), then the first centre, or Lain-lain with a typed name.
  async function pickNadi(f, st, district, otherName) {
    f.elements.state.value = st;
    f.elements.state.dispatchEvent(new Event('change'));
    f.elements.district.value = district;
    f.elements.district.dispatchEvent(new Event('change'));
    f.elements.nadi.value = otherName ? 'lain-lain' : f.elements.nadi.options[1].value;
    f.elements.nadi.dispatchEvent(new Event('change'));
    if (otherName) f.elements.nadi_other.value = otherName;
  }
  async function register(last, remember) {
    await go('#/daftar');
    const f = document.getElementById('register-form');
    const v = { full_name: '  Ujian   ' + last + ' ', age: '30', email: last.toLowerCase() + '@example.com', occupation: 'Pesara' };
    for (const k in v) f.elements[k].value = v[k];
    f.querySelector('input[name="gender"][value="Perempuan"]').checked = true;
    await pickNadi(f, 'Kelantan', 'Bachok');
    f.elements.remember.checked = remember;
    f.requestSubmit();
    await sleep(200);
  }
  const ANS = ${JSON.stringify(ANSWERS)};
  // Complete a slide lesson from its last slide; returns the hash after LENGKAP & TERUSKAN.
  async function completeLesson(key) {
    await go('#/kursus?lesson=' + key + '&slide=1');
    const last = document.getElementById('slide-counter').textContent.split('/')[1];
    await go('#/kursus?lesson=' + key + '&slide=' + last);
    document.getElementById('complete-form').requestSubmit();
    await sleep(150);
    return location.hash;
  }
  // Answer the quiz of lesson key (all right, or the first question wrong) and submit.
  async function answerQuiz(key, allRight) {
    await go('#/kursus?lesson=' + key);
    const qf = document.getElementById('quiz-form');
    if (!qf) return false;
    let first = true;
    for (const fs of qf.querySelectorAll('fieldset')) {
      const q = fs.querySelector('input').name;
      const v = allRight || !first ? ANS[q] : (ANS[q] === 'a' ? 'b' : 'a');
      qf.querySelector('input[name="' + q + '"][value="' + v + '"]').checked = true;
      first = false;
    }
    qf.requestSubmit();
    await sleep(200);
    return true;
  }
  async function passModule(n) {
    await completeLesson('modul-' + n + '-pengenalan');
    return answerQuiz('kuiz-modul-' + n, true);
  }
`;

try {
  await json('/json/version');
  const tab = await openTab(base);

  // Assets load under the /nadi/ subpath, and "Sambung Semula" is explained honestly.
  const h = await tab.evaluate(helpers + `
    const css = [...document.styleSheets].some((s) => (s.href || '').endsWith('/nadi/assets/css/app.css') && s.cssRules.length > 0);
    const landing = text();
    await go('#/daftar');
    const reg = text();
    return { css, path: location.pathname, landing, reg, hasForm: !!document.querySelector('form[action="resume.php"]') };
  `);
  step('draft loads under /nadi/ with its CSS and JS', h.path === '/nadi/' && h.css && h.landing.includes('Daftar Sekarang'), JSON.stringify(h).slice(0, 200));
  step('Sambung Semula on landing and registration says it only works in the real portal (no fake form)',
    h.landing.includes('Sambung Semula') && h.landing.includes('hanya berfungsi dalam portal sebenar')
    && h.reg.includes('hanya berfungsi dalam portal sebenar') && !h.hasForm);
  step('registration help text relies on Selesai / Keluar and the timeout, not on closing the browser',
    h.reg.includes('Menutup pelayar sahaja tidak semestinya mengeluarkan anda') && !h.reg.includes('apabila pelayar ditutup'));

  // Registration: Negeri -> Daerah -> Pusat NADI, Lain-lain fields, no phone or address.
  const reg = await tab.evaluate(helpers + `
    await go('#/daftar');
    const f = document.getElementById('register-form');
    const vals = (sel) => [...sel.options].map((o) => o.value).filter(Boolean);
    const out = { phone: !!f.elements.phone, address: !!f.elements.address,
      start: [f.elements.district.disabled, f.elements.nadi.disabled].join(),
      otherHidden: document.getElementById('f-nadi_other-field').hidden && document.getElementById('f-occupation_other-field').hidden };
    f.elements.state.value = 'Perlis';
    f.elements.state.dispatchEvent(new Event('change'));
    out.perlisDistricts = vals(f.elements.district).join();
    f.elements.district.value = 'Perlis';
    f.elements.district.dispatchEvent(new Event('change'));
    const perlis = vals(f.elements.nadi);
    out.perlisCentres = perlis.length - 1;
    out.lastIsOther = perlis[perlis.length - 1] === 'lain-lain';
    out.allPerlis = perlis.slice(0, -1).every((c) => window.NADI_CENTERS.some((r) => r[0] === c && r[3] === 'Perlis'));
    out.expectedPerlis = window.NADI_CENTERS.filter((r) => r[3] === 'Perlis').length;
    f.elements.nadi.value = 'lain-lain';
    f.elements.nadi.dispatchEvent(new Event('change'));
    out.otherShown = !document.getElementById('f-nadi_other-field').hidden && f.elements.nadi_other.required;
    f.elements.state.value = 'Selangor';
    f.elements.state.dispatchEvent(new Event('change'));
    out.reset = [f.elements.district.value, f.elements.nadi.value, f.elements.nadi.disabled, document.getElementById('f-nadi_other-field').hidden].join();
    f.elements.occupation.value = 'lain-lain';
    f.elements.occupation.dispatchEvent(new Event('change'));
    out.occOther = !document.getElementById('f-occupation_other-field').hidden;
    f.requestSubmit();
    await sleep(150);
    out.errors = [...document.querySelectorAll('.field-error')].map((p) => p.id).join();
    return out;
  `);
  step('registration: no phone or address; Daerah and Pusat NADI wait for the choice before them',
    !reg.phone && !reg.address && reg.start === 'true,true' && reg.otherHidden, JSON.stringify(reg));
  step('Negeri Perlis -> Daerah Perlis -> only the Perlis centres, then Lain-lain; Lain-lain shows the text field; a new Negeri resets both',
    reg.perlisDistricts === 'Perlis' && reg.perlisCentres === reg.expectedPerlis && reg.perlisCentres > 0 && reg.lastIsOther && reg.allPerlis
    && reg.otherShown && reg.reset === ',,true,true' && reg.occOther, JSON.stringify(reg));
  step('submitting the empty form marks name, age, gender, e-mail, Lain-lain occupation and district',
    reg.errors === 'e-full_name,e-age,e-gender,e-email,e-occupation_other,e-district', reg.errors);

  // Landing "Kandungan Kursus": E-Learning / Self-Learning tabs.
  const tabs = await tab.evaluate(helpers + `
    await go('#/');
    const vis = (id) => !document.getElementById(id).hidden;
    const out = { tabs: [...document.querySelectorAll('[role="tab"]')].map((t) => t.textContent.trim().replace(/\\s+/g, ' ')),
      first: [vis('panel-e-learning'), vis('panel-self-learning')] };
    document.getElementById('tab-self-learning').click();
    out.second = [vis('panel-e-learning'), vis('panel-self-learning'), document.getElementById('tab-self-learning').getAttribute('aria-selected')];
    out.selfText = document.getElementById('panel-self-learning').innerText;
    document.getElementById('tab-self-learning').focus();
    document.getElementById('tab-self-learning').dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowLeft', bubbles: true }));
    out.third = [vis('panel-e-learning'), document.activeElement.id];
    return out;
  `);
  const selfCards = await tab.evaluate(`
    return [...document.querySelectorAll('#panel-self-learning .module-card')].map((c) => c.className + ':' + c.querySelector('.module-badge').textContent.trim());
  `);
  step('Self-Learning module cards use the E-Learning style: Mula, amber "1" badge, Penutup flag',
    selfCards.join() === 'module-card is-intro:Mula,module-card:1,module-card is-outro:', JSON.stringify(selfCards));
  step('landing Kandungan Kursus has E-Learning / Self-Learning tabs that switch panels (click and arrow keys)',
    tabs.tabs.length === 2 && tabs.tabs[0].startsWith('E-Learning') && tabs.tabs[1].startsWith('Self-Learning')
    && tabs.first.join() === 'true,false' && tabs.second.join() === 'false,true,true'
    && tabs.selfText.includes('Pengenalan') && tabs.selfText.includes('VIDEO - 10:35') && tabs.selfText.includes('KUIZ - 10 SOALAN')
    && tabs.selfText.includes('Penutup')
    && tabs.third.join() === 'true,tab-e-learning', JSON.stringify(tabs));

  // Learner A on a shared computer: registers without "Ingat saya", completes the course, downloads, leaves.
  const a = await tab.evaluate(helpers + `
    await register('DrafA', false);
    const out = { hash: location.hash, inSession: !!sessionStorage.getItem('${KEY}'), inLocal: !!localStorage.getItem('${KEY}') };
    out.chooseSignout = !!document.getElementById('signout-btn');
    await go('#/kursus?track=e-learning');
    out.topbarSignout = !!document.getElementById('topbar-signout-btn');
    await go('#/kursus?lesson=kuiz-modul-1');
    out.quizLockedFirst = !!document.getElementById('lock-card') && !document.getElementById('quiz-form')
      && text().includes('Sila lengkapkan semua pelajaran dalam Modul 1');
    out.afterLesson1 = await completeLesson('modul-1-pengenalan');
    await answerQuiz('kuiz-modul-1', false);
    out.failText = text();
    await go('#/kursus?lesson=modul-2-pengenalan');
    const lock = document.getElementById('lock-card');
    out.modul2Locked = !!lock && lock.innerText.includes('Modul ini dikunci. Sila lulus Kuiz Modul 1 terlebih dahulu.')
      && !document.getElementById('viewer')
      && !!document.querySelector('.lesson-row.is-locked[data-title="Asas AI"]');
    await answerQuiz('kuiz-modul-1', true);
    out.passText = text();
    out.noFinishYet = !document.getElementById('finish-form');
    out.sidebarPass = (document.querySelector('.lesson-row[data-title="Kuiz Modul 1"] .lesson-status') || {}).textContent;
    const nextBtn = document.getElementById('next-lesson-btn');
    out.nextHref = nextBtn ? nextBtn.getAttribute('href') : '';
    out.modules = [];
    for (let n = 2; n <= 4; n++) out.modules.push(await passModule(n));
    await completeLesson('modul-5-pengenalan');
    await go('#/sijil');
    out.sijilBefore = location.hash;
    await go('#/kursus?lesson=penutup');
    out.penutupLocked = !!document.getElementById('lock-card');
    out.modules.push(await answerQuiz('kuiz-modul-5', true));
    document.getElementById('finish-form').requestSubmit();
    await sleep(200);
    out.certHash = location.hash;
    out.certShowsName = text().includes('Ujian DrafA');
    out.hasSignout = !!document.getElementById('signout-btn');
    document.getElementById('download-btn').click();
    await sleep(500);
    const RealImage = window.Image;
    window.Image = function () {
      const stub = {};
      Object.defineProperty(stub, 'src', { set() { setTimeout(() => stub.onerror && stub.onerror(), 0); } });
      return stub;
    };
    document.getElementById('download-btn').click();
    await sleep(300);
    window.Image = RealImage;
    const err = document.getElementById('download-error');
    out.downloadError = !!err && err.textContent.includes('Sijil tidak dapat dijana');
    document.getElementById('signout-btn').click();
    await sleep(200);
    out.afterHash = location.hash;
    out.afterText = text();
    out.storageEmpty = !sessionStorage.getItem('${KEY}') && !localStorage.getItem('${KEY}');
    await go('#/sijil');
    out.sijilHash = location.hash;
    await go('#/kursus');
    out.kursusHash = location.hash;
    return out;
  `);
  step('A registers without Ingat saya: lands on #/pilih, state in sessionStorage only, pilih and course pages have Selesai / Keluar',
    a.hash === '#/pilih' && a.inSession && !a.inLocal && a.chooseSignout && a.topbarSignout, JSON.stringify(a).slice(0, 300));
  step('Kuiz Modul 1 is locked before the Modul 1 lesson; completing Modul 1 leads to Kuiz Modul 1',
    a.quizLockedFirst && a.afterLesson1 === '#/kursus?lesson=kuiz-modul-1', JSON.stringify(a).slice(0, 300));
  step('Kuiz Modul 1 with one wrong answer: 2/3 (66.67%) GAGAL', a.failText.includes('Markah anda: 2/3 (66.67%)') && a.failText.includes('GAGAL'),
    a.failText.slice(0, 300));
  step('failed attempt: Semakan Jawapan explains the wrong question and links the lesson, without the correct answer',
    a.failText.includes('Semakan Jawapan') && a.failText.includes('Betul 2 daripada 3.') && /Ulang kaji:\s*Pengenalan AI/.test(a.failText)
    && a.failText.includes('AI ialah singkatan bahasa Inggeris') && !a.failText.includes('Jawapan betul:')
    && !a.failText.includes('Kecerdasan Buatan (Artificial Intelligence)'), a.failText.slice(-600));
  step('Modul 2 shows the lock card before Kuiz Modul 1 is passed', a.modul2Locked, JSON.stringify(a).slice(0, 300));
  step('Kuiz Modul 1 retry: LULUS, no LENGKAP & TERUSKAN yet, sidebar LULUS, Teruskan ke Asas AI',
    a.passText.includes('Markah anda: 3/3 (100%)') && a.passText.includes('Tahniah! Anda lulus kuiz ini.') && a.noFinishYet
    && a.sidebarPass === 'LULUS' && a.nextHref === '#/kursus?lesson=modul-2-pengenalan', JSON.stringify(a).slice(0, 400));
  step('Kuiz Modul 2-5 pass in order; #/sijil and Penutup refused before Kuiz Modul 5 is passed',
    a.modules.length === 4 && a.modules.every(Boolean) && a.sijilBefore === '#/kursus?lesson=kuiz-modul-5' && a.penutupLocked,
    JSON.stringify(a).slice(0, 400));
  step('A completes the course and sees the certificate with Selesai / Keluar', a.certHash === '#/sijil' && a.certShowsName && a.hasSignout, JSON.stringify(a).slice(0, 300));
  step('certificate download failure shows "Sijil tidak dapat dijana" (no console error)', a.downloadError, JSON.stringify(a).slice(0, 300));
  step('Selesai / Keluar: landing shows "Anda telah keluar", no name, storage cleared',
    a.afterText.includes('Anda telah keluar') && a.afterText.includes('Daftar Sekarang') && !a.afterText.includes('DrafA') && a.storageEmpty,
    a.afterText.slice(0, 200));
  step('next visitor cannot open the certificate or course (redirected to registration)', a.sijilHash === '#/daftar' && a.kursusHash === '#/daftar',
    a.sijilHash + ' ' + a.kursusHash);

  await tab.evaluate(`location.hash = '#/'; location.reload(); return true;`);
  await sleep(800);
  const b = await tab.evaluate(`return document.getElementById('app').innerText;`);
  step('after reload the landing page shows no trace of A', !b.includes('DrafA') && b.includes('Daftar Sekarang'));

  // Idle timeout with the page reopened, and with the page left open.
  const c = await tab.evaluate(helpers + `
    await register('DrafB', false);
    await go('#/');
    const before = text().includes('DrafB');
    await go('#/sijil');
    const s = JSON.parse(sessionStorage.getItem('${KEY}'));
    s.lastActive = Date.now() - 31 * 60 * 1000;
    sessionStorage.setItem('${KEY}', JSON.stringify(s));
    location.reload();
    return before;
  `);
  await sleep(800);
  const c2 = await tab.evaluate(`return { hash: location.hash, txt: document.getElementById('app').innerText, empty: !sessionStorage.getItem('${KEY}') };`);
  step('idle 31 minutes (page reopened): session-only learner B is forgotten', c && c2.hash === '#/daftar' && !c2.txt.includes('DrafB') && c2.empty,
    JSON.stringify(c2).slice(0, 300));

  const g = await tab.evaluate(helpers + `
    await register('DrafB2', false);
    await go('#/');
    const before = text().includes('DrafB2');
    const real = Date.now;
    Date.now = () => real() + 31 * 60 * 1000; // page left open for 31 minutes
    await go('#/kursus');
    Date.now = real;
    return { before, hash: location.hash, txt: text(), empty: !sessionStorage.getItem('${KEY}') };
  `);
  step('idle 31 minutes (page left open): next click forgets session-only learner B2', g.before && g.hash === '#/daftar' && !g.txt.includes('DrafB2') && g.empty,
    JSON.stringify(g).slice(0, 300));

  // Long reading inside one module: slide clicks every 20 minutes for 60 minutes keep the learner.
  const r = await tab.evaluate(helpers + `
    await register('DrafR', false);
    for (let n = 1; n <= 3; n++) await passModule(n);
    await go('#/kursus?lesson=modul-4-pengenalan&slide=1');
    const real = Date.now;
    let offset = 0;
    Date.now = () => real() + offset;
    for (let i = 0; i < 3; i++) {
      offset += 20 * 60 * 1000;
      document.getElementById('next-btn').click();
      await sleep(50);
    }
    offset += 20 * 60 * 1000;
    document.getElementById('next-btn').click();
    await sleep(50);
    const counter = document.getElementById('slide-counter').textContent;
    while (!document.getElementById('next-btn').classList.contains('is-disabled')) document.getElementById('next-btn').click();
    document.getElementById('complete-form').requestSubmit();
    await sleep(150);
    Date.now = real;
    const s = JSON.parse(sessionStorage.getItem('${KEY}') || 'null');
    return { counter, hash: location.hash, done: !!(s && s.done['modul-4-pengenalan']),
      name: document.getElementById('app').textContent.includes('DrafR') }; // the name is hidden on narrow screens
  `);
  step('80 minutes reading Modul 4 (after Kuiz Modul 1-3) with slide clicks: not signed out, lesson saved', r.counter === '5/12' && r.done && r.name
    && r.hash === '#/kursus?lesson=kuiz-modul-4', JSON.stringify(r));

  // Self-Learning: video (YouTube IFrame API replaced by a stub, no network) -> Kuiz Self-Learning -> certificate.
  const sl = await tab.evaluate(helpers + `
    await register('DrafS', false);
    const out = { hash: location.hash, ctas: [...document.querySelectorAll('.track-card .track-cta')].map((a) => a.getAttribute('href')) };
    const fake = { t: 0, d: 10, seeks: [], destroyed: 0 };
    window.YT = { PlayerState: { ENDED: 0, PLAYING: 1, PAUSED: 2 }, Player: function (host, cfg) {
      fake.cfg = cfg;
      const p = { getCurrentTime: () => fake.t, getDuration: () => fake.d, seekTo: (s) => { fake.seeks.push(s); fake.t = s; },
        playVideo() {}, setPlaybackRate(r) { fake.rate = r; }, destroy() { fake.destroyed++; } };
      setTimeout(() => cfg.events.onReady({ target: p }), 0);
      return p;
    } };
    const tick = async (t) => { fake.t = t; await sleep(600); };
    const watchText = () => document.getElementById('video-watch-text').textContent;
    const btn = () => document.getElementById('complete-btn');
    const s0 = JSON.parse(sessionStorage.getItem('${KEY}'));
    out.storedName = s0.learner.full_name + '|' + ('first_name' in s0.learner);
    await go('#/kursus?track=self-learning');
    out.openHash = location.hash;
    out.intro = (document.getElementById('video-lesson') || { dataset: {} }).dataset.youtube;
    await go('#/kursus?lesson=self-penutup');
    out.outroLocked = !!document.getElementById('lock-card') && text().includes('Penutup dibuka selepas anda lulus Kuiz Self-Learning.');
    await go('#/kursus?track=self-learning');
    out.poster = !!document.getElementById('video-play') && !document.querySelector('#video-lesson iframe') && btn().disabled;
    out.active = (document.querySelector('.track-switch-btn.is-active') || {}).textContent;
    await go('#/kursus?lesson=kuiz-self-learning');
    out.quizLocked = !!document.getElementById('lock-card') && text().includes('Sila lengkapkan semua pelajaran dalam Modul 1: Video Kursus');
    await go('#/kursus?lesson=self-video');
    document.getElementById('video-play').click();
    await sleep(50);
    out.player = [document.getElementById('video-play').hidden, fake.cfg.videoId, fake.cfg.host, fake.cfg.playerVars.rel].join();
    out.startText = watchText();
    fake.cfg.events.onStateChange({ data: 1 });
    for (const t of [1, 2, 3]) await tick(t); // 2 seconds played
    await tick(300); // skip ahead: allowed, but the jump is not counted as watched
    for (const t of [301, 302]) await tick(t); // 4 seconds played
    out.skip = [fake.seeks.length, btn().disabled, watchText()].join('|');
    fake.cfg.events.onStateChange({ data: 2 }); // pause saves the seconds played
    out.saved = (JSON.parse(sessionStorage.getItem('${KEY}')).videoPos || {})['self-video'];
    fake.cfg.events.onStateChange({ data: 1 });
    for (let t = 303; t <= 310; t++) await tick(t); // 11 seconds played
    out.finished = [btn().disabled, watchText().includes('Anda boleh teruskan')].join();
    document.getElementById('complete-form').requestSubmit();
    await sleep(150);
    out.afterVideo = location.hash;
    out.destroyed = fake.destroyed;
    // 7/10 fails, 10/10 passes and offers LENGKAP & TERUSKAN.
    const answer = (wrong) => {
      const qf = document.getElementById('quiz-form');
      let i = 0;
      for (const fs of qf.querySelectorAll('fieldset')) {
        const q = fs.querySelector('input').name;
        const v = i++ < wrong ? (ANS[q] === 'a' ? 'b' : 'a') : ANS[q];
        qf.querySelector('input[name="' + q + '"][value="' + v + '"]').checked = true;
      }
      out.questions = i;
      qf.requestSubmit();
    };
    answer(3);
    await sleep(200);
    out.failText = text();
    await go('#/kursus?lesson=kuiz-self-learning');
    answer(0);
    await sleep(200);
    out.passText = text();
    await go('#/kursus?lesson=modul-2-pengenalan');
    out.elearningLocked = !!document.getElementById('lock-card');
    await go('#/kursus?lesson=kuiz-self-learning');
    out.hasFinish = !!document.getElementById('finish-form');
    return out;
  `);
  step('registration lands on Pilih Cara Belajar with E-Learning and Self-Learning buttons',
    sl.hash === '#/pilih' && sl.ctas.join() === '#/kursus?track=e-learning,#/kursus?track=self-learning', JSON.stringify(sl).slice(0, 300));
  step('Self-Learning opens at the video: poster button (no iframe yet), LENGKAP disabled, Kuiz Self-Learning locked',
    sl.openHash === '#/kursus?track=self-learning' && sl.intro === 'Z4C82eyhwgU' && sl.outroLocked
    && sl.poster && sl.active === 'Self-Learning' && sl.quizLocked, JSON.stringify(sl).slice(0, 300));
  step('registration has one Nama Penuh field; the name is stored with spaces collapsed',
    sl.storedName === 'Ujian DrafS|false', sl.storedName);
  step('a click loads the youtube-nocookie player; skipping is allowed but a jump does not count as watched (4 of 10 seconds)',
    sl.player === 'true,aqz-KE-bpKQ,https://www.youtube-nocookie.com,0' && sl.startText === 'Ditonton 0 / 10 saat'
    && sl.skip === '0|true|Ditonton 4 / 10 saat' && sl.saved === 4, JSON.stringify(sl).slice(0, 400));
  step('10 seconds played from any part: LENGKAP & TERUSKAN opens Kuiz Self-Learning and the player is stopped',
    sl.finished === 'false,true' && sl.afterVideo === '#/kursus?lesson=kuiz-self-learning' && sl.destroyed >= 1, JSON.stringify(sl).slice(0, 400));
  step('Kuiz Self-Learning: 10 questions, 7/10 GAGAL, 10/10 LULUS with LENGKAP & TERUSKAN; E-Learning modules stay locked',
    sl.questions === 10 && sl.failText.includes('Markah anda: 7/10 (70%)') && sl.failText.includes('GAGAL')
    && sl.passText.includes('Markah anda: 10/10 (100%)') && sl.elearningLocked
    && !sl.failText.includes('Jawapan betul:') && sl.failText.includes('Betul 7 daripada 10.')
    && sl.passText.includes('Betul 10 daripada 10.') && sl.passText.includes('S.T.O.P: berhenti, semak dan sahkan'), JSON.stringify(sl).slice(0, 400));

  const sl2 = await tab.evaluate(helpers + `
    const quiz = document.getElementById('finish-form');
    const out = {};
    if (!quiz) {
      const id = JSON.parse(sessionStorage.getItem('${KEY}')).attempts.slice(-1)[0].id;
      await go('#/kursus?lesson=kuiz-self-learning&attempt=' + id);
    }
    document.getElementById('finish-form').requestSubmit();
    await sleep(200);
    out.hash = location.hash;
    out.cert = text().includes('Ujian DrafS');
    await go('#/kursus?lesson=penutup');
    out.penutupOpen = !document.getElementById('lock-card');
    await go('#/kursus?lesson=self-penutup');
    out.penutupOpen = out.penutupOpen && !document.getElementById('lock-card')
      && document.getElementById('video-lesson').dataset.youtube === 'SkVqJ1SGeL0';
    await go('#/pilih');
    out.choose = text().includes('Kursus ini telah selesai');
    delete window.YT;
    return out;
  `);
  step('Self-Learning completion gives the certificate; E-Learning then counts as finished (Penutup open, pilih says selesai)',
    sl2.hash === '#/sijil' && sl2.cert && sl2.penutupOpen && sl2.choose, JSON.stringify(sl2));

  await tab.evaluate(helpers + `await register('DrafC', false); return true;`);
  const tab2 = await openTab(base);
  const d = await tab2.evaluate(`return document.getElementById('app').innerText;`);
  step('new window: session-only learner C is not shown', !d.includes('DrafC') && d.includes('Daftar Sekarang'));
  await tab2.close();

  // Own device: "Ingat saya" keeps the learner across windows until "Bukan anda? Mula semula".
  await tab.evaluate(helpers + `await register('DrafD', true); return true;`);
  const tab3 = await openTab(base);
  const e = await tab3.evaluate(`
    const t = document.getElementById('app').innerText;
    const s = JSON.parse(localStorage.getItem('${KEY}') || 'null');
    const until = s && s.rememberUntil;
    document.getElementById('reset-btn').click();
    await new Promise((r) => setTimeout(r, 200));
    return { t, until, after: document.getElementById('app').innerText, empty: !localStorage.getItem('${KEY}') && !sessionStorage.getItem('${KEY}') };
  `);
  const days = (e.until - Date.now()) / 86400000;
  step('Ingat saya: learner D is remembered in a new window for 30 days', e.t.includes('Selamat kembali') && e.t.includes('DrafD') && days > 29.9 && days <= 30,
    'days ' + days);
  step('Bukan anda? Mula semula: D is forgotten', !e.after.includes('DrafD') && e.after.includes('Anda telah keluar') && e.empty);
  await tab3.close();

  // Remembered state past 30 days, and state saved by the old draft (no remember flag), are dropped.
  await tab.evaluate(helpers + `
    await register('DrafE', true);
    const s = JSON.parse(localStorage.getItem('${KEY}'));
    s.rememberUntil = Date.now() - 1000;
    localStorage.setItem('${KEY}', JSON.stringify(s));
    sessionStorage.clear();
    return true;
  `);
  const tab4 = await openTab(base);
  const x = await tab4.evaluate(`return { t: document.getElementById('app').innerText, empty: !localStorage.getItem('${KEY}') };`);
  step('Ingat saya after 30 days: learner E is forgotten', !x.t.includes('DrafE') && x.t.includes('Daftar Sekarang') && x.empty, JSON.stringify(x).slice(0, 200));
  await tab4.evaluate(`
    sessionStorage.clear();
    localStorage.setItem('${KEY}', JSON.stringify({ learner: { first_name: 'Ujian', last_name: 'DrafLama' }, done: { 'modul-1-pengenalan': true }, attempts: [], completion: null }));
    return true;
  `);
  await tab4.close();
  const tab5 = await openTab(base);
  const y = await tab5.evaluate(`return { t: document.getElementById('app').innerText, empty: !localStorage.getItem('${KEY}') };`);
  step('state saved by the old draft (before Ingat saya) is dropped', !y.t.includes('DrafLama') && y.t.includes('Daftar Sekarang') && y.empty, JSON.stringify(y).slice(0, 200));
  await tab5.close();
  // Optional: save certificate renders for a visual check (NADI_CERT_OUT=<folder>).
  if (process.env.NADI_CERT_OUT) {
    const renders = await tab.evaluate(`
      const base = { course_title: 'x', date_text: '23 November 2026', code: 'NADI-0123456789', issuer: 'NADI' };
      const names = [['Ali', 'Ahmad'], ['Siti Nur Aisyah Humaira Binti', 'Mohamad Abdul Rahman Shahrul Nizam Al-Haj Ismail Iskandar Zulkarnain Tengku Sulaiman']];
      const out = [];
      for (const [first, last] of names) {
        const canvas = await window.NADI_DRAFT.drawCertificate(Object.assign({ name: first + ' ' + last }, base));
        out.push(canvas.toDataURL('image/png').split(',')[1]);
      }
      return out;
    `);
    writeFileSync(join(process.env.NADI_CERT_OUT, 'cert-docs.png'), Buffer.from(renders[0], 'base64'));
    writeFileSync(join(process.env.NADI_CERT_OUT, 'cert-docs-long.png'), Buffer.from(renders[1], 'base64'));
    console.log('wrote cert-docs.png and cert-docs-long.png to ' + process.env.NADI_CERT_OUT);
  }
  await tab.close();

  step('no console errors, exceptions or failed requests', consoleErrors.length === 0, consoleErrors.join(' | '));
} catch (err) {
  failures++;
  console.log('FAIL aborted: ' + err.message);
} finally {
  browser.kill();
  server.close();
  await sleep(500);
  try { rmSync(profile, { recursive: true, force: true }); } catch { /* browser may still hold files */ }
}
console.log(failures === 0 ? '\nAll docs checks passed.' : `\n${failures} docs check(s) failed.`);
process.exit(failures === 0 ? 0 : 1);
