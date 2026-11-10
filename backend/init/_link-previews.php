<?php
/**
 * Vorschaubilder für «Weiterführende Informationen» (Projekt) automatisch holen.
 *
 * Beim Speichern eines Projekts: Für jeden Link ohne eigenes Vorschaubild ruft der Server die Seite
 * einmal auf und sucht das Vorschaubild, das die Website selbst angibt (Open Graph: <meta property="og:image">,
 * dasselbe Bild, das WhatsApp oder LinkedIn beim Teilen zeigen). Das Bild wird in die Mediathek geladen.
 *
 * Warum beim Speichern und nicht beim Seitenaufruf: Fremde Server können langsam sein; Besucher sollen
 * nie darauf warten. Und weil das Bild danach auf dem eigenen Server liegt, fragt der Browser der
 * Besucher keinen fremden Server an (Datenschutz).
 *
 * Gespeichert wird eine Zuordnung «Link → Bild-ID» im versteckten Feld _link_previews (Unterstrich =
 * in WordPress nicht im Backend sichtbar). 0 = kein Bild gefunden (beim nächsten Speichern neuer Versuch).
 */

// Carbon Fields meldet, wenn die Felder eines Beitrags gespeichert wurden
add_action('carbon_fields_post_meta_container_saved', function ($post_id) {
    if (get_post_type($post_id) !== 'project' || wp_is_post_revision($post_id)) {
        return;
    }
    update_link_previews((int) $post_id);
});

// -> Vorschaubilder aller Links eines Projekts aktualisieren
function update_link_previews(int $post_id): void
{
    $saved = get_post_meta($post_id, '_link_previews', true) ?: [];
    $previews = [];

    // Links aus allen Modulen «Weiterführende Informationen» (backend/getter/_project.php)
    foreach (get_project_link_rows($post_id) as $link) {
        $url = esc_url_raw(trim($link['url'] ?? ''));
        // Eigenes Vorschaubild oder Logo der Plattform (Social-Media-Link) hat Vorrang: nichts holen
        if ($url === '' || !empty($link['image']) || get_social_icon_for_url($url)) {
            continue;
        }
        $key = md5($url);
        $existing = (int) ($saved[$key] ?? 0);
        // Schon geholt und noch in der Mediathek: wiederverwenden (kein doppelter Download)
        if ($existing && get_post($existing)) {
            $previews[$key] = $existing;
            continue;
        }
        $previews[$key] = fetch_link_preview($url, $post_id, trim($link['title'] ?? ''));
    }

    // Nur noch aktuelle Links behalten (gelöschte Links fallen raus; die Bilder bleiben in der Mediathek)
    update_post_meta($post_id, '_link_previews', $previews);
}

// -> Open-Graph-Bild einer Seite suchen und in die Mediathek laden; Rückgabe: Bild-ID oder 0
function fetch_link_preview(string $url, int $post_id, string $title): int
{
    // wp_safe_remote_get: blockiert Adressen im eigenen Netzwerk (Schutz vor Missbrauch des Servers).
    // Nur die ersten 300 KB lesen: Das <meta>-Tag steht im <head>, ganz oben.
    $response = wp_safe_remote_get($url, [
        'timeout' => 6,
        'redirection' => 3,
        'limit_response_size' => 300 * KB_IN_BYTES,
        'user-agent' => 'Mozilla/5.0 (compatible; Portfolio-Link-Preview)',
    ]);
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return 0;
    }

    $image_url = find_preview_image_url(wp_remote_retrieve_body($response), $url);
    if (!$image_url) {
        return 0;
    }

    // Funktionen für den Upload sind nur im Backend geladen
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Herunterladen in eine temporäre Datei (download_url prüft die Adresse gleich wie wp_safe_remote_get)
    $tmp = download_url($image_url, 10);
    if (is_wp_error($tmp)) {
        return 0;
    }

    // Dateityp am Inhalt erkennen, nicht an der Adresse (og:image hat oft keine Endung).
    // Nur echte Bilder erlauben, sonst Datei löschen.
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime = wp_get_image_mime($tmp);
    if (!isset($extensions[$mime])) {
        wp_delete_file($tmp);
        return 0;
    }

    $file = [
        'name' => 'link-vorschau-' . substr(md5($url), 0, 8) . '.' . $extensions[$mime],
        'tmp_name' => $tmp,
    ];
    $id = media_handle_sideload($file, $post_id, $title);
    if (is_wp_error($id)) {
        wp_delete_file($tmp);
        return 0;
    }

    // Alt-Text = Titel des Links; Herkunft merken (zur Nachvollziehbarkeit in der Mediathek)
    update_post_meta($id, '_wp_attachment_image_alt', $title);
    update_post_meta($id, '_link_preview_source', $url);
    return (int) $id;
}

// -> Bildadresse aus dem HTML lesen: zuerst og:image, sonst twitter:image
// Reihenfolge der Attribute ist auf Websites unterschiedlich (property vor content oder umgekehrt).
function find_preview_image_url(string $html, string $page_url): string
{
    foreach (['og:image', 'og:image:url', 'twitter:image'] as $name) {
        $quoted = preg_quote($name, '/');
        $patterns = [
            '/<meta[^>]+(?:property|name)=["\']' . $quoted . '["\'][^>]*content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+content=["\']([^"\']+)["\'][^>]*(?:property|name)=["\']' . $quoted . '["\']/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $match)) {
                // &amp; → &; relative Adressen (/bild.jpg) zur vollen Adresse ergänzen
                $image = WP_Http::make_absolute_url(html_entity_decode(trim($match[1])), $page_url);
                return esc_url_raw($image);
            }
        }
    }
    return '';
}
