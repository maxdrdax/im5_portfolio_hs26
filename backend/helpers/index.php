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
    // Leer oder gelöscht: Felder speichern nur die ID, auch wenn das Bild in der Mediathek gelöscht wurde
    if ($id <= 0 || get_post_type($id) !== 'attachment') {
        return false;
    }

    $data = [
        'title' => get_the_title($id),
        'alt' => get_post_meta($id, '_wp_attachment_image_alt', true) ?: get_the_title($id),
        'animated' => false,
    ];

    // Animiertes WebP/GIF: Die verkleinerten Bildgrössen von WordPress enthalten nur das erste Bild
    // (die Animation geht beim Verkleinern verloren). Darum hier immer die Originaldatei verwenden.
    if (is_animated_image($id)) {
        $file = wp_get_original_image_path($id);
        $size_info = $file ? wp_getimagesize($file) : false;
        $data['src'] = wp_get_original_image_url($id);
        $data['type'] = 'image';
        $data['animated'] = true;
        $data['width'] = (int) ($size_info[0] ?? 0);
        $data['height'] = (int) ($size_info[1] ?? 0);
        return $data;
    }

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

// -> Prüfen, ob ein Bild animiert ist (WebP oder GIF mit mehreren Bildern)
// Gelesen wird nur der Anfang der Datei (WebP) bzw. bis zum zweiten Bild (GIF). Das Ergebnis wird im
// Bild gespeichert (_is_animated), damit die Datei nicht bei jedem Seitenaufruf geöffnet werden muss.
function is_animated_image(int $id): bool
{
    $mime = get_post_mime_type($id);
    if (!in_array($mime, ['image/webp', 'image/gif'], true)) {
        return false;
    }
    $cached = get_post_meta($id, '_is_animated', true);
    if ($cached !== '') {
        return $cached === '1';
    }

    $animated = false;
    $file = wp_get_original_image_path($id);
    if ($file && is_readable($file)) {
        if ($mime === 'image/webp') {
            // Animierte WebP-Dateien haben einen «ANIM»-Abschnitt gleich am Anfang
            $head = (string) file_get_contents($file, false, null, 0, 64);
            $animated = str_contains($head, 'ANIM');
        } else {
            // GIF: Jedes Einzelbild beginnt mit einem «Graphic Control Extension»-Block; ab zwei = animiert
            $content = (string) file_get_contents($file);
            $animated = preg_match_all('/\x00\x21\xF9\x04/', $content) > 1;
        }
    }
    update_post_meta($id, '_is_animated', $animated ? '1' : '0');
    return $animated;
}

// -> Details einer Videodatei anhand der ID laden; false, wenn es kein Video ist
// Breite/Höhe kommen aus den Metadaten, die WordPress beim Upload speichert.
function get_video_by_id(int|string $id): false|array
{
    $id = (int) $id;
    if ($id <= 0) {
        return false;
    }

    $mime = get_post_mime_type($id);
    if (!$mime || !str_starts_with($mime, 'video/')) {
        return false;
    }

    $meta = wp_get_attachment_metadata($id) ?: [];
    return [
        'src' => wp_get_attachment_url($id),
        'type' => 'video',
        'mime' => $mime,
        'width' => (int) ($meta['width'] ?? 0),
        'height' => (int) ($meta['height'] ?? 0),
    ];
}

// -> Ein Menu anhand des Keys laden
function get_menu_by_slug(string $slug): array
{
    $theme_locations = get_nav_menu_locations();
    if (!array_key_exists($slug, $theme_locations)) {
        return [];
    }
    $menu_obj = get_term($theme_locations[$slug], 'nav_menu');
    if (!$menu_obj || is_wp_error($menu_obj)) {
        return [];
    }
    $menu = wp_get_nav_menu_items($menu_obj->name) ?: [];
    $items = [];
    foreach ($menu as $item) {
        $data = [
            'ID' => (int) $item->ID,
            'title' => $item->title,
            'url' => $item->url,
            // Externe Menüeinträge immer im neuen Tab, auch wenn in Design > Menüs nicht angehakt
            'target' => is_external_url($item->url) ? '_blank' : $item->target,
            'current' => is_current_menu_item($item),
            // Slug der verlinkten Seite (z. B. «kontakt» für den Kontakt-Link im Header); sonst leer
            'slug' => $item->object === 'page' ? get_post_field('post_name', (int) $item->object_id) : '',
        ];
        // Overlay-Hintergrund nur für die Hauptnavigation laden (spart Datenbankabfragen beim Footer-Menü)
        if ($slug === 'main') {
            $data['image'] = get_media_by_id(carbon_get_nav_menu_item_meta($item->ID, 'menu_image'), 'l');
            $data['video'] = get_video_by_id(carbon_get_nav_menu_item_meta($item->ID, 'menu_video'));
        }
        $items[] = $data;
    }
    return $items;
}

// -> Prüfen, ob ein Menüeintrag zur aktuell angezeigten Seite gehört (für den Active State)
function is_current_menu_item(WP_Post $item): bool
{
    // Eintrag verweist auf genau diese Seite
    if ($item->type === 'post_type' && (int) $item->object_id === get_queried_object_id()) {
        return true;
    }
    // Projekt-Detailseite: «Projekte» (Seite mit Slug «projekte» aus dem CMS-Konzept) ist aktiv
    if (is_singular('project') && $item->object === 'page') {
        return get_post_field('post_name', (int) $item->object_id) === 'projekte';
    }
    return false;
}

// -> Icon aus der eigenen Icon-Bibliothek (frontend/assets/icons/{name}.svg) direkt ins HTML einfügen
// Inline statt <img>: Das Icon übernimmt die Textfarbe (currentColor), auch bei Hover und Farbumkehr.
// $direction: 'down' (Standard) oder 'up-right'; die Drehung macht das CSS (.icon--up-right).
// Sicherheit: Nur Namen aus Buchstaben, Zahlen und Bindestrich, Dateien liegen im Theme (keine Uploads).
function get_icon(string $name, string $direction = 'down', string $class = ''): string
{
    if (!preg_match('/^[a-z0-9-]+$/', $name)) {
        return '';
    }
    $file = get_template_directory() . '/frontend/assets/icons/' . $name . '.svg';
    if (!is_readable($file)) {
        return '';
    }
    $svg = trim((string) file_get_contents($file));
    $classes = trim('icon icon--' . sanitize_html_class($direction) . ' ' . sanitize_html_class($class));
    // Klasse und Barrierefreiheit ergänzen: Icons sind Dekoration, der Text daneben sagt, worum es geht
    return preg_replace('/^<svg\b/', '<svg class="' . esc_attr($classes) . '" aria-hidden="true" focusable="false"', $svg, 1);
}

// -> Titel mit Akzent: **Wort** wird zu <mark>Wort</mark> (grün hinterlegt), *Wort* zu <em>Wort</em> (kursiv)
// Zuerst escapen, dann Sternchen ersetzen. Die Rückgabe ist sicher und kann direkt ausgegeben werden.
// Reihenfolge wichtig: zuerst die doppelten Sternchen, sonst würde *…* sie zerlegen.
function format_accent(?string $text): string
{
    $safe = esc_html($text ?? '');
    $safe = preg_replace('/\*\*(.+?)\*\*/', '<mark>$1</mark>', $safe);
    return preg_replace('/\*(.+?)\*/', '<em>$1</em>', $safe);
}

// -> Rich-Text-Feld (Carbon Fields) sicher ausgeben: Absätze ergänzen, nur erlaubtes HTML
// Externe Links im Text öffnen in einem neuen Tab (auch wenn im Editor nicht angehakt).
// WP_HTML_Tag_Processor (WordPress 6.2+): ändert Attribute sicher, ohne HTML mit Regex zu zerlegen.
function format_rich_text(?string $html): string
{
    if (empty($html)) {
        return '';
    }
    $html = wp_kses_post(wpautop($html));

    $tags = new WP_HTML_Tag_Processor($html);
    while ($tags->next_tag('a')) {
        if (is_external_url((string) $tags->get_attribute('href'))) {
            $tags->set_attribute('target', '_blank');
            // noopener: Die fremde Seite bekommt keinen Zugriff auf dieses Fenster (window.opener)
            $tags->set_attribute('rel', 'noopener');
        }
    }
    return $tags->get_updated_html();
}

// -> Anzahl im Filter-Button, z. B. «Video 2»
// Sichtbar ist nur die Zahl (aria-hidden), Screenreader hören stattdessen «, 2 Projekte».
// $singular / $plural: Bezeichnung für den Screenreader (z. B. 'Projekt' / 'Projekte').
function get_filter_count(int $count, string $singular, string $plural): string
{
    $label = $count === 1 ? $singular : $plural;
    return '<span class="filter-count" aria-hidden="true">' . esc_html((string) $count) . '</span>'
        . '<span class="screen-reader-text">, ' . esc_html($count . ' ' . $label) . '</span>';
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
    // Vimeo ohne Tracking-Cookies: Parameter dnt=1 («do not track») an die Player-Adresse anhängen.
    // add_query_arg() setzt ? oder & richtig; esc_url() maskiert die Adresse wieder für das HTML-Attribut.
    $html = preg_replace_callback(
        '/src="(https:\/\/player\.vimeo\.com\/video\/[^"]+)"/',
        function (array $match): string {
            $src = add_query_arg('dnt', '1', html_entity_decode($match[1]));
            return 'src="' . esc_url($src) . '"';
        },
        $html
    );
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

// -> Figma-Link (Share-Link) prüfen und bereinigen (Projektmodul «Figma-Prototyp»)
// Whitelist: nur https, nur Figma-Hosts, nur Pfade /proto/… oder /design/…; alles andere = false.
// So führt der Button «Figma-Prototyp öffnen» sicher nur zu Figma (kein Tippfehler-Link, kein javascript:).
// Rückgabe: bereinigte Adresse auf www.figma.com oder false. Kein Request an Figma (nur Text umbauen).
function get_figma_url(?string $url): string|false
{
    $url = trim($url ?? '');
    if ($url === '') {
        return false;
    }

    $parts = wp_parse_url($url);
    if (!is_array($parts)) {
        return false;
    }

    // 1. Nur https (kein http, javascript:, data: usw.)
    if (strtolower($parts['scheme'] ?? '') !== 'https') {
        return false;
    }
    // 2. Kein Benutzername/Passwort/Port in der Adresse (Trick: https://figma.com@boese.ch/ hat den Host boese.ch)
    if (isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
        return false;
    }
    // 3. Host exakt vergleichen (nicht mit str_contains: «figma.com.boese.ch» würde sonst durchrutschen)
    $host = strtolower($parts['host'] ?? '');
    if (!in_array($host, ['figma.com', 'www.figma.com', 'embed.figma.com'], true)) {
        return false;
    }
    // 4. Pfad: /proto/<Dateischlüssel>… oder /design/<Dateischlüssel>…, ohne «..»
    $path = $parts['path'] ?? '';
    if (!preg_match('#^/(proto|design)/[A-Za-z0-9]+(/|$)#', $path) || str_contains($path, '..')) {
        return false;
    }

    // Parameter: nur die nötigen übernehmen (Whitelist). Weg fallen z. B. «t» (Tracking-Kennung von Figma),
    // «viewport» (Ausschnitt der Arbeitsfläche) und «show-proto-sidebar» (Flow-Liste verwirrt Besucher).
    parse_str($parts['query'] ?? '', $query);
    $query = array_intersect_key($query, array_flip([
        'node-id',                // Bildschirm, der beim Öffnen erscheint
        'starting-point-node-id', // Start des Flows (Neustart springt hierhin)
        'page-id',                // Seite in der Figma-Datei
        'version-id',             // bestimmte Version (leer = neueste)
        'scaling',                // Darstellung (z. B. min-zoom, scale-down)
        'content-scaling',        // fixed / responsive
    ]));
    $query_string = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

    // Immer die normale Figma-Adresse, auch wenn ein Embed-Link (embed.figma.com) eingefügt wurde
    return 'https://www.figma.com' . $path . ($query_string !== '' ? '?' . $query_string : '');
}

// -> Führt ein Link auf eine andere Website? (dann im neuen Tab öffnen)
// Relative Links (/kontakt/), Anker (#…) und mailto:/tel: gelten nicht als extern.
function is_external_url(?string $url): bool
{
    $host = strtolower((string) wp_parse_url($url ?? '', PHP_URL_HOST));
    if ($host === '') {
        return false;
    }
    $home_host = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
    // www. ignorieren: www.maxhutmacher.ch und maxhutmacher.ch sind dieselbe Website
    return preg_replace('/^www\./', '', $host) !== preg_replace('/^www\./', '', $home_host);
}
