<?php
declare(strict_types=1);

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
        'first_name' => $get('first_name'),
        'last_name'  => $get('last_name'),
        'age'        => $get('age'),
        'email'      => $get('email'),
        'phone'      => $get('phone'),
        'address'    => $get('address'),
        'occupation' => $get('occupation'),
    ];
    $errors = [];

    if ($clean['first_name'] === '') {
        $errors['first_name'] = 'Sila masukkan nama pertama.';
    } elseif (str_len($clean['first_name']) > 100) {
        $errors['first_name'] = 'Nama pertama tidak boleh melebihi 100 aksara.';
    }

    if ($clean['last_name'] === '') {
        $errors['last_name'] = 'Sila masukkan nama akhir.';
    } elseif (str_len($clean['last_name']) > 100) {
        $errors['last_name'] = 'Nama akhir tidak boleh melebihi 100 aksara.';
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
 * Validate the "Sambung Semula" e-mail field.
 * Returns [$email, $error]; $error is a Malay message or null.
 */
function validate_resume_email(array $in): array
{
    $email = is_string($in['email'] ?? null) ? trim($in['email']) : '';
    if ($email === '') {
        return [$email, 'Sila masukkan alamat e-mel yang anda gunakan semasa mendaftar.'];
    }
    if (strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return [$email, 'Alamat e-mel tidak sah.'];
    }
    return [$email, null];
}
