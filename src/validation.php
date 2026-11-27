<?php
declare(strict_types=1);

/** Longest accepted full name (learners.full_name is VARCHAR(200); the certificate fits it on two lines). */
const FULL_NAME_MAX = 150;

/** Length of a UTF-8 string. */
function str_len(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
}

/**
 * Validate the registration form.
 * Returns [$clean, $errors]; $errors is keyed by field name with Malay messages.
 */
function validate_registration(array $in): array
{
    $get = static function (string $k) use ($in): string {
        $v = $in[$k] ?? '';
        return is_string($v) ? trim($v) : '';
    };

    $clean = [
        'full_name'  => normalize_name($get('full_name')),
        'age'        => $get('age'),
        'email'      => $get('email'),
        'phone'      => $get('phone'),
        'address'    => $get('address'),
        'occupation' => $get('occupation'),
    ];
    $errors = [];

    if ($clean['full_name'] === '') {
        $errors['full_name'] = 'Sila masukkan nama penuh.';
    } elseif (str_len($clean['full_name']) > FULL_NAME_MAX) {
        $errors['full_name'] = 'Nama penuh tidak boleh melebihi ' . FULL_NAME_MAX . ' aksara.';
    } elseif (!preg_match('/\p{L}/u', $clean['full_name'])) {
        $errors['full_name'] = 'Nama penuh mesti mengandungi huruf.';
    }

    if ($clean['age'] === '') {
        $errors['age'] = 'Sila masukkan umur.';
    } elseif (!preg_match('/^[0-9]{1,3}$/', $clean['age']) || (int)$clean['age'] < 5 || (int)$clean['age'] > 120) {
        $errors['age'] = 'Umur mestilah nombor antara 5 hingga 120.';
    } else {
        $clean['age'] = (int)$clean['age'];
    }

    if ($clean['email'] === '') {
        $errors['email'] = 'Sila masukkan alamat e-mel.';
    } elseif (strlen($clean['email']) > 190 || filter_var($clean['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Alamat e-mel tidak sah.';
    }

    if ($clean['phone'] === '') {
        $errors['phone'] = 'Sila masukkan nombor telefon.';
    } elseif (!preg_match('/^\+?[0-9 \-]{7,20}$/', $clean['phone'])) {
        $errors['phone'] = 'Nombor telefon tidak sah (7 hingga 20 digit).';
    }

    if ($clean['address'] === '') {
        $errors['address'] = 'Sila masukkan alamat.';
    } elseif (str_len($clean['address']) > 255) {
        $errors['address'] = 'Alamat tidak boleh melebihi 255 aksara.';
    }

    if ($clean['occupation'] === '') {
        $errors['occupation'] = 'Sila masukkan pekerjaan.';
    } elseif (str_len($clean['occupation']) > 100) {
        $errors['occupation'] = 'Pekerjaan tidak boleh melebihi 100 aksara.';
    }

    return [$clean, $errors];
}

/**
 * Validate the "Sambung Semula" field: the full name or the e-mail used at registration.
 * Anything with an "@" is treated as an e-mail address.
 * Returns [$kind ('email' | 'name'), $value, $error]; $error is a Malay message or null.
 */
function validate_resume_login(array $in): array
{
    $raw = $in['login'] ?? ($in['email'] ?? null);
    $value = is_string($raw) ? normalize_name($raw) : '';
    if ($value === '') {
        return ['name', $value, 'Sila masukkan nama penuh atau e-mel yang anda gunakan semasa mendaftar.'];
    }
    if (strpos($value, '@') !== false) {
        if (strlen($value) > 190 || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return ['email', $value, 'Alamat e-mel tidak sah.'];
        }
        return ['email', $value, null];
    }
    if (str_len($value) > FULL_NAME_MAX || !preg_match('/\p{L}/u', $value)) {
        return ['name', $value, 'Nama penuh tidak sah.'];
    }
    return ['name', $value, null];
}

/** Trim a name and collapse runs of whitespace to one space ("  Siti   Aminah " -> "Siti Aminah"). */
function normalize_name(string $s): string
{
    return trim((string)preg_replace('/\s+/u', ' ', $s));
}
