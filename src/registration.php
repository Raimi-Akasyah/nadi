<?php
declare(strict_types=1);

/** Registration choices: gender, occupation and the NADI centres (content/registration.php, content/nadi-centers.csv). */

/** Value of the "Lain-lain" choice in the occupation, district and NADI lists; it shows a text field. */
const REG_OTHER = 'lain-lain';
const REG_OTHER_LABEL = 'Lain-lain';
const REG_OTHER_MAX = 150;

/** @return array{genders: string[], occupations: string[]} */
function registration_choices(): array
{
    static $choices = null;
    if ($choices === null) {
        $choices = require APP_ROOT . '/content/registration.php';
    }
    return $choices;
}

/**
 * NADI centres as state => district => code => name, in the CSV's order (sorted by state, district, name).
 * @return array<string, array<string, array<string, string>>>
 */
function nadi_centers(): array
{
    static $tree = null;
    if ($tree !== null) {
        return $tree;
    }
    $tree = [];
    $fh = fopen(APP_ROOT . '/content/nadi-centers.csv', 'r');
    if ($fh === false) {
        throw new RuntimeException('content/nadi-centers.csv is missing');
    }
    fgetcsv($fh, 0, ',', '"', ''); // header: Kod, Nama NADI, Daerah, Negeri
    while (($row = fgetcsv($fh, 0, ',', '"', '')) !== false) {
        if (count($row) < 4 || $row[0] === '') {
            continue;
        }
        [$code, $name, $district, $state] = array_map('trim', $row);
        $tree[$state][$district][$code] = $name;
    }
    fclose($fh);
    return $tree;
}

/** @return string[] */
function nadi_states(): array
{
    return array_keys(nadi_centers());
}

/** @return string[] districts of a state (empty for an unknown state) */
function nadi_districts(string $state): array
{
    return array_keys(nadi_centers()[$state] ?? []);
}

/** @return array<string, string> code => name for a district (empty when unknown) */
function nadi_district_centers(string $state, string $district): array
{
    return nadi_centers()[$state][$district] ?? [];
}

/**
 * The NADI part of a learner row for display: "NADI Bachok, Bachok, Kelantan". Empty for learners from before
 * these fields.
 */
function learner_nadi_label(array $learner): string
{
    return implode(', ', array_filter([
        trim((string)($learner['nadi_name'] ?? '')),
        trim((string)($learner['district'] ?? '')),
        trim((string)($learner['state'] ?? '')),
    ], 'strlen'));
}
