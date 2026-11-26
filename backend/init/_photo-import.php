<?php
/**
 * Mehrere Fotos auf einmal anlegen
 *
 * Mediathek (Listenansicht) > Bilder anhaken > Mehrfachaktion «Als Fotos anlegen».
 * Pro Bild entsteht ein Beitrag im Post Type «Foto» (veröffentlicht, Grösse «Automatisch», Ausschnitt «Mitte»).
 * Titel = Alt-Text des Bildes, sonst der Bildtitel aus der Mediathek (meist der Dateiname).
 * Bilder, die schon als Foto angelegt sind, werden übersprungen (keine Doppelten).
 * Kategorie gleich mitgeben: Im Dropdown steht pro Fotokategorie ein Eintrag (z. B. «Natur»).
 */

// -> Einträge im Dropdown «Mehrfachaktionen» der Mediathek
// Ein Array als Wert zeigt WordPress als Gruppe (optgroup) an: «Als Fotos anlegen» > Ohne Kategorie, Menschen, Natur …
add_filter('bulk_actions-upload', function (array $actions): array {
    if (!current_user_can('publish_posts')) {
        return $actions;
    }
    $group = ['create_photos' => 'Ohne Kategorie'];
    $terms = get_terms(['taxonomy' => 'photo_category', 'hide_empty' => false]);
    foreach (is_wp_error($terms) ? [] : sort_terms_by_order($terms) as $term) {
        // Aktion «create_photos__12» = anlegen mit Kategorie-ID 12
        $group['create_photos__' . $term->term_id] = $term->name;
    }
    $actions['Als Fotos anlegen'] = $group;
    return $actions;
});

// -> Aktion ausführen: WordPress hat den Nonce (Sicherheitstoken) der Liste schon geprüft
add_filter('handle_bulk_actions-upload', function (string $redirect, string $action, array $ids): string {
    if (!preg_match('/^create_photos(?:__(\d+))?$/', $action, $match) || !current_user_can('publish_posts')) {
        return $redirect;
    }

    // Gewählte Kategorie (nur wenn es sie wirklich gibt)
    $term_id = isset($match[1]) ? (int) $match[1] : 0;
    if ($term_id && !term_exists($term_id, 'photo_category')) {
        $term_id = 0;
    }

    $created = 0;
    $skipped = 0;

    foreach (array_map('intval', $ids) as $attachment_id) {
        // Nur Bilder (keine Videos, PDFs usw.)
        if (!wp_attachment_is_image($attachment_id)) {
            $skipped++;
            continue;
        }

        // Schon als Foto vorhanden? Carbon Fields speichert das Feld «image» als Meta «_image».
        $existing = get_posts([
            'post_type' => 'photo',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'meta_key' => '_image',
            'meta_value' => $attachment_id,
        ]);
        if ($existing) {
            $skipped++;
            continue;
        }

        // Titel: Alt-Text, sonst Bildtitel
        $title = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
        if ($title === '') {
            $title = get_the_title($attachment_id);
        }

        $photo_id = wp_insert_post([
            'post_type' => 'photo',
            'post_status' => 'publish',
            'post_title' => sanitize_text_field($title),
        ], true);
        if (is_wp_error($photo_id)) {
            $skipped++;
            continue;
        }

        // Felder über Carbon Fields setzen (gleiche Speicherung wie beim Bearbeiten von Hand)
        carbon_set_post_meta($photo_id, 'image', $attachment_id);
        carbon_set_post_meta($photo_id, 'grid_size', 'auto');
        carbon_set_post_meta($photo_id, 'focus', 'center');
        if ($term_id) {
            wp_set_object_terms($photo_id, [$term_id], 'photo_category');
        }
        $created++;
    }

    return add_query_arg([
        'photos_created' => $created,
        'photos_skipped' => $skipped,
        'photos_term' => $term_id,
    ], $redirect);
}, 10, 3);

// -> Rückmeldung oben in der Mediathek
add_action('admin_notices', function (): void {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'upload' || !isset($_GET['photos_created'])) {
        return;
    }
    $created = absint($_GET['photos_created']);
    $skipped = absint($_GET['photos_skipped'] ?? 0);

    $term = get_term(absint($_GET['photos_term'] ?? 0), 'photo_category');
    $has_term = $term instanceof WP_Term;

    $message = $has_term
        ? sprintf('%d Foto(s) in «%s» angelegt.', $created, $term->name)
        : sprintf('%d Foto(s) angelegt.', $created);
    if ($skipped > 0) {
        $message .= sprintf(' %d übersprungen (kein Bild oder schon als Foto vorhanden).', $skipped);
    }
    // Weiter zur Seite «Alle Fotos»; ohne Kategorie gefiltert auf «Ohne Kategorie» (= die neuen)
    $link = admin_url('edit.php?post_type=photo&page=photo-bulk-edit' . ($has_term ? '' : '&uncategorized=1'));

    printf(
        '<div class="notice notice-success is-dismissible"><p>%s <a href="%s">Jetzt bearbeiten</a></p></div>',
        esc_html($message),
        esc_url($link)
    );
});

// -> Hinweis in der Liste «Fotos»: wie man mehrere auf einmal anlegt
add_action('admin_notices', function (): void {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'edit-photo') {
        return;
    }
    printf(
        '<div class="notice notice-info"><p>Mehrere Fotos auf einmal: In der <a href="%s">Mediathek (Listenansicht)</a> Bilder anhaken und unter «Als Fotos anlegen» die Kategorie wählen. Titel, Kategorien, Grösse und Ausschnitt danach unter <a href="%s">Alle Fotos</a> auf einmal setzen.</p></div>',
        esc_url(admin_url('upload.php?mode=list')),
        esc_url(admin_url('edit.php?post_type=photo&page=photo-bulk-edit&uncategorized=1'))
    );
});
