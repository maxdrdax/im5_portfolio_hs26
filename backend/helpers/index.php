<?php
/**
 * Funktionen in dieser Datei können überall im Projekt verwendet werden.
 * Wenn eine neue globale Funktion benötigt wird, bitte hier ergänzen.
 *
 * Diese globalen Funktionen sind nicht Wordpress-spezifisch.
 */

// -> PHP-Daten in der Browser-Konsole anzeigen
function js_log(mixed ...$content): void
{
    foreach ($content as $c) {
        echo '<script>console.log(' . json_encode($c) . ')</script>';
    }
}

// -> Details einer Mediendatei anhand der ID laden
function get_media_by_id(int|string $id, string $size = 'm'): false|array
{
    $id = (int) $id;
    if ($id <= 0) {
        return false;
    }

    $data = [
        'title' => get_the_title($id),
        'alt' => get_post_meta($id, '_wp_attachment_image_alt', true) ?: get_the_title($id),
    ];
    $image = wp_get_attachment_image_src($id, $size);
    if ($image) {
        $data['src'] = $image[0];
        $data['type'] = 'image';
        // Breite/Höhe: verhindert Layout-Sprünge und ergibt das Bildformat (Foto-Raster)
        $data['width'] = (int) $image[1];
        $data['height'] = (int) $image[2];
    } else {
        $data['src'] = wp_get_attachment_url($id);
        $data['type'] = get_post_mime_type($id);
    }
    return $data;
}

// -> Ein Menu anhand des Keys laden
function get_menu_by_slug(string $slug): array
{
    $theme_locations = get_nav_menu_locations();
    if (!array_key_exists($slug, $theme_locations)) {
        return [];
    }
    $menu_obj = get_term($theme_locations[$slug], 'nav_menu');
    $menu = wp_get_nav_menu_items($menu_obj->name) ?: [];
    $items = [];
    foreach ($menu as $item) {
        $items[] = [
            'ID' => (int) $item->ID,
            'title' => $item->title,
            'url' => $item->url,
            'target' => $item->target,
        ];
    }
    return $items;
}

// -> Titel mit kursivem Akzent: *Wort* wird zu <em>Wort</em>
// Zuerst escapen, dann Sternchen ersetzen. Die Rückgabe ist sicher und kann direkt ausgegeben werden.
function format_accent(?string $text): string
{
    $safe = esc_html($text ?? '');
    return preg_replace('/\*(.+?)\*/', '<em>$1</em>', $safe);
}

// -> Rich-Text-Feld (Carbon Fields) sicher ausgeben: Absätze ergänzen, nur erlaubtes HTML
function format_rich_text(?string $html): string
{
    if (empty($html)) {
        return '';
    }
    return wp_kses_post(wpautop($html));
}

// -> Begriffe einer Taxonomie für einen Beitrag laden (Name + Slug)
// get_the_terms() liefert false oder WP_Error, wenn nichts zugewiesen ist. Darum zuerst prüfen.
function get_term_list(int $post_id, string $taxonomy): array
{
    $terms = get_the_terms($post_id, $taxonomy);
    if (empty($terms) || is_wp_error($terms)) {
        return [];
    }
    return array_map(function ($term) {
        return [
            'name' => $term->name,
            'slug' => $term->slug,
        ];
    }, $terms);
}

// -> Alle verwendeten Begriffe einer Taxonomie laden (für Filterlisten)
function get_taxonomy_terms(string $taxonomy): array
{
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => true,
    ]);
    if (empty($terms) || is_wp_error($terms)) {
        return [];
    }
    return array_map(function ($term) {
        return [
            'name' => $term->name,
            'slug' => $term->slug,
        ];
    }, $terms);
}

// -> Video-Link (YouTube/Vimeo) in einen Embed-Code umwandeln (oEmbed)
// oEmbed: WordPress fragt beim Anbieter den passenden <iframe>-Code ab.
// Das Ergebnis wird 1 Woche zwischengespeichert (Transient), damit nicht jeder Seitenaufruf eine Anfrage auslöst.
function get_video_embed(?string $url): string|false
{
    $url = esc_url_raw(trim($url ?? ''));
    if (empty($url)) {
        return false;
    }

    $cache_key = 'video_embed_' . md5($url);
    $html = get_transient($cache_key);

    if ($html === false) {
        $html = wp_oembed_get($url, ['width' => 1600]);
        if (!$html) {
            return false;
        }
        set_transient($cache_key, $html, WEEK_IN_SECONDS);
    }

    // YouTube ohne Tracking-Cookies (Datenschutz)
    $html = str_replace('youtube.com/embed/', 'youtube-nocookie.com/embed/', $html);
    // Lazy Loading: Video wird erst geladen, wenn es in die Nähe des Bildschirms kommt
    $html = str_replace('<iframe ', '<iframe loading="lazy" ', $html);

    // Nur ein <iframe> mit diesen Attributen erlauben (Escaping)
    return wp_kses($html, [
        'iframe' => [
            'src' => true,
            'width' => true,
            'height' => true,
            'title' => true,
            'frameborder' => true,
            'allow' => true,
            'allowfullscreen' => true,
            'referrerpolicy' => true,
            'loading' => true,
        ],
    ]);
}
