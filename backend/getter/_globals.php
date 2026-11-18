<?php
// -> Getter für die globalen Einstellungen (Globales)

// Logo = Schriftzug im Header
function get_global_logo(): string
{
    return carbon_get_theme_option('logo') ?: get_bloginfo('name');
}

// Website nur für angemeldete Benutzer? (Phase vor dem Livegang)
function get_global_site_private(): bool
{
    return (bool) carbon_get_theme_option('site_private');
}

// Signet (Handy) aus der Mediathek; false, wenn leer
// SVG hat keine Bildgrössen: get_media_by_id() liefert dann die Datei-URL direkt
function get_global_logo_signet(): array|false
{
    return get_media_by_id(carbon_get_theme_option('logo_signet'), 'xs');
}

// Zwischentitel der Projektseite (Standard für alle Projekte); leer = fester Text im Theme
function get_global_project_titles(): array
{
    return [
        'description' => carbon_get_theme_option('description_title') ?: 'Projektbeschrieb',
        'role' => carbon_get_theme_option('role_title') ?: 'Meine Rolle',
        'video' => carbon_get_theme_option('video_title') ?: 'Video',
        'gallery' => carbon_get_theme_option('gallery_title') ?: 'Einblicke',
        'links' => carbon_get_theme_option('links_title') ?: 'Weiterführende Informationen',
        'prototype' => carbon_get_theme_option('prototype_title') ?: 'Prototyp',
    ];
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
// Icon der Social-Media-Plattform zu einem Link (z. B. instagram.com/… → Instagram-Icon aus den Socials);
// false, wenn der Link zu keiner Plattform in den Socials passt oder diese kein Icon hat.
// Verglichen wird die Domain ohne «www.». Grösse xs: wird in der Hover-Vorschau grösser gezeigt als im Footer.
function get_social_icon_for_url(string $url): array|false
{
    $normalize = function (string $address): string {
        return preg_replace('/^www\./', '', strtolower((string) wp_parse_url($address, PHP_URL_HOST)));
    };
    $host = $normalize($url);
    if ($host === '') {
        return false;
    }
    foreach (carbon_get_theme_option('socials') ?: [] as $social) {
        if (!empty($social['icon']) && $normalize($social['url'] ?? '') === $host) {
            return get_media_by_id($social['icon'], 'xs');
        }
    }
    return false;
}

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
