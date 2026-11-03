<?php
// -> Getter für den Post Type «Foto»

// Daten eines Fotos; Titel wird als Alt-Text verwendet
function get_photo_by_id(int $id): array
{
    $title = get_the_title($id);
    $image = get_media_by_id(carbon_get_post_meta($id, 'image'), 'l');
    if ($image) {
        $image['alt'] = $title;
    }

    // Format aus den Bildmassen ableiten (für das Foto-Raster)
    $format = 'square';
    if ($image && !empty($image['width']) && !empty($image['height'])) {
        if ($image['width'] > $image['height']) {
            $format = 'landscape';
        } elseif ($image['width'] < $image['height']) {
            $format = 'portrait';
        }
    }

    return [
        'id' => $id,
        'title' => $title,
        'image' => $image,
        'format' => $format,
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
