<?php
// -> Getter für den Post Type «Projekt»
// Detailseite, Highlights und Liste nutzen alle diese Funktionen: Daten werden nur einmal erfasst.

// Feste Angaben eines Projekts (Highlights, Projektliste und Kopf der Projektseite)
// Die Module der Projektseite lädt get_project_modules() separat: Liste und Karussell brauchen sie nicht.
function get_project_by_id(int $id): array
{
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
        'featured' => (bool) carbon_get_post_meta($id, 'featured'),
        'hide_title' => (bool) carbon_get_post_meta($id, 'hide_title'),
        'categories' => get_term_list($id, 'project_category'),
    ];
}

// Module der Projektseite in der gespeicherten Reihenfolge, bereit für die Ausgabe
// Jedes Modul: 'type' (= Dateiname in frontend/views/project-modules/), 'title', 'index' und seine Daten.
// Leere Module (z. B. Galerie ohne Bilder) werden weggelassen; unbekannte Typen ebenfalls (Whitelist).
function get_project_modules(int $id): array
{
    // Standard-Zwischentitel aus den Globals (gilt, wenn im Modul nichts eingetragen ist)
    $titles = get_global_project_titles();
    $modules = [];

    foreach (carbon_get_post_meta($id, 'project_modules') ?: [] as $index => $raw) {
        $type = $raw['_type'] ?? '';
        $module = [
            'type' => $type,
            'index' => $index,
            'title' => trim($raw['title'] ?? '') ?: ($titles[$type] ?? ''),
        ];

        switch ($type) {
            case 'description':
                $module['text'] = (string) ($raw['text'] ?? '');
                $show = has_rich_text($module['text']);
                break;
            case 'role':
                $module['text'] = (string) ($raw['text'] ?? '');
                $module['image'] = get_media_by_id($raw['image'] ?? 0, 'l');
                // Text und Bild leer = nicht ausgeben
                $show = has_rich_text($module['text']) || $module['image'];
                break;
            case 'video':
                // Embed-Code ist bereits durch wp_kses() gefiltert (siehe get_video_embed)
                $module['embed'] = get_video_embed((string) ($raw['url'] ?? ''));
                $show = (bool) $module['embed'];
                break;
            case 'gallery':
                $module['items'] = get_gallery_items($raw['gallery'] ?? []);
                $show = !empty($module['items']);
                break;
            case 'links':
                $module['links'] = get_project_links($id, $raw['links'] ?? []);
                $show = !empty($module['links']);
                break;
            case 'prototype':
                // Ungültige Links fallen weg; ohne gültigen Prototyp = nicht ausgeben
                $module['items'] = get_prototype_items($raw['prototypes'] ?? []);
                $show = !empty($module['items']);
                break;
            default:
                $show = false;
        }

        if ($show) {
            $modules[] = $module;
        }
    }
    return $modules;
}

// Rich Text hat sichtbaren Inhalt (nicht nur leere Absätze oder Leerzeichen)
function has_rich_text(string $html): bool
{
    return trim(wp_strip_all_tags($html)) !== '';
}

// Galerie: Videos als Video laden, alles andere als Bild; nur Bilder und Videos behalten
// array_filter entfernt Einträge, deren Datei in der Mediathek gelöscht wurde (false),
// array_values nummeriert danach neu (0, 1, 2 …), damit Zähler und Punkte stimmen.
function get_gallery_items(array $ids): array
{
    $items = array_map(function ($media_id) {
        $media = get_video_by_id($media_id) ?: get_media_by_id($media_id, 'l');
        if ($media) {
            // Darstellung aus der Mediathek (backend/init/_gallery-fit.php): nur «contain» oder Standard «cover»
            $media['fit'] = get_post_meta((int) $media_id, 'gallery_fit', true) === 'contain' ? 'contain' : 'cover';
        }
        return $media;
    }, $ids);
    return array_values(array_filter($items, function ($media) {
        return $media && in_array($media['type'] ?? '', ['image', 'video'], true);
    }));
}

// Figma-Prototypen eines Moduls (höchstens 2, z. B. Handy und Web)
// Jeder Link wird geprüft (Whitelist in get_figma_url); ungültige Einträge werden übersprungen.
function get_prototype_items(array $rows): array
{
    $items = [];
    foreach ($rows as $row) {
        $url = get_figma_url((string) ($row['url'] ?? ''));
        if (!$url) {
            continue;
        }
        $items[] = [
            'label' => trim($row['label'] ?? ''),
            'url' => $url,
            'poster' => get_media_by_id($row['poster'] ?? 0, 'l'),
            // Format nur aus der Whitelist, sonst Querformat
            'format' => ($row['format'] ?? '') === 'portrait' ? 'portrait' : 'landscape',
        ];
    }
    // set_max(2) im Backend begrenzt schon; hier zur Sicherheit auch (Layout ist für 2 gebaut)
    return array_slice($items, 0, 2);
}

// Alle Links aus allen Modulen «Weiterführende Informationen» eines Projekts (Rohdaten)
// Gebraucht beim Speichern (Vorschaubilder holen, backend/init/_link-previews.php).
function get_project_link_rows(int $id): array
{
    $rows = [];
    foreach (carbon_get_post_meta($id, 'project_modules') ?: [] as $module) {
        if (($module['_type'] ?? '') === 'links') {
            $rows = array_merge($rows, $module['links'] ?? []);
        }
    }
    return $rows;
}

// Weiterführende Informationen (Linkliste) eines Moduls
// Einträge ohne gültigen Link werden übersprungen. «external»: Link führt auf eine andere Website
// (dann öffnet er in einem neuen Tab); Links auf die eigene Website bleiben im selben Tab.
function get_project_links(int $id, array $rows): array
{
    // Automatisch geholte Vorschaubilder (backend/init/_link-previews.php): Link (md5) → Bild-ID
    $auto_previews = get_post_meta($id, '_link_previews', true) ?: [];
    $links = [];
    foreach ($rows as $link) {
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
            'external' => is_external_url($url),
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
