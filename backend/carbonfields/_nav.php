<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// -> Felder am Menüeintrag (Design > Menüs): Hintergrund des Overlay-Menüs
// Carbon Fields kann den Container nicht auf ein Menü beschränken. Die Felder erscheinen darum
// bei allen Menüeinträgen, ausgegeben werden sie aber nur in der Hauptnavigation «main».
Container::make('nav_menu_item', 'Overlay-Menü')
    ->add_fields([
        // set_width(50): Bild und Video nebeneinander (weniger Scrollen in der Menü-Liste)
        Field::make('image', 'menu_image', 'Hintergrundbild')
            ->set_width(50)
            ->set_help_text('Nur Hauptnavigation. Hintergrund beim Hover. Wird ignoriert, wenn unter Globales ein Menü-Hintergrund gesetzt ist.'),
        Field::make('file', 'menu_video', 'Hintergrundvideo (MP4)')
            ->set_type(['video'])
            ->set_width(50)
            ->set_help_text('Optional. Kurzer Clip ohne Ton (ideal unter 5 MB). Das Bild bleibt Vorschau.'),
    ]);
