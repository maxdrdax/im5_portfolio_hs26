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

    // Google Fonts (Geist, Playfair Display)
    // Hinweis Datenschutz: Für den Livegang besser lokal in frontend/assets/fonts/ hosten.
    wp_enqueue_style(
        'portfolio-fonts',
        'https://fonts.googleapis.com/css2?family=Geist:wght@400;500&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap',
        [],
        null
    );

    // Haupt-Stylesheet; Version = Änderungsdatum, damit der Browser-Cache nach Updates neu lädt
    wp_enqueue_style(
        'portfolio-main',
        $uri . '/frontend/styles/main.css',
        ['portfolio-fonts'],
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
