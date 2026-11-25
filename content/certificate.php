<?php
/**
 * Certificate template and the positions of the printed fields (SIJIL PENYERTAAN).
 *
 * The whole design (logos, title, wording, signature, badge) is one picture:
 * 'template' (path relative to public/). It fills the full A4 landscape page.
 * Only the learner's name, the date and the certificate number are printed on top.
 *
 * Units: every position and size below is in millimetres on A4 landscape
 * (297 x 210 mm, origin top-left), except font sizes, which are in points.
 *
 * Measuring a spot on the template: open the PNG in an image editor and read the
 * pixel position, then mm = px * 297 / image width in px (1123 px today, so
 * 1 px = 0.26447 mm; the same factor applies vertically).
 *
 * - 'name.patch' [x, y, w, h] is filled with 'name.patch_color' to hide the
 *   "[ NAMA PESERTA ]" placeholder. The name is centred on the page at
 *   'name.center_y', shrunk from 'max_size' to 'min_size' to fit 'max_width',
 *   and split over two smaller lines when it is still too long.
 * - 'details' places the date line and the certificate number line under it,
 *   left-aligned at x.
 *
 * Swapping the design: replace public/assets/certificate/template.png and edit
 * these numbers. The static preview in docs/ has a copy of the picture
 * (docs/assets/certificate/template.png) and of these numbers (CERT in
 * docs/assets/js/app.js). Update both.
 */
return [
    'template' => 'assets/certificate/template.png',
    'name' => [
        'patch'       => [104, 70, 88, 12],
        'patch_color' => [255, 255, 255],
        'center_y'    => 76,
        'max_width'   => 250,
        'max_size'    => 26,
        'min_size'    => 12,
        'color'       => [11, 60, 110],
    ],
    'details' => [
        'x'           => 62,
        'y'           => 155.5,
        'line_height' => 5,
        'size'        => 8.5,
        'color'       => [80, 80, 80],
    ],
    'date_label' => 'Tarikh',
    'code_label' => 'No. Sijil',
];
