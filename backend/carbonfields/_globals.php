<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// -> Inhaltsfeld-Konfiguration der globalen Einstellungen des Themes
// Carbon Fields kennt keinen Feldtyp «url»: darum text-Feld mit type="url" (Browser prüft das Format)
Container::make('theme_options', 'Globales')
    ->add_fields([
        Field::make('text', 'logo', 'Logo (Schriftzug im Header)')
            ->set_default_value('Max Hutmacher'),
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
    ]);
