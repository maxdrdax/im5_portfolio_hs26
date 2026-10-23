<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// -> Konfiguration der verschiedenen Module
Container::make('post_meta', 'Module')
    ->where('post_type', '=', 'page')
    ->add_fields([
        Field::make('complex', 'modules', 'Module')
            // -> example
            ->add_fields('example', 'Beispiel', [
                Field::make('text', 'title', 'Titel')->set_required(),
                Field::make('image', 'image', 'Bild')->set_required(),
            ])
            ->add_fields('home_intro', 'Startseite Intro', [
                Field::make('text', 'eyebrow', 'Eyebrow')->set_default_value('Portfolio'),
                Field::make('text', 'title', 'Titel')->set_required(),
                Field::make('textarea', 'description', 'Beschreibung'),
            ])
            ->add_fields('home_gallery', 'Startseite Galerie', [
                Field::make('complex', 'items', 'Galerie')
                    ->add_fields([
                        Field::make('text', 'title', 'Titel')->set_required(),
                        Field::make('text', 'meta', 'Zusatzinformation'),
                        Field::make('image', 'image', 'Bild')->set_required(),
                        Field::make('text', 'link', 'Link'),
                    ])
                    ->set_header_template('<% if (title) { %><%- title %><% } %>')
                    ->set_layout('tabbed-vertical'),
            ])
            ->add_fields('home_logos', 'Startseite Logos', [
                Field::make('complex', 'items', 'Logos')
                    ->add_fields([
                        Field::make('text', 'title', 'Name')->set_required(),
                        Field::make('image', 'logo', 'Logo')->set_required(),
                        Field::make('text', 'link', 'Link'),
                    ])
                    ->set_header_template('<% if (title) { %><%- title %><% } %>')
                    ->set_layout('tabbed-vertical'),
            ])
            // -> SETTINGS
            ->set_layout('tabbed-vertical')
    ]);

// -> Funktion, um die Module und die entsprechenden Templates auf der Seite zu laden
function render_modules (): void {
    $modules = carbon_get_the_post_meta('modules');
    if (empty($modules)) {
        return;
    }
    foreach ($modules as $module) {
        // -> Modifiziere die Daten des Moduls, falls nötig, basierend auf dem Modultyp
        switch ($module['_type']) {
            case 'example':
                $module['image'] = get_media_by_id($module['image'], 'full');
                break;
            case 'home_gallery':
                foreach ($module['items'] as &$item) {
                    $item['image'] = get_media_by_id($item['image'] ?? 0, 'l');
                }
                unset($item);
                break;
            case 'home_logos':
                foreach ($module['items'] as &$item) {
                    $item['logo'] = get_media_by_id($item['logo'] ?? 0, 'm');
                }
                unset($item);
                break;
        }
        // -> Laden des Templates basierend auf dem Modultyp und weitergeben der Daten
        get_template_part(
            'frontend/views/modules/' . $module['_type'],
            null,
            [
                'data' => $module
            ]
        );
    }
}
