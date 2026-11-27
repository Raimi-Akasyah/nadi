<?php
/**
 * Choices on the registration form. The labels are stored as they are in the learners table, so do not rename an
 * existing one once learners have registered (add a new one instead).
 *
 * NADI centres (Negeri -> Daerah -> Pusat NADI) come from content/nadi-centers.csv, made by
 * `php bin/update-nadi-centers.php` from https://www.nadi.my/lokasi-nadi.
 *
 * Every list also gets "Lain-lain" (REG_OTHER in src/registration.php), which shows a text field.
 *
 * Keep GENDERS and OCCUPATIONS in docs/assets/js/app.js in sync with this file.
 */
return [
    'genders'     => ['Lelaki', 'Perempuan'],
    // NADI centres serve rural and suburban communities: students, housewives, micro entrepreneurs, farmers and
    // fishermen, gig workers, retirees and job seekers who want digital and AI skills for income and productivity.
    'occupations' => [
        'Pelajar / Penuntut',
        'Suri Rumah',
        'Usahawan / Peniaga',
        'Bekerja Sendiri / Pekerja Bebas (Freelance)',
        'Pekerja Gig (e-hailing, p-hailing, penghantaran)',
        'Petani / Penternak / Nelayan',
        'Kakitangan Kerajaan',
        'Kakitangan Swasta',
        'Pesara',
        'Tidak Bekerja / Mencari Pekerjaan',
    ],
];
