<?php
// -> Taxonomie «Projektkategorie» für Projekte (Filter auf der Seite Projekte)
register_taxonomy(
    'project_category',
    ['project'],
    [
        'label' => 'Projektkategorie',
        'labels' => [
            'name' => 'Projektkategorien',
            'singular_name' => 'Projektkategorie',
            'menu_name' => 'Kategorien',
            'all_items' => 'Alle Projektkategorien',
            'edit_item' => 'Projektkategorie bearbeiten',
            'view_item' => 'Projektkategorie ansehen',
            'update_item' => 'Projektkategorie updaten',
            'add_new_item' => 'Neue Projektkategorie hinzufügen',
        ],
        // Keine eigenen Archivseiten: gefiltert wird direkt auf der Seite Projekte
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => false,
        'show_in_nav_menus' => false,
        'show_admin_column' => true,
        'query_var' => false,
        'hierarchical' => true,
        'rewrite' => false,
    ]
);
