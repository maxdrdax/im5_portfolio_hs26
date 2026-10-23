<?php

// todo: Hier die Getter für die Globalen Informationen definieren
function get_global_logo() {
    $logo_id = carbon_get_theme_option('logo');
    return $logo_id ? get_media_by_id($logo_id) : false;
}

function get_global_logo_link() {
    return carbon_get_theme_option('logo_link') ?: home_url('/');
}

function get_global_footer_copyright() {
    return carbon_get_theme_option('footer_copyright');
}

function get_global_socials() {
    $socials = carbon_get_theme_option('socials') ?: [];
    return array_map(function ($item) {
        $item ['icon'] = get_media_by_id($item['icon']);
        return $item;
        }, $socials);
}
