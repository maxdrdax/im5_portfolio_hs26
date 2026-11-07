<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// -> Konfiguration der Module (Modulbaukasten) gemäss CMS-Konzept, Teil C
// Titel-Felder unterstützen einen kursiven Akzent: Wort in *Sternchen* setzen.
Container::make('post_meta', 'Module')
    ->where('post_type', '=', 'page')
    ->add_fields([
        Field::make('complex', 'modules', 'Module')
            // -> hero_image (Startseite, Über mich)
            ->add_fields('hero_image', 'Hero Bild', [
                Field::make('text', 'title', 'Titel')
                    ->set_help_text('Optional. Akzent mit *Sternchen*.'),
                Field::make('image', 'image', 'Bild')
                    ->set_required()
                    ->set_help_text('Wird auch als Vorschaubild und Fallback für das Video verwendet.'),
                Field::make('file', 'video', 'Video (MP4)')
                    ->set_type(['video'])
                    ->set_help_text('Optional. Kurzer Clip ohne Ton (ideal unter 5 MB), läuft in Schleife.'),
            ])
            // -> hero_text (Projekte, Fotos, Kontakt)
            ->add_fields('hero_text', 'Hero Text', [
                Field::make('text', 'title', 'Titel')
                    ->set_required()
                    ->set_help_text('Akzent mit *Sternchen*.'),
                Field::make('textarea', 'text', 'Text'),
            ])
            // -> projects (Highlights-Karussell oder Liste); Daten kommen aus dem Post Type Projekt
            ->add_fields('projects', 'Projekte', [
                Field::make('text', 'title', 'Titel'),
                Field::make('select', 'layout', 'Darstellung')
                    ->set_options([
                        'carousel' => 'Karussell',
                        'list' => 'Liste',
                    ])
                    ->set_default_value('carousel'),
                Field::make('association', 'projects', 'Projekte')
                    ->set_types([
                        ['type' => 'post', 'post_type' => 'project'],
                    ])
                    ->set_help_text('Leer lassen = alle Projekte automatisch.'),
            ])
            // -> gallery_photos (keine Felder)
            ->add_fields('gallery_photos', 'Fotogalerie', [
                Field::make('html', 'gallery_photos_info')
                    ->set_html('<p>Listet automatisch alle Fotos mit Filter nach Fotokategorie.</p>'),
            ])
            // -> logos
            ->add_fields('logos', 'Logos', [
                Field::make('text', 'title', 'Titel'),
                Field::make('complex', 'logos', 'Logos')
                    ->add_fields([
                        Field::make('image', 'logo', 'Logo')->set_required(),
                        Field::make('text', 'link', 'Link')->set_attribute('type', 'url'),
                    ])
                    ->set_layout('tabbed-horizontal'),
            ])
            // -> profile (Über mich)
            ->add_fields('profile', 'Profil', [
                Field::make('text', 'name', 'Name'),
                Field::make('text', 'position', 'Funktion'),
                Field::make('complex', 'skills', 'Skills')
                    ->add_fields([
                        Field::make('text', 'skill', 'Skill')->set_required(),
                    ])
                    ->set_header_template('<% if (skill) { %><%- skill %><% } %>')
                    ->set_layout('tabbed-horizontal'),
                Field::make('complex', 'facts', 'Steckbrief')
                    ->add_fields([
                        Field::make('text', 'label', 'Bezeichnung')->set_width(50),
                        // «value» ist in Carbon Fields reserviert, darum «detail»
                        Field::make('text', 'detail', 'Wert')
                            ->set_width(50)
                            ->set_help_text('Mehrere Werte mit Komma trennen, sie stehen dann untereinander.'),
                    ])
                    ->set_header_template('<% if (label) { %><%- label %><% } %>')
                    ->set_layout('tabbed-vertical'),
                Field::make('text', 'title', 'Titel')
                    ->set_help_text('Akzent mit *Sternchen*.'),
                Field::make('rich_text', 'text', 'Text'),
            ])
            // -> experience (Über mich)
            ->add_fields('experience', 'Erfahrung', [
                Field::make('text', 'title', 'Titel'),
                Field::make('textarea', 'text', 'Text'),
                Field::make('complex', 'stations', 'Stationen')
                    ->add_fields([
                        Field::make('text', 'title', 'Titel')->set_required(),
                        Field::make('text', 'institution', 'Institution')->set_width(50),
                        Field::make('text', 'period', 'Zeitraum')->set_width(50),
                        Field::make('textarea', 'text', 'Text'),
                        Field::make('text', 'tags', 'Tags (mit Komma getrennt)'),
                    ])
                    ->set_header_template('<% if (title) { %><%- title %><% } %>')
                    ->set_layout('tabbed-vertical'),
                Field::make('file', 'cv', 'Lebenslauf (PDF)')
                    ->set_type(['application/pdf']),
            ])
            // -> contact (keine Felder, Daten aus den Globals)
            ->add_fields('contact', 'Kontakt', [
                Field::make('html', 'contact_info')
                    ->set_html('<p>Zeigt E-Mail und alle Socials aus «Globales».</p>'),
            ])
            // -> text (Impressum, Datenschutz)
            ->add_fields('text', 'Text', [
                Field::make('text', 'title', 'Titel'),
                Field::make('rich_text', 'text', 'Text'),
            ])
            // -> SETTINGS
            ->set_layout('tabbed-vertical'),
    ]);

// -> Funktion, um die Module und die entsprechenden Templates auf der Seite zu laden
function render_modules(): void
{
    $modules = carbon_get_the_post_meta('modules');
    if (empty($modules)) {
        return;
    }
    foreach ($modules as $module) {
        // -> Modifiziere die Daten des Moduls, falls nötig, basierend auf dem Modultyp
        switch ($module['_type']) {
            case 'hero_image':
                $module['image'] = get_media_by_id($module['image'] ?? 0, 'xl');
                $module['video'] = get_video_by_id($module['video'] ?? 0);
                break;
            case 'projects':
                // Association liefert Einträge mit 'id'; leer = alle Projekte
                $ids = array_map(function ($item) {
                    return (int) $item['id'];
                }, $module['projects'] ?? []);
                $module['projects'] = get_projects($ids);
                $module['categories'] = get_taxonomy_terms('project_category');
                break;
            case 'gallery_photos':
                $module['photos'] = get_photos();
                $module['categories'] = get_taxonomy_terms('photo_category');
                break;
            case 'logos':
                $module['logos'] = $module['logos'] ?? [];
                foreach ($module['logos'] as &$item) {
                    $item['logo'] = get_media_by_id($item['logo'] ?? 0, 's');
                }
                unset($item);
                break;
            case 'experience':
                $module['stations'] = $module['stations'] ?? [];
                foreach ($module['stations'] as &$station) {
                    // «Schnitt, Kamera» → ['Schnitt', 'Kamera']
                    $station['tags'] = array_values(array_filter(array_map('trim', explode(',', $station['tags'] ?? ''))));
                }
                unset($station);
                // Direkte Datei-URL: get_media_by_id() würde bei PDFs das Vorschaubild liefern
                $cv_id = (int) ($module['cv'] ?? 0);
                $module['cv'] = $cv_id ? wp_get_attachment_url($cv_id) : false;
                break;
            case 'contact':
                $module['email'] = get_global_email();
                $module['socials'] = get_global_socials();
                break;
        }

        // -> Laden des Templates basierend auf dem Modultyp und weitergeben der Daten
        get_template_part(
            'frontend/views/modules/' . $module['_type'],
            null,
            [
                'data' => $module,
            ]
        );
    }
}
