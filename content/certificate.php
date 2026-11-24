<?php
/**
 * Certificate wording and artwork (SIJIL PENYERTAAN).
 *
 * How to change it:
 * - Text: edit the strings below. The learner's name, the date and the certificate
 *   number are filled in automatically.
 * - 'summary' is printed as one line in three parts: normal, bold, normal.
 * - 'modules' is printed one entry per line.
 * - Images: paths are relative to public/. To change the artwork, replace the PNG
 *   files in public/assets/certificate/ (keep the same names) or point these paths
 *   at new files. background.png fills the whole A4 landscape page (297 x 210 mm),
 *   logos.png is drawn 18 mm high at the top, badge.png 28 mm wide at the bottom right.
 * - The static preview in docs/ has a copy of this text (CERT in docs/assets/js/app.js)
 *   and of the images (docs/assets/certificate/). Update both.
 * - The layout (positions, fonts, colours) is in src/CertificatePdf.php.
 */
return [
    'background' => 'assets/certificate/background.png',
    'logos'      => 'assets/certificate/logos.png',
    'badge'      => 'assets/certificate/badge.png',
    'title'      => 'SIJIL PENYERTAAN',
    'intro'      => 'Dengan ini disahkan bahawa',
    'completed'  => 'telah berjaya untuk menamatkan program',
    'program'    => 'AI FOR COMMUNITY@NADI',
    'focus'      => 'Program yang menfokuskan kepada literasi AI, produktiviti dan penggunaan AI yang bertanggungjawab',
    'summary'    => ['Kursus ini merangkumi ', '3 jam', ' pembelajaran intensif bagi modul-modul berikut:'],
    'modules'    => [
        '1. Kesedaran AI   2. Asas AI   3. Kemahiran AI Praktikal (Kejuruteraan Prompt)',
        '4. AI untuk Perniagaan Mikro & Produktiviti   5. AI Bertanggungjawab dan Kesediaan Masa Hadapan',
    ],
    'signatory'  => [
        'name'  => 'Thanaselan Rajendran',
        'title' => 'Managing Director',
        'org'   => 'Pepper Labs Sdn Bhd',
    ],
    'date_label' => 'Tarikh',
    'code_label' => 'No. Sijil',
];
