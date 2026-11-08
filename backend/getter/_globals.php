<?php
// -> Getter für die globalen Einstellungen (Globales)

// Logo = Schriftzug im Header
function get_global_logo(): string
{
    return carbon_get_theme_option('logo') ?: get_bloginfo('name');
}

// Signet (Handy) aus der Mediathek; false, wenn leer
// SVG hat keine Bildgrössen: get_media_by_id() liefert dann die Datei-URL direkt
function get_global_logo_signet(): array|false
{
    return get_media_by_id(carbon_get_theme_option('logo_signet'), 'xs');
}

// Copyright im Footer
function get_global_copyright(): string
{
    return carbon_get_theme_option('copyright') ?: '';
}

// E-Mail-Adresse; false, wenn leer oder ungültig
function get_global_email(): string|false
{
    $email = sanitize_email(carbon_get_theme_option('email') ?: '');
    return is_email($email) ? $email : false;
}

// Gemeinsamer Hintergrund fürs Overlay-Menü (Bild und/oder Video); false, wenn beides leer ist
// → dann gelten die Hintergründe der einzelnen Menüpunkte
function get_global_menu_background(): array|false
{
    $image = get_media_by_id(carbon_get_theme_option('menu_bg_image'), 'l');
    $video = get_video_by_id(carbon_get_theme_option('menu_bg_video'));
    if (!$image && !$video) {
        return false;
    }
    return ['image' => $image, 'video' => $video];
}

// Socials; mit $only_with_icon = true nur Einträge mit Icon (für den Footer)
function get_global_socials(bool $only_with_icon = false): array
{
    $socials = carbon_get_theme_option('socials') ?: [];
    $socials = array_map(function ($item) {
        $item['icon'] = get_media_by_id($item['icon'] ?? 0, 'xxs');
        return $item;
    }, $socials);

    if ($only_with_icon) {
        $socials = array_filter($socials, function ($item) {
            return !empty($item['icon']);
        });
    }
    return array_values($socials);
}
