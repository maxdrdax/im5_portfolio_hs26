<?php
// -> Getter für den Post Type «Projekt»
// Detailseite, Highlights und Liste nutzen alle diese Funktionen: Daten werden nur einmal erfasst.

// Alle Daten eines Projekts
function get_project_by_id(int $id): array
{
    // Galerie: Videos als Video laden, alles andere als Bild
    // array_filter entfernt Einträge, deren Datei in der Mediathek gelöscht wurde (false),
    // array_values nummeriert danach neu (0, 1, 2 …), damit Zähler und Punkte stimmen.
    $gallery = array_values(array_filter(array_map(function ($media_id) {
        return get_video_by_id($media_id) ?: get_media_by_id($media_id, 'l');
    }, carbon_get_post_meta($id, 'gallery') ?: [])));

    // Standard-Zwischentitel aus den Globals (gilt, wenn im Projekt nichts eingetragen ist)
    $titles = get_global_project_titles();

    return [
        'id' => $id,
        'title' => get_the_title($id),
        'url' => get_permalink($id),
        'cover' => get_media_by_id(carbon_get_post_meta($id, 'cover'), 'l'),
        // kleine Version (600 px) für Vorschaubilder, z. B. Projektliste auf dem Handy
        'cover_small' => get_media_by_id(carbon_get_post_meta($id, 'cover'), 's'),
        'cover_video' => get_video_by_id(carbon_get_post_meta($id, 'cover_video')),
        'year' => carbon_get_post_meta($id, 'year'),
        'context' => carbon_get_post_meta($id, 'context'),
        'role' => carbon_get_post_meta($id, 'role'),
        'description' => carbon_get_post_meta($id, 'description'),
        'role_text' => carbon_get_post_meta($id, 'role_text'),
        // Zwischentitel: eigener Titel im Projekt, sonst Standard aus den Globals
        'description_title' => carbon_get_post_meta($id, 'description_title') ?: $titles['description'],
        'role_title' => carbon_get_post_meta($id, 'role_title') ?: $titles['role'],
        'video_title' => carbon_get_post_meta($id, 'video_title') ?: $titles['video'],
        'gallery_title' => carbon_get_post_meta($id, 'gallery_title') ?: $titles['gallery'],
        'links_title' => carbon_get_post_meta($id, 'links_title') ?: $titles['links'],
        'links' => get_project_links($id),
        'role_image' => get_media_by_id(carbon_get_post_meta($id, 'role_image'), 'l'),
        'gallery' => array_values(array_filter($gallery)),
        // Nur der Link; eingebettet wird erst auf der Detailseite (spart Anfragen in Liste/Karussell)
        'video_url' => carbon_get_post_meta($id, 'video_url'),
        'featured' => (bool) carbon_get_post_meta($id, 'featured'),
        'hide_title' => (bool) carbon_get_post_meta($id, 'hide_title'),
        'categories' => get_term_list($id, 'project_category'),
    ];
}

// Weiterführende Informationen eines Projekts (Linkliste)
// Einträge ohne gültigen Link werden übersprungen. «external»: Link führt auf eine andere Website
// (dann öffnet er in einem neuen Tab); Links auf die eigene Website bleiben im selben Tab.
function get_project_links(int $id): array
{
    $home_host = wp_parse_url(home_url(), PHP_URL_HOST);
    // Automatisch geholte Vorschaubilder (backend/init/_link-previews.php): Link (md5) → Bild-ID
    $auto_previews = get_post_meta($id, '_link_previews', true) ?: [];
    $links = [];
    foreach (carbon_get_post_meta($id, 'links') ?: [] as $link) {
        $url = esc_url_raw(trim($link['url'] ?? ''));
        $title = trim($link['title'] ?? '');
        if ($url === '' || $title === '') {
            continue;
        }
        // Vorschau beim Hover: 1. eigenes Bild, 2. Logo der Plattform (Social-Media-Link),
        // 3. automatisch geholtes Bild der Website, sonst false (keine Vorschau)
        $own = get_media_by_id($link['image'] ?? 0, 'm');
        $logo = $own ? false : get_social_icon_for_url($url);

        $links[] = [
            'title' => $title,
            'source' => trim($link['source'] ?? ''),
            'url' => $url,
            'external' => wp_parse_url($url, PHP_URL_HOST) !== $home_host,
            'preview' => $own ?: $logo ?: get_media_by_id($auto_previews[md5($url)] ?? 0, 'm'),
            // Logo wird auf grüner Fläche gezeigt statt bildfüllend
            'preview_logo' => (bool) $logo,
        ];
    }
    return $links;
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
