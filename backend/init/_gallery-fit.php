<?php
// -> Feld «Darstellung in der Galerie» an Bildern und Videos in der Mediathek (gallery_fit)
// Füllen (cover, Standard): Fläche ganz gefüllt, Ränder werden abgeschnitten.
// Einpassen (contain): ganzes Bild sichtbar, freie Fläche dunkel (z. B. Moodboard).
// Warum Hooks statt Carbon Fields: Das Feld «media_gallery» speichert nur die IDs der Bilder, darum hängt
// die Einstellung an der Datei selbst. WordPress zeigt das Feld im Mediathek-Fenster und auf der Seite
// «Medien > Bearbeiten» an. Soll dasselbe Bild einmal gefüllt und einmal eingepasst erscheinen:
// Bild ein zweites Mal hochladen und nur die Kopie auf «Einpassen» stellen.

// Erlaubte Werte (Whitelist): Schlüssel = gespeicherter Wert, Wert = Beschriftung im Backend
function get_gallery_fit_options(): array
{
    return [
        'cover' => 'Füllen (Standard)',
        'contain' => 'Einpassen (ganzes Bild)',
    ];
}

// 1. Feld anzeigen (nur bei Bildern und Videos)
add_filter('attachment_fields_to_edit', function (array $fields, WP_Post $post): array {
    if (!wp_attachment_is('image', $post) && !wp_attachment_is('video', $post)) {
        return $fields;
    }

    $current = get_post_meta($post->ID, 'gallery_fit', true) ?: 'cover';
    // name muss «attachments[ID][Feldname]» heissen, damit WordPress den Wert beim Speichern mitschickt
    $name = 'attachments[' . $post->ID . '][gallery_fit]';

    $html = '<select name="' . esc_attr($name) . '" id="' . esc_attr($name) . '">';
    foreach (get_gallery_fit_options() as $value => $label) {
        $html .= '<option value="' . esc_attr($value) . '"' . selected($current, $value, false) . '>'
            . esc_html($label) . '</option>';
    }
    $html .= '</select>';

    $fields['gallery_fit'] = [
        'label' => 'Darstellung in der Galerie',
        'input' => 'html',
        'html' => $html,
        'helps' => 'Einpassen: ganzes Bild sichtbar, z. B. bei Moodboards. Gilt in den Galerien der Projekte.',
    ];
    return $fields;
}, 10, 2);

// 2. Wert speichern: nur Werte aus der Whitelist; Standard (cover) wird nicht gespeichert
add_filter('attachment_fields_to_save', function (array $post, array $attachment): array {
    if (!isset($attachment['gallery_fit']) || !current_user_can('edit_post', $post['ID'])) {
        return $post;
    }

    $value = sanitize_key($attachment['gallery_fit']);
    if ($value === 'contain') {
        update_post_meta($post['ID'], 'gallery_fit', 'contain');
    } else {
        // Füllen = Standard: Eintrag löschen statt «cover» speichern (weniger Daten, gleiche Wirkung)
        delete_post_meta($post['ID'], 'gallery_fit');
    }
    return $post;
}, 10, 2);
