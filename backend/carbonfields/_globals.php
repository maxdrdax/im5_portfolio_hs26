<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// -> Inhaltsfeld-Konfiguration der globalen Einstellungen des Themes
// Carbon Fields kennt keinen Feldtyp «url»: darum text-Feld mit type="url" (Browser prüft das Format)
Container::make('theme_options', 'Globales')
    ->add_fields([
        Field::make('text', 'logo', 'Logo (Schriftzug im Header)')
            ->set_width(50)
            ->set_default_value('Max Hutmacher'),
        Field::make('image', 'logo_signet', 'Signet (Handy)')
            ->set_width(50)
            ->set_help_text('Optional. Ersetzt auf dem Handy den Schriftzug. Weiss, am besten als SVG oder PNG mit transparentem Hintergrund.'),
        Field::make('text', 'copyright', 'Copyright (Footer)')
            ->set_default_value('© Max Hutmacher'),
        Field::make('text', 'email', 'E-Mail')
            ->set_attribute('type', 'email'),
        Field::make('complex', 'socials', 'Socials')
            ->set_help_text('Footer zeigt nur Socials mit Icon, die Kontaktseite alle.')
            ->add_fields([
                Field::make('text', 'title', 'Titel')->set_required(),
                Field::make('text', 'url', 'Link')
                    ->set_attribute('type', 'url')
                    ->set_required(),
                Field::make('image', 'icon', 'Icon'),
            ])
            ->set_header_template('<% if (title) { %><%- title %><% } %>')
            ->set_layout('tabbed-vertical'),
        // Gemeinsamer Hintergrund fürs Overlay-Menü: hat Vorrang vor den Bildern der einzelnen Menüpunkte
        Field::make('image', 'menu_bg_image', 'Menü-Hintergrundbild')
            ->set_width(50)
            ->set_help_text('Optional. Gilt für das ganze Menü-Overlay. Leer = Hintergrund pro Menüpunkt (Design > Menüs).'),
        Field::make('file', 'menu_bg_video', 'Menü-Hintergrundvideo (MP4)')
            ->set_type(['video'])
            ->set_width(50)
            ->set_help_text('Optional. Kurzer Clip ohne Ton (ideal unter 5 MB). Das Bild bleibt Vorschau und Fallback.'),
    ]);
