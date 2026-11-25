<?php
/** Inline SVG icons for the views (decorative: aria-hidden, no text). */
if (!function_exists('icon')) {
    function icon(string $name, string $class = ''): string
    {
        static $paths = [
            'alert'    => '<circle cx="12" cy="12" r="9.5"/><path d="M12 7.5v5.5M12 16.5h.01"/>',
            'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
            'award'    => '<circle cx="12" cy="9" r="6"/><path d="m8.5 13.8-1.5 7.7 5-3 5 3-1.5-7.7"/>',
            'back'     => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
            'book'     => '<path d="M2 5a2 2 0 0 1 2-2h5a3 3 0 0 1 3 3v15a2 2 0 0 0-2-2H2z"/><path d="M22 5a2 2 0 0 0-2-2h-5a3 3 0 0 0-3 3v15a2 2 0 0 1 2-2h8z"/>',
            'check'    => '<path d="M20 6 9 17l-5-5"/>',
            'clipboard' => '<rect x="5" y="4" width="14" height="18" rx="2"/><path d="M9 4V2.5h6V4"/><path d="m9 13 2 2 4-4"/>',
            'download' => '<path d="M12 3v12M7 10l5 5 5-5"/><path d="M5 21h14"/>',
            'expand'   => '<path d="M8 3H5a2 2 0 0 0-2 2v3M21 8V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3M16 21h3a2 2 0 0 0 2-2v-3"/>',
            'flag'     => '<path d="M5 21V4"/><path d="M5 4h12l-2.5 4.5L17 13H5"/>',
            'gift'     => '<rect x="3" y="8" width="18" height="5" rx="1"/><path d="M19 13v8H5v-8M12 8v13"/><path d="M12 8H8a2.5 2.5 0 1 1 0-5c3 0 4 5 4 5zM12 8h4a2.5 2.5 0 1 0 0-5c-3 0-4 5-4 5z"/>',
            'home'     => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/>',
            'info'     => '<circle cx="12" cy="12" r="9.5"/><path d="M12 11v6M12 7.5h.01"/>',
            'list'     => '<path d="M9 6h12M9 12h12M9 18h12"/><path d="M4 6h.01M4 12h.01M4 18h.01"/>',
            'lock'     => '<rect x="4" y="11" width="16" height="10" rx="2.5"/><path d="M8 11V7.5a4 4 0 0 1 8 0V11"/><path d="M12 15v2"/>',
            'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3.5 7 8.5 6 8.5-6"/>',
            'next'     => '<path d="m9 18 6-6-6-6"/>',
            'prev'     => '<path d="m15 18-6-6 6-6"/>',
            'quiz'     => '<circle cx="12" cy="12" r="9.5"/><path d="M9.2 9.2a2.9 2.9 0 0 1 5.6 1c0 1.9-2.8 2.6-2.8 2.6"/><path d="M12 16.8h.01"/>',
            'refresh'  => '<path d="M3 12a9 9 0 0 1 15.4-6.4L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15.4 6.4L3 16"/><path d="M3 21v-5h5"/>',
            'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/>',
            'shield'   => '<path d="M12 3 4.5 6v5.5c0 4.6 3.2 8 7.5 9.5 4.3-1.5 7.5-4.9 7.5-9.5V6z"/><path d="m9 12 2.2 2.2L15.5 10"/>',
            'slides'   => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M12 16v4M8 20h8"/><path d="m10.5 8 4 2-4 2z"/>',
            'user'     => '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M19 8v6M16 11h6"/>',
        ];
        $cls = 'icon' . ($class !== '' ? ' ' . $class : '');
        return '<svg class="' . $cls . '" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"'
            . ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . ($paths[$name] ?? '') . '</svg>';
    }
}
