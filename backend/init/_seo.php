<?php
/**
 * Yoast SEO: Daten aus den Projektfeldern (Carbon Fields) an Yoast übergeben
 *
 * Yoast liest nur den normalen WordPress-Editor und das Beitragsbild. Projekte haben beides nicht,
 * ihre Inhalte stecken in Carbon Fields. Diese Datei hilft Yoast an zwei Stellen:
 * 1. Vorschaubild beim Teilen (og:image) = Titelbild «cover»
 * 2. Meta-Beschreibung = Anfang des ersten Moduls «Projektbeschrieb», falls keine eigene gesetzt ist
 *
 * Was in der Yoast-Box eines Projekts von Hand eingetragen ist, hat immer Vorrang.
 * Ist Yoast nicht aktiv, laufen die Filter nie (kein Fehler).
 * Ein- und ausschalten: Zeile require_once … '_seo.php' in backend/init/index.php.
 */

// Maximale Länge der automatischen Beschreibung (Google zeigt etwa 150–160 Zeichen)
const SEO_DESCRIPTION_LENGTH = 155;

// -> Kurzbeschreibung aus dem Projektbeschrieb erzeugen (ohne HTML, an einer Wortgrenze gekürzt)
// Quelle: das erste Modul «Projektbeschrieb» mit Text (Module: backend/carbonfields/_project.php)
function get_project_seo_description(int $id): string
{
    $text = '';
    foreach (carbon_get_post_meta($id, 'project_modules') ?: [] as $module) {
        if (($module['_type'] ?? '') === 'description' && has_rich_text((string) ($module['text'] ?? ''))) {
            $text = (string) $module['text'];
            break;
        }
    }
    $text = wp_strip_all_tags(strip_shortcodes($text));
    // &amp; usw. zurück in Zeichen, mehrere Leerzeichen/Umbrüche zu einem Leerzeichen
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', ' ', $text));

    if (mb_strlen($text) <= SEO_DESCRIPTION_LENGTH) {
        return $text;
    }

    $cut = mb_substr($text, 0, SEO_DESCRIPTION_LENGTH);
    $last_space = mb_strrpos($cut, ' ');
    if ($last_space !== false) {
        $cut = mb_substr($cut, 0, $last_space);
    }
    return rtrim($cut, ' ,.;:–-') . ' …';
}

// -> Gemeinsame Logik für Meta-Beschreibung und og:description
// Yoast escaped die Ausgabe selbst (esc_attr im Meta-Tag), darum hier reiner Text.
function seo_project_description_fallback($description): string
{
    $description = (string) $description;
    if ($description !== '' || !is_singular('project')) {
        return $description;
    }
    return get_project_seo_description(get_queried_object_id());
}

// -> <meta name="description"> und <meta property="og:description">
add_filter('wpseo_metadesc', 'seo_project_description_fallback');
add_filter('wpseo_opengraph_desc', 'seo_project_description_fallback');

// -> Yoast-Box im Editor ans Ende (unter die Inhaltsfelder von Carbon Fields)
add_filter('wpseo_metabox_prio', function (): string {
    return 'low';
});

// -> Vorschaubild beim Teilen (og:image, wird auch für X/Twitter verwendet)
// Yoast ruft diese Aktion auf, wenn es Bilder für eine Seite sammelt. Ist schon ein Bild da
// (z. B. von Hand in der Yoast-Box gesetzt), bleibt es dabei.
add_action('wpseo_add_opengraph_images', function ($image_container): void {
    if (!is_singular('project') || $image_container->has_images()) {
        return;
    }
    $cover_id = (int) carbon_get_post_meta(get_queried_object_id(), 'cover');
    if ($cover_id > 0) {
        $image_container->add_image_by_id($cover_id);
    }
});
