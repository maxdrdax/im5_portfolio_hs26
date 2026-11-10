<?php


/**
 * Website nur für angemeldete Benutzer (Phase vor dem Livegang)
 * Schalter: Globales > «Website nur für angemeldete Benutzer» (Feld site_private).
 * Ist er aktiv, sehen Besucher ohne Login nur die WordPress-Anmeldung. Testzugänge bekommen die
 * Rolle «Abonnent» (darf nichts ändern). Beim Livegang ausschalten (manuals/go-live-checkliste.md).
 *
 * Hinweis: Dateien aus der Mediathek (wp-content/uploads) bleiben über ihre genaue URL erreichbar.
 */

// -> Ist der Schutz aktiv und die Person nicht eingeloggt?
function is_private_mode_blocked(): bool
{
    return function_exists('carbon_get_theme_option') && get_global_site_private() && !is_user_logged_in();
}

// -> Seiten, Projekte, Feeds usw.: zur Anmeldung umleiten
// template_redirect läuft vor jeder Ausgabe im Frontend (nicht im Backend und nicht auf der Anmeldeseite).
// auth_redirect() leitet zu wp-login.php und nach dem Login zurück auf die gewünschte Seite.
add_action('template_redirect', function (): void {
    if (is_private_mode_blocked()) {
        auth_redirect();
    }
});

// -> Sitemaps sperren (Yoast: /sitemap_index.xml, /project-sitemap.xml usw.)
// Yoast gibt die Sitemap aus, bevor template_redirect läuft. Darum greift der Schutz oben dort nicht,
// und die Sitemap würde alle Seiten- und Projekt-URLs verraten. parse_request läuft früher.
add_action('parse_request', function (): void {
    if (!is_private_mode_blocked()) {
        return;
    }
    $path = (string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (preg_match('#sitemap[^/]*\.xml$#i', $path)) {
        auth_redirect();
    }
});

// -> REST-API sperren: Sonst könnte man Inhalte (z. B. Projekte) ohne Login über /wp-json/ abrufen.
// Eingeloggte (auch das Backend und Carbon Fields) sind nicht betroffen.
add_filter('rest_authentication_errors', function ($result) {
    if (!empty($result) || !is_private_mode_blocked()) {
        return $result;
    }
    return new WP_Error('rest_private', 'Die Website ist noch nicht öffentlich.', ['status' => 401]);
});

// -> Anwendungspasswörter abschalten
// Sie sind nur für externe Programme gedacht (Zugriff per REST-API/XML-RPC ohne normales Login).
// Diese Website braucht sie nicht: Abschalten entfernt einen möglichen Angriffsweg und den Hinweis
// «Basis-Authentifizierung» im Benutzerprofil. Das normale Login funktioniert unverändert.
add_filter('wp_is_application_passwords_available', '__return_false');

// -> Admin-Leiste im Frontend nur für Personen, die Inhalte bearbeiten
// Testzugänge (Rolle «Abonnent») sehen die Website wie normale Besucher, ohne schwarze Leiste oben.
add_filter('show_admin_bar', function (bool $show): bool {
    return current_user_can('edit_posts') ? $show : false;
});
