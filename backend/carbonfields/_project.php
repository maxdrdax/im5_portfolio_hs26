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
        Field::make('image', 'cover', 'Titelbild')
            ->set_required()
            ->set_help_text('Hero im Projekt, Karte im Highlights-Karussell und Hover-Vorschau in der Liste.'),
        Field::make('text', 'year', 'Jahr')->set_width(33),
        Field::make('text', 'context', 'Kontext')->set_width(33),
        Field::make('text', 'role', 'Meine Rolle')->set_width(33),
        Field::make('text', 'video_url', 'Video (YouTube/Vimeo-Link)')
            ->set_attribute('type', 'url')
            ->set_attribute('placeholder', 'https://vimeo.com/123456789')
            ->set_help_text('Optional. Link aus der Adresszeile von YouTube oder Vimeo einfügen.'),
        Field::make('rich_text', 'description', 'Projektbeschrieb'),
        Field::make('rich_text', 'role_text', 'Rolle und Teamabgrenzung (Text)')
            ->set_help_text('Optional. Text und Bild leer = Sektion wird nicht angezeigt.'),
        Field::make('image', 'role_image', 'Rolle und Teamabgrenzung (Bild)'),
        Field::make('media_gallery', 'gallery', 'Bildergalerie')
            ->set_type(['image']),
    ]);
