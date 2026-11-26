<?php
/**
 * Sicherheits-Header für alle Seiten im Frontend
 *
 * HTTP-Header sind Anweisungen an den Browser, die der Server vor dem Inhalt mitschickt.
 * Der Hook «send_headers» läuft bei jeder Frontend-Seite, bevor etwas ausgegeben wird.
 * Backend und Login setzen ihren Frame-Schutz schon selbst (WordPress-Standard).
 *
 * Bewusst KEINE strenge Content Security Policy (Quellen für Scripts/Styles):
 * Sie würde YouTube/Vimeo-Einbettungen, die Import-Map von WordPress (Script-Module)
 * und die Inline-Styles (--ratio, --logo-ratio) blockieren.
 *
 * Testen: Entwicklertools > Netzwerk > Dokument > Antwort-Header.
 */

add_action('send_headers', function (): void {
    // Clickjacking-Schutz: Nur die eigene Website darf diese Seite in einem iframe zeigen.
    // X-Frame-Options für ältere Browser, frame-ancestors (CSP) für neuere.
    header('X-Frame-Options: SAMEORIGIN');

    // Schlanke CSP, blockiert nichts, was die Website braucht:
    // frame-ancestors = Frame-Schutz, object-src = keine alten Plugins (Flash usw.),
    // base-uri = kein fremdes <base>-Tag, form-action = Formulare nur an die eigene Domain.
    header("Content-Security-Policy: frame-ancestors 'self'; object-src 'none'; base-uri 'self'; form-action 'self'");

    // Dateityp nicht erraten: Eine als Bild ausgelieferte Datei wird nie als Script ausgeführt.
    header('X-Content-Type-Options: nosniff');

    // Externe Links erfahren nur die Domain, nicht die genaue Unterseite.
    // (YouTube braucht die Domain, darum nicht «no-referrer».)
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Kamera, Mikrofon und Standort sperren (braucht die Website nicht).
    // Vollbild und Autoplay für Videos bleiben erlaubt.
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    // HSTS: Browser lädt die Seite künftig direkt über HTTPS (auch die allererste Anfrage).
    // Nur senden, wenn die Seite schon über HTTPS kommt.
    // Vorerst 1 Tag (86400 s) zum Testen; nach einer Woche ohne Probleme auf 1 Jahr (31536000) erhöhen.
    // Ohne includeSubDomains (Subdomains von Hostpoint nicht unter eigener Kontrolle), ohne preload.
    if (is_ssl()) {
        header('Strict-Transport-Security: max-age=86400');
    }
});
