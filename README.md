# NADI LMS

A small learning portal for the NADI community course "Malaysia AWS AI Essentials".

Learners register once with their details (first name, last name, age, e-mail, phone, address, occupation). There is no account or password. A learner who comes back later, on the same or another device, continues with "Sambung Semula" by entering only their e-mail (see [Continuing later: Sambung Semula](#continuing-later-sambung-semula) and [Shared computers and own devices](#shared-computers-and-own-devices)). They then go through Modul 1 to Modul 5 in a slide viewer and take a 10-question final quiz (Kuiz Akhir). A score of at least 75% passes, and attempts are unlimited. After passing, "LENGKAP & TERUSKAN" records the course completion and the learner can download a PDF certificate with their first and last name, the completion date and a unique certificate number. Anyone can check that number on the public verification page.

Plain PHP 8 and MySQL/MariaDB. No framework, no Composer and no build step.

## Folder layout

| Folder | Contents |
| --- | --- |
| `public/` | The web root: page scripts (`index.php`, `register.php`, `course.php`, `quiz-submit.php`, `complete.php`, `certificate.php`, `certificate-download.php`, `verify.php`, ...) and `assets/` (CSS, JS, logo). |
| `src/` | Bootstrap, database connection, helpers, validation, repositories (all SQL), `Course.php`, `Quiz.php` (grading), `CertificatePdf.php`. |
| `views/` | HTML templates. |
| `content/` | `course.php` (sections, lessons, slides), `quiz.php` (questions, answers, pass mark) and `certificate.php` (certificate template path and the positions of the name, date and certificate number). |
| `config/` | `config.example.php`. Your real `config.php` goes here and is not committed. |
| `sql/` | `schema.sql`, imported once with phpMyAdmin. |
| `lib/fpdf/` | FPDF 1.86, used to build the certificate PDF. |
| `tests/` | `lint.ps1`, `unit.php`, `e2e.php`, `docs-check.mjs` (headless-browser check of `docs/`), `render-cert.php` (sample certificates for a visual check). |

Every folder except `public/` has an `.htaccess` with `Require all denied`, as a second guard in case the document root is not changed.

## Requirements

- PHP 8.0 or newer with `pdo_mysql` (and `iconv` or `mbstring`, both standard).
- MySQL 5.7+ or MariaDB 10.3+.

## Deploying on Plesk

1. **Create the database.** Websites & Domains → Databases → Add Database. Enter a database name, then create a database user and password. Note the host (usually `localhost`).
2. **Import the tables.** In the database list, click phpMyAdmin for the new database, open the Import tab, choose `sql/schema.sql` and click Import. This creates `learners`, `learner_tokens`, `resume_attempts`, `lesson_progress`, `quiz_attempts` and `completions`. Registrations, progress, quiz attempts and certificates are stored here and can be viewed in phpMyAdmin. **Updating an existing site:** import `sql/schema.sql` again. Every table uses `CREATE TABLE IF NOT EXISTS`, so this only adds the new `learner_tokens` and `resume_attempts` tables and leaves existing data alone.
3. **Upload the files** to `httpdocs/` with File Manager (upload a zip and extract it) or with Plesk Git. The result should be `httpdocs/public`, `httpdocs/src`, `httpdocs/config` and so on.
4. **Set the document root.** Hosting Settings (Hosting & DNS → Hosting) → Document root: `httpdocs/public`. Save.
5. **Configure the database connection.** Copy `config/config.example.php` to `config/config.php` and fill in the host, database name, user and password from step 1. Alternatively, leave `config/config.php` absent and set the environment variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` in PHP Settings. Never commit `config/config.php`.
6. **PHP version.** PHP Settings → choose PHP 8.x (8.0 or newer).
7. **HTTPS.** SSL/TLS Certificates → install a free Let's Encrypt certificate and turn on the permanent redirect from HTTP to HTTPS. Session and learner cookies are marked secure automatically when the site is served over HTTPS.

Open the site. The landing page shows "Daftar Sekarang".

## Static preview on GitHub Pages (draft)

`docs/` holds a static copy of the portal for previewing before the Plesk deployment: landing page, registration form (client-side checks only), module viewer, Kuiz Akhir and a certificate PDF generated in the browser. It is plain HTML, CSS and JavaScript with no backend. Nothing is sent to a server; progress is kept in the browser (sessionStorage, or localStorage when "Ingat saya" is ticked, as described below), and every page shows "Draf / pratonton — data tidak disimpan". The quiz answers are visible in `docs/assets/js/app.js`, so use it for preview only.

To publish it: on GitHub open the repository → Settings → Pages → Build and deployment → Source: **Deploy from a branch** → Branch: **main**, folder **/docs** → Save. After a minute the preview is at `https://raimi-akasyah.github.io/nadi/`.

To try it locally: `& 'C:\xampp 8.2\php\php.exe' -S 127.0.0.1:8090 -t docs`, then open http://127.0.0.1:8090.

The course structure, slide titles and quiz questions in `docs/assets/js/app.js` are copied from `content/course.php` and `content/quiz.php`, and the slide images in `docs/assets/slides/` are copies of `public/assets/slides/`. Update both when the content changes.

## Editing the content

- **Slides and modules:** edit `content/course.php`. Each section has lessons, and each presentation lesson has `slides` of `['title' => ..., 'image' => 'assets/slides/page-NN.webp']`. The images are pages of the course deck PDF ("NADI _AWS Training Slide Deck"), rendered as 1600x900 WebP and named by PDF page number (`page-01.webp`, `page-07.webp`, ...). They live in `public/assets/slides/`, with identical copies in `docs/assets/slides/` for the static preview. The `title` is the image's alt text. A slide without `image` shows `title` and `body` as text. Keep the lesson `key` values unchanged once learners have started, because progress is stored by key.

  | Section | PDF pages |
  | --- | --- |
  | Pengenalan | 1, 7 |
  | Modul 1: Kesedaran AI & Keyakinan Digital | 8-10 |
  | Modul 2: Asas AI | 11-14 |
  | Modul 3: Kemahiran Praktikal AI | 15-23 |
  | Modul 4: AI untuk Perniagaan Mikro & Produktiviti | 24-35 |
  | Modul 5: AI Bertanggungjawab & Kesediaan Masa Hadapan | 36-42 |
  | Kuiz Akhir | (quiz, no slides) |
  | Penutup | 45 |

  Pages 2-6, 43 and 44 of the deck are not used.
- **Quiz questions:** edit `content/quiz.php`. Each question has an `id`, `text`, four `options` (`a` to `d`) and the correct `answer`. Correct answers are never sent to the browser.
- **Pass mark:** change `pass_mark` in `content/quiz.php` (default 75). A learner passes when `correct * 100 / total >= pass_mark`.
- **Which modules unlock the quiz:** `quiz_requires` in `content/course.php`.
- **Certificate:** the whole design (logos, "SIJIL PENYERTAAN", wording, signature, badge) is one picture, `public/assets/certificate/template.png`, drawn over the full A4 landscape page. The PDF covers the "[ NAMA PESERTA ]" placeholder with a white rectangle and prints the learner's name there (shrunk, or split over two lines, when it is long), and prints the date and certificate number at the bottom left. All positions are in millimetres in `content/certificate.php`. To find a position on a new template, read the pixel position in an image editor and convert it: mm = px × 297 / image width in px. To change the design, replace `template.png` and adjust those numbers. The static preview uses a copy in `docs/assets/certificate/template.png` and a copy of the numbers in `CERT` in `docs/assets/js/app.js`, so update both. The current template is 1123 × 794 px, which is 96 dpi on A4: it looks fine on screen but will be slightly soft when printed. For sharp prints, export the same design at 3508 × 2480 px (300 dpi) and replace both copies; the coordinates in mm stay the same. To check a change, run `& 'C:\xampp 8.2\php\php.exe' tests/render-cert.php <folder>` (writes `cert-php.pdf` and `cert-php-long.pdf`, no database needed) and, for the preview, `node tests/docs-check.mjs` with the environment variable `NADI_CERT_OUT=<folder>` (writes `cert-docs.png` and `cert-docs-long.png`). `certificate.issuer` in `config/config.php` is only the PDF author. The drawing code is in `src/CertificatePdf.php`.

## Continuing later: Sambung Semula

Progress (lessons done, quiz pass and certificate) is stored in the database against the learner, not in the browser. To continue, the learner opens the site, goes to "Sambung Semula" (on the landing page, or the link on the registration page, `resume.php`), and enters the e-mail they registered with. No code or password is asked for.

- **Same device, next day.** If "Ingat saya" was ticked, the site opens straight at their progress. If not, or after "Selesai / Keluar" or the 30-minute timeout, they enter their e-mail in "Sambung Semula" and continue where they stopped.
- **Different device.** They enter their e-mail in "Sambung Semula" and get the same progress, quiz result and certificate. Nothing is reset.
- **E-mail not found.** The page says "Tiada peserta berdaftar dengan e-mel ini" and offers registration.
- **Registering again with the same e-mail** does not create a second learner. It resumes the existing one and says "E-mel ini sudah berdaftar, jadi kemajuan anda yang sedia ada telah disambung semula", with the name that will appear on the certificate. The details typed in the new form are not saved; the original name stays on the certificate.
- **Several learners with one e-mail** (left from earlier testing): the one with the most progress is resumed. The order is a course completion first, then a passed quiz, then the most completed lessons, and the newest record on a tie. No rows are merged or deleted.

E-mail matching ignores letter case and surrounding spaces. Anyone who knows a learner's e-mail can resume as that learner and download their certificate. This was a deliberate choice to keep the course free of codes and passwords.

Abuse protection: each lookup by e-mail (Sambung Semula, or registering with an e-mail that already exists) is counted in `resume_attempts`, with the IP address and e-mail stored only as SHA-256 hashes. Within 15 minutes, one IP address may make 40 lookups and one e-mail may be looked up 10 times (`RESUME_*` constants in `src/helpers.php`). The per-IP limit is high on purpose, because a community centre with 30 learners usually shares one public IP address. Beyond the limit the page answers "Terlalu banyak cubaan Sambung Semula" (HTTP 429). Rows older than a day are deleted automatically. The forms use the same CSRF token as the rest of the site, and all SQL is parameterized.

## Shared computers and own devices

The course is used both on shared computers (community centres, cafes) and on learners' own phones and laptops. The registration and Sambung Semula forms have an unticked checkbox, "Ingat saya pada peranti ini selama 30 hari".

- **Not ticked (the default, for shared computers).** The learner is kept only in the browser session. They are signed out when they press "Selesai / Keluar" (on every course page and on the certificate page) or after 30 minutes without activity (`LEARNER_IDLE_MINUTES` in `src/helpers.php`). Do not rely on closing the browser: Chrome and Edge can restore the previous session, including its cookies, when "Continue where you left off" is on. Tell learners on shared computers to press "Selesai / Keluar" before they leave. Their progress stays saved, and they can continue later with Sambung Semula. Registering or resuming without the box ticked also removes any earlier learner's remember cookie on that browser.
- **Ticked (own device).** A 30-day cookie (`LEARNER_COOKIE_DAYS`) brings the learner back to their progress after the browser is reopened. Only a SHA-256 hash of the cookie's random token is stored in the database. Ticking it on a second device (through Sambung Semula) adds a token in `learner_tokens`, so both devices stay remembered.

**Long reading.** Slide changes and quiz answers happen in the browser without loading a page. While the learner is active (clicks, keys, scrolling, touches), `public/assets/js/course.js` sends a small keep-alive request to `keepalive.php` at most every 4 minutes, so a learner reading one long module (Modul 4 takes about 45 minutes) is not signed out. A learner who stops interacting for 30 minutes is still signed out.

"Selesai / Keluar" and "Bukan anda? Mula semula" (landing page) both sign out: they clear the session, delete the remember cookie and show "Anda telah keluar". After that the browser shows no name or progress and the course and certificate pages redirect to registration. The progress itself stays in the database. Cookies are HttpOnly, SameSite=Lax and marked secure over HTTPS, and every form uses a CSRF token.

The `docs/` draft follows the same rules where it can: without the box ticked it keeps its data in `sessionStorage` and forgets it after 30 minutes without activity; with the box ticked it uses `localStorage` for 30 days from registration. Data saved in `localStorage` by the draft before "Ingat saya" existed is dropped. The draft has no server, so Sambung Semula cannot work there: it shows a note that Sambung Semula only works in the real portal and that progress stays in that browser only.

## Certificate verification

Each completion gets one certificate number such as `NADI-1A2B3C4D5E`, stored in `completions.certificate_code` and printed on the PDF. Anyone can check it at `https://your-domain/verify.php?code=NADI-1A2B3C4D5E` or by typing it into the form on `verify.php`. The page shows "Sijil sah" with the name, course and date, or "Sijil tidak dijumpai".

## Local development (Windows, XAMPP)

```powershell
# Is MariaDB running?
& 'C:\xampp 8.2\mysql\bin\mysql.exe' --no-defaults -h 127.0.0.1 -u root -e "SELECT 1"

# If not, start it (keep this window open; cwd C:\xampp 8.2)
& 'C:\xampp 8.2\mysql\bin\mysqld.exe' --no-defaults '--basedir=C:/xampp 8.2/mysql' '--datadir=C:/xampp 8.2/mysql/data' --port=3306 --standalone --console

# Create the database and import the schema
& 'C:\xampp 8.2\mysql\bin\mysql.exe' --no-defaults -h 127.0.0.1 -u root -e "CREATE DATABASE IF NOT EXISTS nadi_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; USE nadi_lms; SOURCE C:/Users/hp/Downloads/NADI/sql/schema.sql;"

# Local config: copy config/config.example.php to config/config.php
# with host 127.0.0.1, port 3306, name nadi_lms, user root, empty password.

# Dev server (from the project folder)
& 'C:\xampp 8.2\php\php.exe' -S 127.0.0.1:8080 -t public
```

Then open http://127.0.0.1:8080.

## Tests

Run from the project folder:

```powershell
# Syntax check of every PHP file
powershell -ExecutionPolicy Bypass -File tests/lint.ps1

# Unit tests (no database needed): dates, course structure, quiz data, validation, grading, PDF
& 'C:\xampp 8.2\php\php.exe' tests/unit.php

# End-to-end test (needs the database and the dev server running)
& 'C:\xampp 8.2\php\php.exe' tests/e2e.php http://127.0.0.1:8080

# docs/ draft in headless Chrome or Edge, served under /nadi/ like GitHub Pages (needs Node 22+)
node tests/docs-check.mjs
```

The e2e test registers new learners with unique `e2e...+<time>@example.com` e-mails, so it leaves test rows in the local database. Steps 11 to 17 cover shared computers: a learner who finishes and presses "Selesai / Keluar", a closed browser, the idle timeout and the "Ingat saya" cookie. Steps 18 to 26 cover Sambung Semula: progress made on one device (cookie jar) restored on a fresh one and finished there with a certificate in the learner's name, the not-found message, registering again with the same e-mail, duplicate e-mails, 80 minutes in one module kept alive by `keepalive.php`, "Ingat saya" on two devices, and the rate limit. The idle-timeout steps age the dev server's session file, so run the test on the same machine as the server. The test clears rate-limit rows for 127.0.0.1 before and after it runs.

`tests/docs-check.mjs` starts its own static server for `docs/` under `/nadi/` and a headless browser (Chrome, Edge, or `CHROME_PATH`). It fails on any console error. It covers the shared-computer flow, the idle timeout, long reading with slide clicks, the 30-day "Ingat saya" expiry, dropping data from the old draft, and the Sambung Semula note.
