<?php
// -> Getter für den Post Type «Foto»

// Auswahl «Grösse im Raster» – eine Quelle für Carbon-Feld, Backend-Liste, QuickEdit und Getter
function get_photo_size_options(): array
{
    return [
        'auto' => 'Automatisch (nach Bildformat)',
        'small' => 'Klein (1 × 1)',
        'wide' => 'Breit (2 × 1)',
        'tall' => 'Hoch (1 × 2)',
        'large' => 'Gross (2 × 2)',
        'panorama' => 'Panorama (3 × 2)',
    ];
}

// Auswahl «Bildausschnitt»
function get_photo_focus_options(): array
{
    return [
        'center' => 'Mitte',
        'top' => 'Oben',
        'bottom' => 'Unten',
        'left' => 'Links',
        'right' => 'Rechts',
    ];
}

// Daten eines Fotos; Titel wird als Alt-Text verwendet
function get_photo_by_id(int $id): array
{
    $title = get_the_title($id);
    $image_id = (int) carbon_get_post_meta($id, 'image');
    $image = get_media_by_id($image_id, 'l');
    if ($image) {
        $image['alt'] = $title;
        // srcset: alle Bildgrössen, der Browser wählt passend zur Kachelgrösse
        $image['srcset'] = wp_get_attachment_image_srcset($image_id, 'l') ?: '';
    }

    // Format aus den Bildmassen ableiten
    $format = 'square';
    if ($image && !empty($image['width']) && !empty($image['height'])) {
        if ($image['width'] > $image['height']) {
            $format = 'landscape';
        } elseif ($image['width'] < $image['height']) {
            $format = 'portrait';
        }
    }

    // Kachelgrösse: gewählter Wert oder automatisch aus dem Format
    $size = carbon_get_post_meta($id, 'grid_size');
    if ($size === 'auto' || !array_key_exists($size, get_photo_size_options())) {
        $size = ['landscape' => 'wide', 'portrait' => 'tall', 'square' => 'small'][$format];
    }

    // Bildausschnitt (nur erlaubte Werte, sonst Mitte)
    $focus = carbon_get_post_meta($id, 'focus');
    if (!array_key_exists($focus, get_photo_focus_options())) {
        $focus = 'center';
    }

    return [
        'id' => $id,
        'title' => $title,
        'image' => $image,
        'format' => $format,
        'size' => $size,
        'focus' => $focus,
        'categories' => get_term_list($id, 'photo_category'),
    ];
}

// Alle veröffentlichten Fotos (Reihenfolge wie im Backend nach Datum)
function get_photos(): array
{
    $ids = get_posts([
        'post_type' => 'photo',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
    // Fotos ohne Bild weglassen
    return array_values(array_filter(array_map('get_photo_by_id', $ids), function ($photo) {
        return !empty($photo['image']);
    }));
}
