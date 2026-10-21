<?php
/**
 * In diesem Ordner werden Custom Post Types verwaltet.
 * Für jeden Custom Post Type muss eine neue Datei erstellt werden
 * und in dieser Datei hinzugefügt werden. Der Dateiname sollte
 * dem Slug des Post Typs entsprechen.
 *
 * Dokumentation: https://developer.wordpress.org/reference/functions/register_post_type/
 */

add_action('init', function () {
    // require_once __DIR__ . '/_posttype.php';
}, 0);
