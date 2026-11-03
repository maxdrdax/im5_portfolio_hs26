<?php
// -> Getter für den Post Type «Projekt»
// Detailseite, Highlights und Liste nutzen alle diese Funktionen: Daten werden nur einmal erfasst.

// Alle Daten eines Projekts
function get_project_by_id(int $id): array
{
    $gallery = array_map(function ($image_id) {
        return get_media_by_id($image_id, 'l');
    }, carbon_get_post_meta($id, 'gallery') ?: []);

    return [
        'id' => $id,
        'title' => get_the_title($id),
        'url' => get_permalink($id),
        'cover' => get_media_by_id(carbon_get_post_meta($id, 'cover'), 'l'),
        'year' => carbon_get_post_meta($id, 'year'),
        'context' => carbon_get_post_meta($id, 'context'),
        'role' => carbon_get_post_meta($id, 'role'),
        'description' => carbon_get_post_meta($id, 'description'),
        'role_text' => carbon_get_post_meta($id, 'role_text'),
        'role_image' => get_media_by_id(carbon_get_post_meta($id, 'role_image'), 'l'),
        'gallery' => array_values(array_filter($gallery)),
        // Nur der Link; eingebettet wird erst auf der Detailseite (spart Anfragen in Liste/Karussell)
        'video_url' => carbon_get_post_meta($id, 'video_url'),
        'featured' => (bool) carbon_get_post_meta($id, 'featured'),
        'categories' => get_term_list($id, 'project_category'),
    ];
}

// Mehrere Projekte laden: mit IDs in dieser Reihenfolge, ohne IDs alle (neueste zuerst)
function get_projects(array $ids = []): array
{
    $args = [
        'post_type' => 'project',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
    ];
    if (!empty($ids)) {
        $args['post__in'] = $ids;
        $args['orderby'] = 'post__in';
    } else {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }
    return array_map('get_project_by_id', get_posts($args));
}
