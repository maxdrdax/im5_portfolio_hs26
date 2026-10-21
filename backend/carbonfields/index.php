<?php
/**
 * Alles, was mit Carbon Fields zu tun hat, ist in diesem Ordner verwaltet.
 * Wenn ein neuer Post Type oder eine Taxonomie spezifische Carbon Fields
 * benötigen, kann eine neue Konfigurationsdatei in diesem Ordner erstellt
 * und in diese Datei importiert werden.
 *
 * Dokumentation: https://docs.carbonfields.net/
 */

add_action('carbon_fields_register_fields', function () {
    require_once __DIR__ . '/_globals.php';
    require_once __DIR__ . '/_modules.php';
});
