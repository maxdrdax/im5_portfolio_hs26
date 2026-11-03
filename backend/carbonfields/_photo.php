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
    ]);
