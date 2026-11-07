<?php
/**
 * Theme-Setup: Theme-Features aktivieren und Styles/Scripts laden.
 * Styles und Scripts werden nur über wp_enqueue_* geladen, nie direkt im <head>.
 */

// -> Theme-Features
add_action('after_setup_theme', function () {
    // WordPress setzt den <title> selbst (inkl. Seitentitel)
    add_theme_support('title-tag');
    // Sauberes HTML5-Markup für Galerien, Bildunterschriften usw.
    add_theme_support('html5', ['gallery', 'caption', 'style', 'script']);
});

// -> Styles und Scripts im Frontend laden
add_action('wp_enqueue_scripts', function () {
    $dir = get_template_directory();
    $uri = get_template_directory_uri();

    // Haupt-Stylesheet; Version = Änderungsdatum, damit der Browser-Cache nach Updates neu lädt.
    // Die Schriften sind lokal eingebunden (frontend/styles/_fonts.css, Dateien in frontend/assets/fonts/).
    wp_enqueue_style(
        'portfolio-main',
        $uri . '/frontend/styles/main.css',
        [],
        (string) filemtime($dir . '/frontend/styles/main.css')
    );

    // Haupt-Script als ES-Modul (type="module")
    wp_enqueue_script_module(
        'portfolio-main',
        $uri . '/frontend/scripts/main.js',
        [],
        (string) filemtime($dir . '/frontend/scripts/main.js')
    );
});

// -> Wichtigste Schriften vorab laden (preload): Sie sind sofort sichtbar (Text, Logo im Header).
// Ohne preload entdeckt der Browser sie erst, nachdem main.css und _fonts.css geladen sind.
// crossorigin ist bei Schriften Pflicht, sonst lädt der Browser die Datei doppelt.
add_action('wp_head', function () {
    $fonts = [
        'geist-latin.woff2',
        'playfair-display-italic-latin.woff2',
    ];
    foreach ($fonts as $font) {
        $url = get_template_directory_uri() . '/frontend/assets/fonts/' . $font;
        echo '<link rel="preload" href="' . esc_url($url) . '" as="font" type="font/woff2" crossorigin>' . "\n";
    }
}, 1);
