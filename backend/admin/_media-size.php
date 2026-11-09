<?php
/**
 * Mediathek (Medien > Mediathek): Dateigrösse anzeigen und zu grosse Dateien markieren.
 * 1. Listenansicht: Spalte «Dateigrösse» (Grösse, Abmessungen, Hinweis «Zu gross»).
 *    Ein-/ausblenden über «Ansicht anpassen» oben rechts (WordPress-Standard).
 * 2. Kachelansicht: Checkbox «Dateigrösse anzeigen» über den Kacheln; zeigt die Grösse auf jeder Kachel
 *    und rahmt zu grosse Dateien rot ein (media-size.js / media-size.css).
 *
 * Warum wichtig: Grosse Originale belegen Speicher beim Hosting und machen das Erzeugen der
 * Bildgrössen langsam. Videos laufen auf der Website direkt als Datei: jedes MB zählt beim Laden.
 *
 * Dokumentation:
 * https://developer.wordpress.org/reference/hooks/manage_media_columns/
 * https://developer.wordpress.org/reference/hooks/wp_prepare_attachment_for_js/
 */

// ▼ Zum Anpassen: ab welcher Dateigrösse eine Datei als «zu gross» gilt (in MB, pro Dateiart)
function get_media_size_limits(): array
{
    return [
        'image' => 1, // Bilder: WordPress verkleinert für die Website, das Original sollte trotzdem schlank sein
        'video' => 5, // Videos (MP4): laufen direkt als Datei, siehe Hilfetexte «ideal unter 5 MB»
    ];
}
// ▲

// -> Grösse und Bewertung einer Datei; false, wenn die Datei fehlt
function get_media_size_info(int $id): array|false
{
    $file = get_attached_file($id);
    if (!$file || !file_exists($file)) {
        return false;
    }
    $bytes = (int) filesize($file);
    // Dateiart = erster Teil des MIME-Typs, z. B. «image» aus «image/jpeg»
    $type = strtok((string) get_post_mime_type($id), '/');
    $limit = get_media_size_limits()[$type] ?? null;

    $meta = wp_get_attachment_metadata($id) ?: [];
    $dimensions = !empty($meta['width']) && !empty($meta['height'])
        ? $meta['width'] . ' × ' . $meta['height'] . ' px'
        : '';

    return [
        'label' => size_format($bytes, 1), // z. B. «1.4 MB»
        'dimensions' => $dimensions,
        'too_large' => $limit !== null && $bytes > $limit * MB_IN_BYTES,
        'limit' => $limit ? $limit . ' MB' : '',
    ];
}

// -> 1. Listenansicht: Spalte «Dateigrösse» nach dem Titel
add_filter('manage_media_columns', function (array $columns): array {
    $new = [];
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        if ($key === 'title') {
            $new['media_size'] = 'Dateigrösse';
        }
    }
    return $new;
});

add_action('manage_media_custom_column', function (string $column, int $post_id): void {
    if ($column !== 'media_size') {
        return;
    }
    $info = get_media_size_info($post_id);
    if (!$info) {
        echo '–';
        return;
    }
    echo '<span class="media-size' . ($info['too_large'] ? ' is-too-large' : '') . '">' . esc_html($info['label']) . '</span>';
    if ($info['dimensions']) {
        echo '<br><span class="media-size-dimensions">' . esc_html($info['dimensions']) . '</span>';
    }
    if ($info['too_large']) {
        echo '<br><span class="media-size-warning">Zu gross (über ' . esc_html($info['limit']) . ')</span>';
    }
}, 10, 2);

// -> 2. Kachelansicht: Angaben an die Daten jeder Kachel anhängen (die Kacheln baut WordPress per JavaScript)
add_filter('wp_prepare_attachment_for_js', function (array $response, WP_Post $attachment): array {
    // Nur in der Mediathek selbst (upload.php), nicht in jedem Auswahlfenster
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $referer = wp_get_referer() ?: '';
    if (($screen && $screen->id === 'upload') || str_contains($referer, 'upload.php')) {
        $response['mediaSize'] = get_media_size_info($attachment->ID);
    }
    return $response;
}, 10, 2);

// -> CSS und JS nur auf der Mediathek laden
add_action('admin_enqueue_scripts', function (string $hook): void {
    if ($hook !== 'upload.php') {
        return;
    }
    $dir = get_template_directory();
    $uri = get_template_directory_uri();

    wp_enqueue_style(
        'portfolio-admin-media-size',
        $uri . '/backend/admin/media-size.css',
        [],
        (string) filemtime($dir . '/backend/admin/media-size.css')
    );
    wp_enqueue_script(
        'portfolio-admin-media-size',
        $uri . '/backend/admin/media-size.js',
        ['media-views'],
        (string) filemtime($dir . '/backend/admin/media-size.js'),
        true
    );
});
