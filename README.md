# NADI LMS

A small learning portal for the NADI community course "Malaysia AWS AI Essentials".

Learners register once with their details (full name, age, gender, e-mail, occupation from a list, and their NADI centre chosen as Negeri → Daerah → Pusat NADI; see [Registration form and NADI centres](#registration-form-and-nadi-centres)). There is no account or password. A learner who comes back later, on the same or another device, continues with "Sambung Semula" by entering only their full name or their e-mail (see [Continuing later: Sambung Semula](#continuing-later-sambung-semula) and [Shared computers and own devices](#shared-computers-and-own-devices)). After registering (or Sambung Semula) they choose how to learn on "Pilih Cara Belajar" (`pilih.php`): **E-Learning** (slides, described below) or **Self-Learning** (one video watched to the end, then Kuiz Self-Learning with all 10 module questions; see [Self-Learning (video)](#self-learning-video)). Both tracks share one completion and one certificate, so finishing either one is enough. In E-Learning they go through Modul 1 to Modul 5 in a slide viewer. Each module ends with its own quiz (Kuiz Modul 1 to Kuiz Modul 5). A score of at least 75% passes, and attempts are unlimited. The modules open in order: Modul 2 opens after Kuiz Modul 1 is passed, and so on. After all five module quizzes are passed, "LENGKAP & TERUSKAN" records the course completion and the learner can download a PDF certificate with their first and last name, the completion date and a unique certificate number. Anyone can check that number on the public verification page.

Plain PHP 8 and MySQL/MariaDB. No framework, no Composer and no build step.

## Folder layout

| Folder | Contents |
| --- | --- |
| `public/` | The web root: page scripts (`index.php`, `register.php`, `course.php`, `quiz-submit.php`, `complete.php`, `certificate.php`, `certificate-download.php`, `verify.php`, ...) and `assets/` (CSS, JS, logo). |
| `src/` | Bootstrap, database connection, helpers, validation, repositories (all SQL), `Course.php`, `Quiz.php` (grading), `CertificatePdf.php`. |
| `views/` | HTML templates. |
| `content/` | `course.php` (E-Learning: sections, lessons, slides), `self-learning.php` (Self-Learning: the video lesson and its quiz), `quiz.php` (questions, answers, pass mark) and `certificate.php` (certificate template path and the positions of the name, date and certificate number). |
| `config/` | `config.example.php`. Your real `config.php` goes here and is not committed. |
| `sql/` | `schema.sql`, imported once with phpMyAdmin, `migrate-module-quizzes.sql` for sites created before the module quizzes, `migrate-full-name.sql` for sites created before the single "Nama Penuh" field, and `migrate-registration-fields.sql` for sites created before Jantina and Pusat NADI. |
| `lib/fpdf/` | FPDF 1.86, used to build the certificate PDF. |
| `bin/` | `admin-password.php` (creates the admin password hash). |
| `tests/` | `lint.ps1`, `unit.php`, `e2e.php`, `docs-check.mjs` (headless-browser check of `docs/`), `render-cert.php` (sample certificates for a visual check). |

Every folder except `public/` has an `.htaccess` with `Require all denied`, as a second guard in case the document root is not changed.

## Requirements

- PHP 8.0 or newer with `pdo_mysql` (and `iconv` or `mbstring`, both standard).
- MySQL 5.7+ or MariaDB 10.3+.

## Deploying on Plesk

1. **Create the database.** Websites & Domains → Databases → Add Database. Enter a database name, then create a database user and password. Note the host (usually `localhost`).
2. **Import the tables.** In the database list, click phpMyAdmin for the new database, open the Import tab, choose `sql/schema.sql` and click Import. This creates `learners`, `learner_tokens`, `resume_attempts`, `lesson_progress`, `quiz_attempts` and `completions`. Registrations, progress, quiz attempts and certificates are stored here and can be viewed in phpMyAdmin. **Updating an existing site:** import `sql/schema.sql` again, then import `sql/migrate-module-quizzes.sql`, then `sql/migrate-full-name.sql`, then `sql/migrate-registration-fields.sql` (do this before uploading the new files: registration writes `learners.full_name`, `gender`, `state`, `district`, `nadi_code` and `nadi_name`). The registration-fields migration adds those columns (empty for existing learners) and gives `phone` and `address` an empty default; existing phone numbers and addresses stay in the table. It deletes nothing and is safe to import twice. The full-name migration adds `learners.full_name`, fills it with first name + last name for every existing learner (so existing certificates keep the same name), gives `first_name` and `last_name` an empty default, and adds the indexes used by Sambung Semula. It deletes nothing and is safe to import twice. Every table in `schema.sql` uses `CREATE TABLE IF NOT EXISTS`, so it only adds missing tables (such as `learner_tokens` and `resume_attempts`) and leaves existing data alone. The migration adds the column `quiz_attempts.quiz_key` (which module quiz an attempt belongs to) and its index, only when they are missing, so it is safe to import twice. Every existing attempt gets `quiz_key = 'kuiz-akhir'`, the old single final quiz. Nothing is deleted: a learner who already passed the old Kuiz Akhir, or already has a completion, counts as having passed all five module quizzes, keeps their certificate number and date, and `verify.php` keeps working. A learner with old lesson progress but no pass keeps their completed lessons and takes Kuiz Modul 1, 2, ... in order.
3. **Upload the files** to `httpdocs/` with File Manager (upload a zip and extract it) or with Plesk Git. The result should be `httpdocs/public`, `httpdocs/src`, `httpdocs/config` and so on.
4. **Set the document root.** Hosting Settings (Hosting & DNS → Hosting) → Document root: `httpdocs/public`. Save.
5. **Configure the database connection.** Copy `config/config.example.php` to `config/config.php` and fill in the host, database name, user and password from step 1. Alternatively, leave `config/config.php` absent and set the environment variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` in PHP Settings. Never commit `config/config.php`.
6. **PHP version.** PHP Settings → choose PHP 8.x (8.0 or newer).
7. **HTTPS.** SSL/TLS Certificates → install a free Let's Encrypt certificate and turn on the permanent redirect from HTTP to HTTPS. Session and learner cookies are marked secure automatically when the site is served over HTTPS.

Open the site. The landing page shows "Daftar Sekarang".

## Static preview on GitHub Pages (draft)

`docs/` holds a static copy of the portal for previewing before the Plesk deployment: landing page, registration form (client-side checks only), module viewer, the module quizzes (locked in the same order as the portal) and a certificate PDF generated in the browser. It is plain HTML, CSS and JavaScript with no backend. Nothing is sent to a server; progress is kept in the browser (sessionStorage, or localStorage when "Ingat saya" is ticked, as described below), and every page shows "Draf / pratonton — data tidak disimpan". The quiz answers are visible in `docs/assets/js/app.js`, so use it for preview only.

To publish it: on GitHub open the repository → Settings → Pages → Build and deployment → Source: **Deploy from a branch** → Branch: **main**, folder **/docs** → Save. After a minute the preview is at `https://raimi-akasyah.github.io/nadi/`.

To try it locally: `& 'C:\xampp 8.2\php\php.exe' -S 127.0.0.1:8090 -t docs`, then open http://127.0.0.1:8090.

The course structure, slide titles and quiz questions in `docs/assets/js/app.js` are copied from `content/course.php` and `content/quiz.php`, and the slide images in `docs/assets/slides/` are copies of `public/assets/slides/`. Update both when the content changes.

## Editing the content

- **Slides and modules:** edit `content/course.php`. Each section has lessons, and each presentation lesson has `slides` of `['title' => ..., 'image' => 'assets/slides/page-NN.webp']`. The images are pages of the course deck PDF ("NADI _AWS Training Slide Deck"), rendered as 1600x900 WebP and named by PDF page number (`page-01.webp`, `page-07.webp`, ...). They live in `public/assets/slides/`, with identical copies in `docs/assets/slides/` for the static preview. The `title` is the image's alt text. A slide without `image` shows `title` and `body` as text. Keep the lesson `key` values unchanged once learners have started, because progress is stored by key.

  | Section | PDF pages | Quiz at the end |
  | --- | --- | --- |
  | Pengenalan | 1, 7 | |
  | Modul 1: Kesedaran AI & Keyakinan Digital | 8-10 | Kuiz Modul 1 (3 questions) |
  | Modul 2: Asas AI | 11-14 | Kuiz Modul 2 (2 questions) |
  | Modul 3: Kemahiran Praktikal AI | 15-23 | Kuiz Modul 3 (2 questions) |
  | Modul 4: AI untuk Perniagaan Mikro & Produktiviti | 24-35 | Kuiz Modul 4 (1 question) |
  | Modul 5: AI Bertanggungjawab & Kesediaan Masa Hadapan | 36-42 | Kuiz Modul 5 (2 questions) |
  | Penutup | 45 | |

  Pages 2-6, 43 and 44 of the deck are not used. Each module's quiz is the last lesson of its section, e.g. `['key' => 'kuiz-modul-1', 'title' => 'Kuiz Modul 1', 'type' => 'quiz', 'quiz' => 'modul-1']`.
- **Quiz questions:** edit `content/quiz.php`. `quizzes` has one list per module (`modul-1` to `modul-5`), each with a `title` and its `questions`. Each question has an `id`, `text`, four `options` (`a` to `d`) and the correct `answer`. To add a question to a module, append it to that module's list with an `id` that is unique across all quizzes (e.g. `q11`) and an `answer` of `a` to `d`, then copy it to `QUIZ` in `docs/assets/js/app.js`. Correct answers are never sent to the browser. At least 4 questions per module are recommended: with 1 to 3 questions a pass mark of 75% means every answer must be right.
- **Semakan Jawapan (answer review):** every question has an `explanation`. After each attempt the result page lists every question with the learner's answer. A wrong answer gets its explanation and a link to the module's lesson for study; the correct option itself is shown only after the quiz is passed, when every explanation is shown. Copy new explanations to `QUIZ` in `docs/assets/js/app.js` too.
- **Pass mark:** change `pass_mark` in `content/quiz.php` (default 75, the same for every module quiz). A learner passes a quiz when `correct * 100 / total >= pass_mark`.
- **Which modules unlock:** the order of the sections in `content/course.php`. Sections before the first quiz (Pengenalan) are always open. Modul 1 is open from the start; each later module (its slides and its quiz) opens when the quiz of the module before it is passed. A module's quiz opens when that module's slide lessons are done. Penutup, "LENGKAP & TERUSKAN" and the certificate need all five quizzes passed. The server enforces this: a locked lesson answers HTTP 403 with a message and a link to what to do first (`Course::lockReason()` in `src/Course.php`; the draft has a copy in `lockReason()` in `docs/assets/js/app.js`).
- **Certificate:** the whole design (logos, "SIJIL PENYERTAAN", wording, signature, badge) is one picture, `public/assets/certificate/template.png`, drawn over the full A4 landscape page. The PDF covers the "[ NAMA PESERTA ]" placeholder with a white rectangle and prints the learner's name there (shrunk, or split over two lines, when it is long), and prints the date and certificate number at the bottom left. All positions are in millimetres in `content/certificate.php`. To find a position on a new template, read the pixel position in an image editor and convert it: mm = px × 297 / image width in px. To change the design, replace `template.png` and adjust those numbers. The static preview uses a copy in `docs/assets/certificate/template.png` and a copy of the numbers in `CERT` in `docs/assets/js/app.js`, so update both. The current template is 1123 × 794 px, which is 96 dpi on A4: it looks fine on screen but will be slightly soft when printed. For sharp prints, export the same design at 3508 × 2480 px (300 dpi) and replace both copies; the coordinates in mm stay the same. To check a change, run `& 'C:\xampp 8.2\php\php.exe' tests/render-cert.php <folder>` (writes `cert-php.pdf` and `cert-php-long.pdf`, no database needed) and, for the preview, `node tests/docs-check.mjs` with the environment variable `NADI_CERT_OUT=<folder>` (writes `cert-docs.png` and `cert-docs-long.png`). `certificate.issuer` in `config/config.php` is only the PDF author. The drawing code is in `src/CertificatePdf.php`.
- **Design and brand assets:** the portal and the static preview share one stylesheet, so `public/assets/css/app.css` and `docs/assets/css/app.css` must stay byte-identical: edit the one in `public/` and copy it over. The mascot (`mascot-wave-*.webp`, `mascot-cheer.webp`), the AWS and Pepper Labs logos (`logo-aws.webp`, `logo-pepperlabs.webp`) and the orange and amber shapes (`blob-*.svg`) in `assets/img/` were cut from the course deck PDF; `docs/assets/img/` holds copies. The fonts Fredoka and Nunito are self-hosted WOFF2 files in `assets/fonts/` (SIL Open Font License, licence texts `OFL-Fredoka.txt` and `OFL-Nunito.txt` next to them), so no external font service is used. The page markup lives in `views/*.php` for the portal and in the HTML strings of `docs/assets/js/app.js` for the preview: change both when the design changes.

## Registration form and NADI centres

The form asks for Nama Penuh, Umur, Jantina (Lelaki / Perempuan), E-mel, Pekerjaan and Pusat NADI. Phone number and address are no longer asked.

- **Pekerjaan** is a list in `content/registration.php` (Pelajar, Suri Rumah, Usahawan, Pekerja Gig, Petani / Nelayan, Pesara and so on, matching who NADI centres serve), plus "Lain-lain", which shows a text field. The typed text is stored as the occupation.
- **Pusat NADI** is chosen in three steps: Negeri, then Daerah (only that state's districts), then the centre (only that district's centres), with "Lain-lain (tiada dalam senarai)" at the end, which shows a text field for the centre's name. The server checks that the district belongs to the state and the centre to the district. `learners.nadi_code` holds the centre's site code (e.g. `PIK_0386`), empty for "Lain-lain"; `nadi_name` holds the name.
- **The list** is `content/nadi-centers.csv` (Kod, Nama NADI, Daerah, Negeri): 1099 centres in 16 states and 152 districts, taken from the official [Lokasi NADI](https://www.nadi.my/lokasi-nadi) page. To refresh it, run `php bin/update-nadi-centers.php`; it reads the JSON that page loads, keeps only the code, name, district and state (no staff names or phone numbers), and also writes `docs/assets/js/nadi-centers.js` for the preview. Perlis centres without a district in the source get the district "Perlis"; two centres with the same name in one district get their code added to the name.
- **Without JavaScript** the page lists every district and centre grouped by state and district, so the form still works. `public/assets/js/register.js` (a byte copy is in `docs/assets/js/register.js`) narrows the lists and shows the "Lain-lain" fields.
- The admin panel shows each learner's gender and centre, searches by centre name, and exports Jantina, Negeri, Daerah, Kod NADI and Pusat NADI in the CSV.

## Panel admin

`https://your-domain/admin.php` is for the organisers. It shows the number of learners (and new ones in the last 7 days), certificates issued and through which track, how many learners started each track, per-quiz figures (learners who tried, passed, attempts, pass rate), the questions answered wrong most often (from the latest 5000 attempts), and the learner list with search (name, e-mail, phone), a filter (all / with certificate / without) and 50 learners per page. "Eksport CSV" downloads the current search and filter as a UTF-8 CSV that opens in Excel; cells that start with `=`, `+`, `-` or `@` get a leading apostrophe so a spreadsheet never runs them as formulas.

- **Turn it on.** Run `php bin/admin-password.php`, type a password of at least 12 characters, and put the printed hash in `config/config.php` as `'admin' => ['password_hash' => '...']` (single quotes), or in the environment variable `ADMIN_PASSWORD_HASH`. Only the hash is stored. Without a valid hash `admin.php` answers 404.
- **Protection.** CSRF token on sign-in and sign-out, a new session id on sign-in, sign-out after 30 minutes without activity (`ADMIN_IDLE_MINUTES` in `src/admin.php`), and 10 failed passwords per IP address per 15 minutes; after that even the right password is refused (HTTP 429). Failed sign-ins are counted in `resume_attempts` under an IP hash of their own, so they do not use up Sambung Semula for learners on the same IP. The page sends `noindex`, `no-store` and `X-Frame-Options: DENY`. The panel has no link from the public pages.

## Self-Learning (video)

The Self-Learning track in `content/self-learning.php` has the same frame as E-Learning: Pengenalan (a video, always open), one module with the main video (`'type' => 'video'`, open from the start) followed by Kuiz Self-Learning, and Penutup (a video, opens when Kuiz Self-Learning is passed). That quiz is `'self-learning'` in `content/quiz.php`; it has no questions of its own and asks the questions of the module quizzes listed in `'from'`, so a new module question appears in both tracks. Both tracks use the course key `ai-essentials`, so they share `completions`, the certificate and `verify.php`. After a completion from either track, every lesson of both tracks is open. Before that, passing Kuiz Self-Learning does not unlock E-Learning modules, and passing module quizzes does not pass Kuiz Self-Learning.

- **Video hosting.** Upload the video to YouTube as **Unlisted** and put its 11-character id (from `https://youtu.be/<id>`) in `'youtube'`, and its length in seconds in `'duration'`. Copy both to `SELF` in `docs/assets/js/app.js`. The three current videos are **placeholders** (Blender Foundation open movies, CC BY: Caminandes 2 for Pengenalan, Big Buck Bunny for the module, Caminandes 3 for Penutup).
- **Light page.** The lesson first shows a poster button made from the site's own assets. Only a click loads the YouTube IFrame API and the player from `youtube-nocookie.com`, so nothing is fetched from YouTube before the learner presses play. The video is streamed by YouTube, not by the Plesk server.
- **How much to watch.** `VIDEO_MIN_WATCH_SECONDS` in `src/helpers.php` (now **10**, with a copy in `docs/assets/js/app.js`) is the number of seconds of playback, from any part of the video, after which "LENGKAP & TERUSKAN" is enabled. Skipping is allowed, but a jump does not count as watched; speeds up to 2x are allowed. The seconds played are kept in `sessionStorage` (under a key tied to the learner and the session), so a reload keeps them. On the server, `lesson-complete.php` refuses the video lesson (HTTP 403) until that many seconds have passed since the learner first opened it in this session (`video_watched_long_enough()`). Set `VIDEO_MIN_WATCH_SECONDS` to `0` to require the whole video instead: then skipping ahead past the furthest point watched jumps back, the button is enabled only when the video ends, and the server waits `duration / VIDEO_MAX_SPEED` seconds. In both modes a completed video can be rewatched freely, and the page sends the keep-alive while the video plays, so a long video does not hit the 30-minute idle timeout.
- **Switching.** The course sidebar has an E-Learning / Self-Learning switch (`course.php?track=...`), and the landing page shows both tracks' contents under tabs (`public/assets/js/tabs.js`; without JavaScript both lists are shown). `course.php` without `?lesson=` or `?track=` opens the track last used in this session (E-Learning by default).

## Continuing later: Sambung Semula

Progress (lessons done, module quiz passes and certificate) is stored in the database against the learner, not in the browser. To continue, the learner opens the site, goes to "Sambung Semula" (on the landing page, or the link on the registration page, `resume.php`), and enters the full name or the e-mail they registered with (one field; anything with "@" is treated as an e-mail). No code or password is asked for.

- **By full name.** The name must match the registered one; letter case and extra spaces do not matter. Two different people can share a name: when a name belongs to learners with more than one e-mail address, nobody is resumed and the page answers "Lebih daripada seorang peserta berdaftar dengan nama ini. Sila Sambung Semula dengan e-mel..." (HTTP 409), so no one sees another person's progress or certificate.

- **Same device, next day.** If "Ingat saya" was ticked, the site opens straight at their progress. If not, or after "Selesai / Keluar" or the 30-minute timeout, they enter their e-mail in "Sambung Semula" and continue where they stopped.
- **Different device.** They enter their e-mail in "Sambung Semula" and get the same progress, quiz results (which module quizzes are passed) and certificate. Nothing is reset.
- **Name or e-mail not found.** The page says "Tiada peserta berdaftar dengan nama atau e-mel ini" and offers registration.
- **Registering again with the same e-mail** does not create a second learner. It resumes the existing one and says "E-mel ini sudah berdaftar, jadi kemajuan anda yang sedia ada telah disambung semula", with the name that will appear on the certificate. The details typed in the new form are not saved; the original name stays on the certificate.
- **Several learners with one e-mail** (left from earlier testing): the one with the most progress is resumed. The order is a course completion first, then a passed quiz, then the most completed lessons, and the newest record on a tie. No rows are merged or deleted.

E-mail matching ignores letter case and surrounding spaces. Anyone who knows a learner's full name or e-mail can resume as that learner and download their certificate. A name is easier to know than an e-mail, so this is weaker than e-mail only; it was a deliberate choice to keep the course free of codes and passwords.

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

# An existing local database from before the module quizzes: run the migration (safe to run twice)
Get-Content -Raw sql/migrate-module-quizzes.sql | & 'C:\xampp 8.2\mysql\bin\mysql.exe' --no-defaults -h 127.0.0.1 -u root nadi_lms
# ...the single "Nama Penuh" field, then Jantina and Pusat NADI (each safe to run twice)
Get-Content -Raw sql/migrate-full-name.sql | & 'C:\xampp 8.2\mysql\bin\mysql.exe' --no-defaults -h 127.0.0.1 -u root nadi_lms
Get-Content -Raw sql/migrate-registration-fields.sql | & 'C:\xampp 8.2\mysql\bin\mysql.exe' --no-defaults -h 127.0.0.1 -u root nadi_lms

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

# Unit tests (no database needed): dates, course structure, module locking, quiz data, validation, grading, PDF
& 'C:\xampp 8.2\php\php.exe' tests/unit.php

# End-to-end test (needs the database and the dev server running)
& 'C:\xampp 8.2\php\php.exe' tests/e2e.php http://127.0.0.1:8080

# docs/ draft in headless Chrome or Edge, served under /nadi/ like GitHub Pages (needs Node 22+)
node tests/docs-check.mjs
```

The e2e test registers new learners with unique `e2e...+<time>@example.com` e-mails, so it leaves test rows in the local database. Run `sql/migrate-module-quizzes.sql` and `sql/migrate-full-name.sql` on the local database first (see Local development). Steps 48 to 51 cover the registration fields: every state, district and centre on the form, wrong combinations refused, "Lain-lain" saved as typed, and `sql/migrate-registration-fields.sql` imported twice on a scratch database `<db>_regtest`. Steps 43 to 47 cover the admin panel and run only when the server has an admin hash and `NADI_ADMIN_PASSWORD` holds its password (otherwise they print SKIP): sign-in, dashboard, search and filter, CSV export, sign-out and the sign-in limit. Steps 38 to 42 cover the full name: Sambung Semula by name (any letter case), an unknown name, a name shared by two e-mails (409), the single Nama Penuh field, and `sql/migrate-full-name.sql` imported twice on a scratch database `<db>_nametest` in the old format. Steps 3 to 10 cover the module quizzes: Kuiz Modul 1 locked (403) before the Modul 1 lesson, Modul 2 locked before Kuiz Modul 1 is passed, no correct answers in the quiz page, a failing attempt (2/3) and a passing retry, and completion, certificate and Penutup refused until all five quizzes are passed. Steps 11 to 19 cover the certificate, verification and shared computers: a learner who finishes and presses "Selesai / Keluar", a closed browser, the idle timeout and the "Ingat saya" cookie. Steps 20 to 28 cover Sambung Semula: progress and per-module quiz status made on one device (cookie jar) restored on a fresh one and finished there with a certificate in the learner's name, the not-found message, registering again with the same e-mail, duplicate e-mails, 80 minutes in one module kept alive by `keepalive.php`, "Ingat saya" on two devices, and the rate limit. Steps 32 to 37 cover Self-Learning: the video page without an iframe, the video lesson refused before `VIDEO_MIN_WATCH_SECONDS` and saved after (the test ages the session), Kuiz Self-Learning failing at 7/10 and passing at 8/10, the shared certificate, and `complete.php` with a wrong track. Steps 29 and 30 cover learners from before the module quizzes (a passed `kuiz-akhir` attempt, and an existing completion). Step 31 creates a scratch database `<db>_migtest` in the old format, imports `sql/migrate-module-quizzes.sql` twice, checks that the old attempt is tagged `kuiz-akhir` and the completion is unchanged, and drops it; it runs the `mysql` client from `MYSQL_BIN` (default `C:\xampp 8.2\mysql\bin\mysql.exe`) with the credentials in `config/config.php`. The idle-timeout steps age the dev server's session file, so run the test on the same machine as the server. The test clears rate-limit rows for 127.0.0.1 before and after it runs.

`tests/docs-check.mjs` starts its own static server for `docs/` under `/nadi/` and a headless browser (Chrome, Edge, or `CHROME_PATH`). It fails on any console error. It covers the module quizzes (a failed Kuiz Modul 1, the Modul 2 lock card, a passing retry, Kuiz Modul 2 to 5 in order and the certificate refused before Kuiz Modul 5 is passed), the shared-computer flow, the idle timeout, long reading with slide clicks, the 30-day "Ingat saya" expiry, dropping data from the old draft, and the Sambung Semula note.
