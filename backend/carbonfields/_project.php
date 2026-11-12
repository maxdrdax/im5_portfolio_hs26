<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

// -> Inhaltsfelder Post Type «Projekt»
// Oben die festen Angaben: Highlights und Projektliste brauchen sie (Titelbild, Jahr usw.).
// Darunter der Inhalt der Projektseite als Module (verschieben, weglassen, ergänzen).
Container::make('post_meta', 'Projekt')
    ->where('post_type', '=', 'project')
    // Reihenfolge im Editor: «high» = zuoberst, vor den Modulen (default) und Yoast (low, siehe _seo.php)
    ->set_priority('high')
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
    ]);

// -> Module der Projektseite
// Name «project_modules» statt «modules»: Die Seiten haben schon ein Feld «modules». Carbon Fields
// sucht Felder über alle Container gleichen Typs; gleiche Namen würden verwechselt.
// Zwischentitel (title): leer = Standard aus «Globales» (siehe get_global_project_titles).
Container::make('post_meta', 'Module der Projektseite')
    ->where('post_type', '=', 'project')
    ->set_priority('default')
    ->add_fields([
        Field::make('complex', 'project_modules', 'Module')
            ->set_help_text('Reihenfolge per Drag-and-Drop. Leere Module werden nicht angezeigt.')
            // -> Projektbeschrieb: Titel links, Text rechts
            ->add_fields('description', 'Projektbeschrieb', [
                Field::make('text', 'title', 'Titel')
                    ->set_attribute('placeholder', 'Standard aus «Globales»')
                    ->set_help_text('Optional. Akzent mit *Sternchen*.'),
                Field::make('rich_text', 'text', 'Text'),
            ])
            // -> Meine Rolle: Text und/oder Bild (3:2) unter dem Text
            ->add_fields('role', 'Meine Rolle', [
                Field::make('text', 'title', 'Titel')
                    ->set_attribute('placeholder', 'Standard aus «Globales»')
                    ->set_help_text('Optional. Akzent mit *Sternchen*.'),
                Field::make('rich_text', 'text', 'Text'),
                Field::make('image', 'image', 'Bild')
                    ->set_help_text('Optional. Format 3:2.'),
            ])
            // -> Video: YouTube/Vimeo per oEmbed
            ->add_fields('video', 'Video', [
                Field::make('text', 'title', 'Titel')
                    ->set_attribute('placeholder', 'Standard aus «Globales»')
                    ->set_help_text('Optional. Akzent mit *Sternchen*.'),
                Field::make('text', 'url', 'YouTube/Vimeo-Link')
                    ->set_attribute('type', 'url')
                    ->set_attribute('placeholder', 'https://vimeo.com/123456789')
                    ->set_help_text('Link aus der Adresszeile von YouTube oder Vimeo einfügen.'),
            ])
            // -> Galerie: horizontales Akkordeon (Bilder und Videos)
            ->add_fields('gallery', 'Galerie', [
                Field::make('text', 'title', 'Titel')
                    ->set_attribute('placeholder', 'Standard aus «Globales»')
                    ->set_help_text('Optional. Akzent mit *Sternchen*.'),
                Field::make('media_gallery', 'gallery', 'Bilder und Videos')
                    ->set_type(['image', 'video'])
                    ->set_help_text('Videos laufen ohne Ton in Schleife. Volle Videos besser über das Modul «Video». Ganzes Bild zeigen statt zuschneiden: Bild anklicken → «Darstellung in der Galerie: Einpassen».'),            ])
            // -> Weiterführende Informationen: Linkliste mit Hover-Vorschau
            ->add_fields('links', 'Weiterführende Informationen', [
                Field::make('text', 'title', 'Titel')
                    ->set_attribute('placeholder', 'Standard aus «Globales»')
                    ->set_help_text('Optional. Akzent mit *Sternchen*.'),
                Field::make('complex', 'links', 'Links')
                    ->set_help_text('Z. B. Festival, Artikel, Making-of.')
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
            ])
            // -> Figma-Prototyp: Embed mit Click-to-load (lädt erst beim Klick etwas von Figma)
            // Ein oder zwei Prototypen (z. B. Handy und Web), zwei stehen ab Tablet nebeneinander.
            // Nicht im Standardaufbau: nur bei Projekten mit Prototyp über «Hinzufügen» ergänzen.
            ->add_fields('prototype', 'Figma-Prototyp', [
                Field::make('text', 'title', 'Titel')
                    ->set_attribute('placeholder', 'Standard aus «Globales»')
                    ->set_help_text('Optional. Akzent mit *Sternchen*.'),
                Field::make('complex', 'prototypes', 'Prototypen')
                    ->set_help_text('Ein oder zwei Prototypen. Zwei stehen nebeneinander (z. B. Handy und Web).')
                    ->set_max(2)
                    ->add_fields([
                        Field::make('text', 'label', 'Beschriftung')
                            ->set_attribute('placeholder', 'z. B. Mobile oder Web')
                            ->set_help_text('Optional. Steht über der Vorschau. Leer = keine Beschriftung.'),
                        Field::make('text', 'url', 'Figma-Link')
                            ->set_attribute('type', 'url')
                            ->set_attribute('placeholder', 'https://www.figma.com/proto/…')
                            ->set_required()
                            ->set_help_text('Share-Link aus Figma (figma.com/proto/… oder figma.com/design/…). Andere Links werden nicht angezeigt.'),
                        Field::make('image', 'poster', 'Vorschaubild')
                            ->set_width(50)
                            ->set_help_text('Optional. Erscheint vor dem Start. Leer = dunkle Fläche.'),
                        Field::make('select', 'format', 'Format')
                            ->set_width(50)
                            ->set_options([
                                'landscape' => 'Querformat 16:10 (Desktop)',
                                'portrait' => 'Hochformat 9:16 (Handy-Prototyp)',
                            ])
                            ->set_default_value('landscape'),
                    ])
                    // Kopfzeile im Backend: Beschriftung, sonst Format (damit man die Einträge unterscheiden kann)
                    ->set_header_template('<%- label ? label : (format === "portrait" ? "Hochformat (Handy)" : "Querformat (Web)") %>'),
            ])
            // Neues Projekt startet mit dem Standardaufbau (gleiche Reihenfolge wie bisher)
            ->set_default_value(get_project_default_modules())
            ->set_layout('tabbed-vertical'),
    ]);

// -> Standardaufbau einer Projektseite (eine Quelle für neue Projekte und den Umzug)
function get_project_default_modules(): array
{
    return [
        ['_type' => 'description'],
        ['_type' => 'role'],
        ['_type' => 'video'],
        ['_type' => 'gallery'],
        ['_type' => 'links'],
    ];
}
