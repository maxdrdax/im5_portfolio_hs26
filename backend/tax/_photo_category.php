<?php
// -> Taxonomie «Fotokategorie» für Fotos (Filter in der Fotogalerie)
register_taxonomy(
    'photo_category',
    ['photo'],
    [
        'label' => 'Fotokategorie',
        'labels' => [
            'name' => 'Fotokategorien',
            'singular_name' => 'Fotokategorie',
            'menu_name' => 'Kategorien',
            'all_items' => 'Alle Fotokategorien',
            'edit_item' => 'Fotokategorie bearbeiten',
            'view_item' => 'Fotokategorie ansehen',
            'update_item' => 'Fotokategorie updaten',
            'add_new_item' => 'Neue Fotokategorie hinzufügen',
        ],
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
