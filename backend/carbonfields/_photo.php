<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// -> Inhaltsfelder Post Type «Foto» (Titel = Alt-Text)
Container::make('post_meta', 'Foto')
    ->where('post_type', '=', 'photo')
    ->add_fields([
        Field::make('image', 'image', 'Bild')
            ->set_required()
            ->set_help_text('Der Titel des Beitrags wird als Alt-Text verwendet.'),
        // Kachelgrösse im Foto-Raster (3 Spalten); «Automatisch» leitet sie aus dem Bildformat ab
        Field::make('select', 'grid_size', 'Grösse im Raster')
            ->set_options(get_photo_size_options())
            ->set_default_value('auto')
            ->set_width(50)
            ->set_help_text('Automatisch: Querformat = Breit, Hochformat = Hoch, Quadrat = Klein.'),
        // Welcher Teil sichtbar bleibt, wenn die Kachel ein anderes Seitenverhältnis hat
        Field::make('select', 'focus', 'Bildausschnitt')
            ->set_options(get_photo_focus_options())
            ->set_default_value('center')
            ->set_width(50)
            ->set_help_text('Oben/Unten wirkt, wenn das Foto oben/unten beschnitten wird (z. B. Querformat in Breit), Links/Rechts bei seitlichem Beschnitt (z. B. Hochformat in Hoch).'),
    ]);
