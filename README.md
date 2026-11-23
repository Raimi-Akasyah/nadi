# NADI LMS

A small learning portal for the NADI community course "Malaysia AWS AI Essentials".

Learners register once with their details (first name, last name, age, e-mail, phone, address, occupation). There is no account, password or sign-in. They then go through Modul 1 to Modul 5 in a slide viewer and take a 10-question final quiz (Kuiz Akhir). A score of at least 75% passes, and attempts are unlimited. After passing, "LENGKAP & TERUSKAN" records the course completion and the learner can download a PDF certificate with their first and last name, the completion date and a unique certificate number. Anyone can check that number on the public verification page.

Plain PHP 8 and MySQL/MariaDB. No framework, no Composer and no build step.

## Folder layout

| Folder | Contents |
| --- | --- |
| `public/` | The web root: page scripts (`index.php`, `register.php`, `course.php`, `quiz-submit.php`, `complete.php`, `certificate.php`, `certificate-download.php`, `verify.php`, ...) and `assets/` (CSS, JS, logo). |
| `src/` | Bootstrap, database connection, helpers, validation, repositories (all SQL), `Course.php`, `Quiz.php` (grading), `CertificatePdf.php`. |
| `views/` | HTML templates. |
| `content/` | `course.php` (sections, lessons, slides) and `quiz.php` (questions, answers, pass mark). |
| `config/` | `config.example.php`. Your real `config.php` goes here and is not committed. |
| `sql/` | `schema.sql`, imported once with phpMyAdmin. |
| `lib/fpdf/` | FPDF 1.86, used to build the certificate PDF. |
| `tests/` | `lint.ps1`, `unit.php`, `e2e.php`. |

Every folder except `public/` has an `.htaccess` with `Require all denied`, as a second guard in case the document root is not changed.

## Requirements

- PHP 8.0 or newer with `pdo_mysql` (and `iconv` or `mbstring`, both standard).
- MySQL 5.7+ or MariaDB 10.3+.

## Deploying on Plesk

1. **Create the database.** Websites & Domains → Databases → Add Database. Enter a database name, then create a database user and password. Note the host (usually `localhost`).
2. **Import the tables.** In the database list, click phpMyAdmin for the new database, open the Import tab, choose `sql/schema.sql` and click Import. This creates `learners`, `lesson_progress`, `quiz_attempts` and `completions`. Registrations, progress, quiz attempts and certificates are stored here and can be viewed in phpMyAdmin.
3. **Upload the files** to `httpdocs/` with File Manager (upload a zip and extract it) or with Plesk Git. The result should be `httpdocs/public`, `httpdocs/src`, `httpdocs/config` and so on.
4. **Set the document root.** Hosting Settings (Hosting & DNS → Hosting) → Document root: `httpdocs/public`. Save.
5. **Configure the database connection.** Copy `config/config.example.php` to `config/config.php` and fill in the host, database name, user and password from step 1. Alternatively, leave `config/config.php` absent and set the environment variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` in PHP Settings. Never commit `config/config.php`.
6. **PHP version.** PHP Settings → choose PHP 8.x (8.0 or newer).
7. **HTTPS.** SSL/TLS Certificates → install a free Let's Encrypt certificate and turn on the permanent redirect from HTTP to HTTPS. Session and learner cookies are marked secure automatically when the site is served over HTTPS.

Open the site. The landing page shows "Daftar Sekarang".

## Editing the content

- **Slides and modules:** edit `content/course.php`. Each section has lessons, and each presentation lesson has `slides` of `['title' => ..., 'body' => ...]`. A slide can show an image instead by adding `'image' => 'assets/slides/name.png'` and uploading the file to `public/assets/slides/`. Keep the lesson `key` values unchanged once learners have started, because progress is stored by key.
- **Quiz questions:** edit `content/quiz.php`. Each question has an `id`, `text`, four `options` (`a` to `d`) and the correct `answer`. Correct answers are never sent to the browser.
- **Pass mark:** change `pass_mark` in `content/quiz.php` (default 75). A learner passes when `correct * 100 / total >= pass_mark`.
- **Which modules unlock the quiz:** `quiz_requires` in `content/course.php`.
- **Certificate issuer text:** `certificate.issuer` in `config/config.php`. The layout is in `src/CertificatePdf.php`.

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
```

The e2e test registers a new learner with a unique `e2e+<time>@example.com` e-mail, so it leaves test rows in the local database.
