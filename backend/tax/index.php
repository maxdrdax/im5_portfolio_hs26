<?php
/**
* Taxonomien werden in diesem Ordner verwaltet. Für jede Taxonomie muss eine
* neue Datei erstellt werden und in dieser Datei importiert werden. Der Dateiname
* sollte dem Slug der Taxonomie entsprechen.
*
* Dokumentation: https://developer.wordpress.org/reference/functions/register_taxonomy/
*/

add_action('init', function () {
    // require_once __DIR__ . '/_taxonomy.php';
}, 0);
