<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// -> Inhaltsfelder Post Type «Projekt» (alle Angaben werden nur hier erfasst)
Container::make('post_meta', 'Projekt')
    ->where('post_type', '=', 'project')
    ->add_fields([
        Field::make('checkbox', 'featured', 'Ausgewähltes Projekt')
            ->set_option_value('yes')
            ->set_help_text('Erscheint im Filter «Ausgewählte Projekte» (Standardfilter auf der Seite Projekte).'),
        Field::make('checkbox', 'hide_title', 'Titel unter dem Hero ausblenden')
            ->set_option_value('yes')
            ->set_help_text('Z. B. wenn der Schriftzug schon im Titelbild steht. Für Screenreader bleibt der Titel erhalten.'),
        Field::make('image', 'cover', 'Titelbild')
            ->set_required()
            ->set_help_text('Hero im Projekt, Karte im Highlights-Karussell und Hover-Vorschau in der Liste.'),
        Field::make('file', 'cover_video', 'Titelvideo (MP4)')
            ->set_type(['video'])
            ->set_help_text('Optional. Kurzer Clip ohne Ton (ideal unter 5 MB). Läuft im Hero und beim Hover in Karussell und Liste. Das Titelbild bleibt Vorschaubild.'),
        Field::make('text', 'year', 'Jahr')->set_width(33),
        Field::make('text', 'context', 'Kontext')->set_width(33),
        Field::make('text', 'role', 'Meine Rolle (kurz)')->set_width(33),
        Field::make('text', 'video_url', 'Video (YouTube/Vimeo-Link)')
            ->set_attribute('type', 'url')
            ->set_attribute('placeholder', 'https://vimeo.com/123456789')
            ->set_help_text('Optional. Link aus der Adresszeile von YouTube oder Vimeo einfügen.'),
        // Zwischentitel: leer = Standard aus «Globales» (siehe get_global_project_titles)
        Field::make('text', 'video_title', 'Titel über dem Video')
            ->set_attribute('placeholder', 'Standard aus «Globales»')
            ->set_help_text('Optional. Akzent mit *Sternchen*.'),
        Field::make('text', 'description_title', 'Titel über dem Projektbeschrieb')
            ->set_attribute('placeholder', 'Standard aus «Globales»')
            ->set_help_text('Optional. Akzent mit *Sternchen*.'),
        Field::make('rich_text', 'description', 'Projektbeschrieb'),
        Field::make('text', 'role_title', 'Titel über «Meine Rolle»')
            ->set_attribute('placeholder', 'Standard aus «Globales»')
            ->set_help_text('Optional. Akzent mit *Sternchen*.'),
        Field::make('rich_text', 'role_text', 'Meine Rolle')
            ->set_help_text('Optional. Text und Bild leer = Sektion wird nicht angezeigt.'),
        Field::make('image', 'role_image', 'Meine Rolle (Bild)'),
        Field::make('text', 'gallery_title', 'Titel über der Bildergalerie')
            ->set_attribute('placeholder', 'Standard aus «Globales»')
            ->set_help_text('Optional. Akzent mit *Sternchen*.'),
        Field::make('media_gallery', 'gallery', 'Bildergalerie (Bilder und Videos)')
            ->set_type(['image', 'video'])
            ->set_help_text('Videos laufen ohne Ton in Schleife. Volle Videos besser über das Feld «Video (YouTube/Vimeo-Link)».'),
        // Weiterführende Informationen: Linkliste am Ende der Projektseite, leer = nicht ausgeben
        Field::make('text', 'links_title', 'Titel über «Weiterführende Informationen»')
            ->set_attribute('placeholder', 'Standard aus «Globales»')
            ->set_help_text('Optional. Akzent mit *Sternchen*.'),
        Field::make('complex', 'links', 'Weiterführende Informationen')
            ->set_help_text('Optional. Z. B. Festival, Artikel, Making-of. Leer = Sektion wird nicht angezeigt.')
            ->add_fields([
                Field::make('text', 'title', 'Titel')
                    ->set_width(50)
                    ->set_required(),
                Field::make('text', 'source', 'Quelle')
                    ->set_width(50)
                    ->set_help_text('Optional. Z. B. «Solothurner Filmtage, 2026».'),
                Field::make('text', 'url', 'Link')
                    ->set_attribute('type', 'url')
                    ->set_attribute('placeholder', 'https://')
                    ->set_width(50)
                    ->set_required(),
                Field::make('image', 'image', 'Vorschaubild')
                    ->set_width(50)
                    ->set_help_text('Optional. Erscheint beim Hover. Leer = bei Social Media das Logo aus «Globales» (Socials), sonst wird das Bild beim Speichern automatisch von der Website geholt.'),
            ])
            ->set_header_template('<% if (title) { %><%- title %><% } %>')
            ->set_layout('tabbed-vertical'),
    ]);
