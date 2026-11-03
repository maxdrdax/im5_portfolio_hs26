<?php
/**
* Taxonomien werden in diesem Ordner verwaltet. Für jede Taxonomie muss eine
* neue Datei erstellt werden und in dieser Datei importiert werden. Der Dateiname
* sollte dem Slug der Taxonomie entsprechen.
*
* Dokumentation: https://developer.wordpress.org/reference/functions/register_taxonomy/
*/

add_action('init', function () {
    require_once __DIR__ . '/_project_category.php';
    require_once __DIR__ . '/_photo_category.php';
}, 0);

// -> Begriffe aus dem CMS-Konzept einmalig anlegen (nur im Backend, nur einmal dank Option)
add_action('admin_init', function () {
    if (get_option('portfolio_terms_seeded')) {
        return;
    }
    $terms = [
        'project_category' => ['Video', 'Animation', 'Print', 'Fotos', 'Digital'],
        'photo_category' => ['Menschen', 'Natur', 'Architektur', 'Street'],
    ];
    foreach ($terms as $taxonomy => $names) {
        foreach ($names as $name) {
            if (!term_exists($name, $taxonomy)) {
                wp_insert_term($name, $taxonomy);
            }
        }
    }
    update_option('portfolio_terms_seeded', 1);
});
