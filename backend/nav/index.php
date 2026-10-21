<?php
/**
 * In dieser Datei werden Navigationen / Menus registriert.
 * Um eine Navigation im Backend erstellen zu können, muss diese
 * hier registriert werden.
 *
 * Dokumentaion: https://developer.wordpress.org/reference/functions/register_nav_menus/
 */

add_filter('init', function () {
    register_nav_menus([
        // todo: Menus hier registrieren
    ]);
});
