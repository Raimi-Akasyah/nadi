// Headless-browser check of the docs/ GitHub Pages draft, served under the /nadi/ subpath like
// https://raimi-akasyah.github.io/nadi/. Needs Node 22+ (global WebSocket) and Chrome or Edge.
// Run from the project folder: node tests/docs-check.mjs
// Fails on any console error, uncaught exception or failed request.
import { spawn } from 'node:child_process';
import { createServer } from 'node:http';
import { mkdtempSync, readFileSync, rmSync, statSync } from 'node:fs';
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
  async function register(last, remember) {
    await go('#/daftar');
    const f = document.getElementById('register-form');
    const v = { first_name: 'Ujian', last_name: last, age: '30', email: last.toLowerCase() + '@example.com',
      phone: '+60 12-345 6789', address: 'No 1, Jalan Ujian', occupation: 'Penguji' };
    for (const k in v) f.elements[k].value = v[k];
    f.elements.remember.checked = remember;
    f.requestSubmit();
    await sleep(200);
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

  // Learner A on a shared computer: registers without "Ingat saya", completes the course, downloads, leaves.
  const a = await tab.evaluate(helpers + `
    await register('DrafA', false);
    const out = { hash: location.hash, inSession: !!sessionStorage.getItem('${KEY}'), inLocal: !!localStorage.getItem('${KEY}') };
    out.topbarSignout = !!document.getElementById('topbar-signout-btn');
    for (let n = 1; n <= 5; n++) {
      await go('#/kursus?lesson=modul-' + n + '-pengenalan&slide=5');
      document.getElementById('complete-form').requestSubmit();
      await sleep(150);
    }
    await go('#/kursus?lesson=kuiz-akhir');
    const qf = document.getElementById('quiz-form');
    const ans = ${JSON.stringify(ANSWERS)};
    for (const q in ans) qf.querySelector('input[name="' + q + '"][value="' + ans[q] + '"]').checked = true;
    qf.requestSubmit();
    await sleep(200);
    document.getElementById('finish-form').requestSubmit();
    await sleep(200);
    out.certHash = location.hash;
    out.certShowsName = text().includes('Ujian DrafA');
    out.hasSignout = !!document.getElementById('signout-btn');
    document.getElementById('download-btn').click();
    await sleep(200);
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
  step('A registers without Ingat saya: state in sessionStorage only, course page has Selesai / Keluar',
    a.hash.startsWith('#/kursus') && a.inSession && !a.inLocal && a.topbarSignout, JSON.stringify(a).slice(0, 300));
  step('A completes the course and sees the certificate with Selesai / Keluar', a.certHash === '#/sijil' && a.certShowsName && a.hasSignout, JSON.stringify(a).slice(0, 300));
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
    document.getElementById('complete-form').requestSubmit();
    await sleep(150);
    Date.now = real;
    const s = JSON.parse(sessionStorage.getItem('${KEY}') || 'null');
    return { counter, hash: location.hash, done: !!(s && s.done['modul-4-pengenalan']),
      name: document.getElementById('app').textContent.includes('DrafR') }; // the name is hidden on narrow screens
  `);
  step('80 minutes reading Modul 4 with slide clicks: not signed out, lesson saved', r.counter === '5/5' && r.done && r.name
    && r.hash.startsWith('#/kursus?lesson=modul-5'), JSON.stringify(r));

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
