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

    // Form values (shown again after an error), then the values stored: occupation_text, nadi_code, nadi_name.
    $clean = [
        'full_name'        => normalize_name($get('full_name')),
        'age'              => $get('age'),
        'gender'           => $get('gender'),
        'email'            => $get('email'),
        'occupation'       => $get('occupation'),
        'occupation_other' => normalize_name($get('occupation_other')),
        'state'            => $get('state'),
        'district'         => $get('district'),
        'nadi'             => $get('nadi'),
        'nadi_other'       => normalize_name($get('nadi_other')),
    ];
    $errors = [];
    $choices = registration_choices();

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

    if (!in_array($clean['gender'], $choices['genders'], true)) {
        $errors['gender'] = 'Sila pilih jantina.';
    }

    // Occupation: one of the list, or "Lain-lain" with the occupation typed in.
    $clean['occupation_text'] = '';
    if ($clean['occupation'] === REG_OTHER) {
        if ($clean['occupation_other'] === '') {
            $errors['occupation_other'] = 'Sila nyatakan pekerjaan anda.';
        } elseif (str_len($clean['occupation_other']) > 100) {
            $errors['occupation_other'] = 'Pekerjaan tidak boleh melebihi 100 aksara.';
        } else {
            $clean['occupation_text'] = $clean['occupation_other'];
        }
    } elseif (in_array($clean['occupation'], $choices['occupations'], true)) {
        $clean['occupation_text'] = $clean['occupation'];
    } else {
        $errors['occupation'] = 'Sila pilih pekerjaan.';
    }

    // Pusat NADI: Negeri -> Daerah -> a centre of that district, or "Lain-lain" with the centre typed in.
    $clean['nadi_code'] = '';
    $clean['nadi_name'] = '';
    if (!in_array($clean['state'], nadi_states(), true)) {
        $errors['state'] = 'Sila pilih negeri.';
    } elseif (!in_array($clean['district'], nadi_districts($clean['state']), true)) {
        $errors['district'] = 'Sila pilih daerah.';
    } elseif ($clean['nadi'] === REG_OTHER) {
        if ($clean['nadi_other'] === '') {
            $errors['nadi_other'] = 'Sila tulis nama pusat NADI anda.';
        } elseif (str_len($clean['nadi_other']) > REG_OTHER_MAX) {
            $errors['nadi_other'] = 'Nama pusat NADI tidak boleh melebihi ' . REG_OTHER_MAX . ' aksara.';
        } else {
            $clean['nadi_name'] = $clean['nadi_other'];
        }
    } else {
        $centers = nadi_district_centers($clean['state'], $clean['district']);
        if (isset($centers[$clean['nadi']])) {
            $clean['nadi_code'] = $clean['nadi'];
            $clean['nadi_name'] = $centers[$clean['nadi']];
        } else {
            $errors['nadi'] = 'Sila pilih pusat NADI, atau pilih Lain-lain jika tiada dalam senarai.';
        }
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
