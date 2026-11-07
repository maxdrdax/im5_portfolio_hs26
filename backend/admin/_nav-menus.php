<?php
/**
 * Backend «Design > Menüs»: einfacher Hintergründe fürs Overlay-Menü pflegen
 * 1. Vorschaubild (menu_image) in der zugeklappten Zeile jedes Menüeintrags
 * 2. Button «Alle aufklappen / Alle zuklappen» über der Liste
 * Die Felder selbst stehen in backend/carbonfields/_nav.php (Bild und Video nebeneinander).
 *
 * Dokumentation:
 * https://developer.wordpress.org/reference/hooks/wp_nav_menu_item_custom_fields/
 */

// -> 1. Vorschaubild-URL pro Menüeintrag mitgeben
// Der Hook gibt Inhalt im aufgeklappten Bereich des Eintrags aus. Wir geben nur ein verstecktes
// Daten-Element aus; nav-menus.js setzt das Bild dann in die zugeklappte Zeile.
add_action('wp_nav_menu_item_custom_fields', function ($item_id): void {
    $image_id = (int) carbon_get_nav_menu_item_meta((int) $item_id, 'menu_image');
    if (!$image_id) {
        return;
    }
    $image = wp_get_attachment_image_src($image_id, 'xxs');
    if (!$image) {
        return;
    }
    echo '<span class="portfolio-menu-thumb-data" data-thumb="' . esc_url($image[0]) . '" hidden></span>';
});

// -> 2. CSS und JS nur auf der Seite «Design > Menüs» laden
add_action('admin_enqueue_scripts', function (string $hook): void {
    if ($hook !== 'nav-menus.php') {
        return;
    }
    $dir = get_template_directory();
    $uri = get_template_directory_uri();

    wp_enqueue_style(
        'portfolio-admin-nav-menus',
        $uri . '/backend/admin/nav-menus.css',
        [],
        (string) filemtime($dir . '/backend/admin/nav-menus.css')
    );
    // Abhängigkeit «nav-menu»: das Menü-Script von WordPress (Auf-/Zuklappen der Einträge)
    wp_enqueue_script(
        'portfolio-admin-nav-menus',
        $uri . '/backend/admin/nav-menus.js',
        ['nav-menu'],
        (string) filemtime($dir . '/backend/admin/nav-menus.js'),
        true
    );
});
