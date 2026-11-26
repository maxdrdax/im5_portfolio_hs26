<?php
/**
 * Fotos > Alle bearbeiten: alle Fotos auf einer Seite bearbeiten und mit einem Klick speichern
 *
 * Pro Foto: Vorschaubild, Titel (= Alt-Text), Kategorien, Grösse im Raster, Bildausschnitt.
 * Die Schnellbearbeitung von WordPress öffnet nur eine Zeile aufs Mal. Diese Seite ist ein
 * normales Formular mit allen Fotos, gespeichert wird über admin-post.php.
 * Gespeichert wird nur, was sich geändert hat.
 *
 * Hinweis: PHP nimmt pro Formular nur eine begrenzte Anzahl Felder an (max_input_vars, meist 1000).
 * Bei rund 5 Feldern pro Foto reicht das für etwa 150 Fotos. Bei mehr: Filter «Ohne Kategorie» nutzen.
 */

const PHOTO_BULK_SLUG = 'photo-bulk-edit';
// Adresse der alten Liste (Schnellbearbeitung, Löschen, Sortieren). Der Zusatz photo_view=list verhindert die Umleitung unten.
const PHOTO_LIST_SLUG = 'edit.php?post_type=photo&photo_view=list';

// -> Menü «Fotos»: «Alle Fotos» = diese Seite (zuoberst), die alte Liste heisst «Liste»
add_action('admin_menu', function (): void {
    add_submenu_page(
        'edit.php?post_type=photo',
        'Alle Fotos',
        'Alle Fotos',
        'edit_posts',
        PHOTO_BULK_SLUG,
        'render_photo_bulk_edit_page',
        0 // Position: zuoberst im Untermenü
    );
});

add_action('admin_menu', function (): void {
    global $submenu;
    foreach ($submenu['edit.php?post_type=photo'] ?? [] as $key => $item) {
        if ($item[2] === 'edit.php?post_type=photo') {
            $submenu['edit.php?post_type=photo'][$key][0] = 'Liste';
            $submenu['edit.php?post_type=photo'][$key][2] = PHOTO_LIST_SLUG;
        }
    }
}, 999);

// -> Liste: im Untermenü «Liste» markieren (WordPress kennt die geänderte Adresse sonst nicht)
add_filter('submenu_file', function ($submenu_file) {
    $screen = get_current_screen();
    return ($screen && $screen->id === 'edit-photo') ? PHOTO_LIST_SLUG : $submenu_file;
});

// -> Klick auf «Fotos» im Menü (edit.php?post_type=photo ohne weitere Angaben) öffnet diese Seite.
// Mit weiteren Angaben (Seitenzahl, Filter, Meldungen nach dem Löschen usw.) bleibt die Liste.
add_action('load-edit.php', function (): void {
    $query = $_GET;
    if (($query['post_type'] ?? '') === 'photo' && count($query) === 1) {
        wp_safe_redirect(admin_url('edit.php?post_type=photo&page=' . PHOTO_BULK_SLUG));
        exit;
    }
});

// -> In der Liste: Link «Alle» führt auf die Liste, nicht auf diese Seite
add_filter('views_edit-photo', function (array $views): array {
    if (isset($views['all'])) {
        $views['all'] = str_replace(
            'href="edit.php?post_type=photo"',
            'href="' . esc_url(admin_url(PHOTO_LIST_SLUG)) . '"',
            $views['all']
        );
    }
    return $views;
});

// -> Fotos für die Seite (gleiche Reihenfolge wie get_photos(), auch Entwürfe)
function get_photo_bulk_ids(bool $only_uncategorized): array
{
    $args = [
        'post_type' => 'photo',
        'post_status' => ['publish', 'draft', 'pending', 'private'],
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'orderby' => 'date',
        'order' => 'DESC',
    ];
    if ($only_uncategorized) {
        $args['tax_query'] = [[
            'taxonomy' => 'photo_category',
            'operator' => 'NOT EXISTS',
        ]];
    }
    return get_posts($args);
}

// -> Seite ausgeben
function render_photo_bulk_edit_page(): void
{
    if (!current_user_can('edit_posts')) {
        return;
    }

    $only_uncategorized = isset($_GET['uncategorized']);
    $ids = get_photo_bulk_ids($only_uncategorized);
    $terms = get_terms(['taxonomy' => 'photo_category', 'hide_empty' => false]);
    $terms = is_wp_error($terms) ? [] : sort_terms_by_order($terms);
    $base_url = admin_url('edit.php?post_type=photo&page=' . PHOTO_BULK_SLUG);
    ?>
    <div class="wrap photo-bulk">
        <h1 class="wp-heading-inline">Alle Fotos</h1>
        <a class="page-title-action" href="<?= esc_url(admin_url('upload.php?mode=list')) ?>">Mehrere aus der Mediathek anlegen</a>
        <a class="page-title-action" href="<?= esc_url(admin_url('post-new.php?post_type=photo')) ?>">Neues Foto</a>
        <a class="page-title-action" href="<?= esc_url(admin_url(PHOTO_LIST_SLUG)) ?>">Liste (Löschen, Schnellbearbeitung)</a>
        <hr class="wp-header-end">

        <?php if (isset($_GET['updated'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?= esc_html(sprintf('%d Foto(s) gespeichert.', absint($_GET['updated']))) ?></p>
            </div>
        <?php endif; ?>

        <ul class="subsubsub">
            <li><a href="<?= esc_url($base_url) ?>" <?= $only_uncategorized ? '' : 'class="current"' ?>>Alle</a> |</li>
            <li><a href="<?= esc_url(add_query_arg('uncategorized', '1', $base_url)) ?>" <?= $only_uncategorized ? 'class="current"' : '' ?>>Ohne Kategorie</a></li>
        </ul>

        <?php if (!$ids) : ?>
            <p class="photo-bulk__empty">Keine Fotos gefunden.</p>
        <?php else : ?>
            <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
                <input type="hidden" name="action" value="photo_bulk_save">
                <input type="hidden" name="redirect" value="<?= esc_attr($only_uncategorized ? add_query_arg('uncategorized', '1', $base_url) : $base_url) ?>">
                <?php wp_nonce_field('photo_bulk_save', 'photo_bulk_nonce'); ?>

                <p class="photo-bulk__actions">
                    <?php submit_button('Alle speichern', 'primary', 'submit', false); ?>
                    <span class="description"><?= esc_html(count($ids)) ?> Foto(s). Der Titel ist der Alt-Text.</span>
                </p>

                <table class="widefat striped photo-bulk__table">
                    <thead>
                        <tr>
                            <th class="photo-bulk__col-image">Bild</th>
                            <th>Titel (Alt-Text)</th>
                            <th>Kategorien</th>
                            <th>Grösse im Raster</th>
                            <th>Bildausschnitt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ids as $id) :
                            $image_id = (int) carbon_get_post_meta($id, 'image');
                            $size = carbon_get_post_meta($id, 'grid_size') ?: 'auto';
                            $focus = carbon_get_post_meta($id, 'focus') ?: 'center';
                            $current_terms = wp_get_object_terms($id, 'photo_category', ['fields' => 'ids']);
                            $current_terms = is_wp_error($current_terms) ? [] : array_map('intval', $current_terms);
                            $name = 'photos[' . $id . ']';
                            ?>
                            <tr>
                                <td class="photo-bulk__col-image">
                                    <?php if ($image_id) : ?>
                                        <a href="<?= esc_url(get_edit_post_link($id)) ?>" title="Foto einzeln bearbeiten">
                                            <?= wp_get_attachment_image($image_id, 'xs', false, ['class' => 'photo-bulk__thumb', 'alt' => '']) ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="text" class="widefat" name="<?= esc_attr($name . '[title]') ?>" value="<?= esc_attr(get_the_title($id)) ?>">
                                    <?php if (get_post_status($id) !== 'publish') : ?>
                                        <span class="photo-bulk__status"><?= esc_html(get_post_status_object(get_post_status($id))->label) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php foreach ($terms as $term) : ?>
                                        <label class="photo-bulk__term">
                                            <input type="checkbox" name="<?= esc_attr($name . '[cats][]') ?>" value="<?= esc_attr($term->term_id) ?>" <?php checked(in_array($term->term_id, $current_terms, true)); ?>>
                                            <?= esc_html($term->name) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <select name="<?= esc_attr($name . '[size]') ?>">
                                        <?php foreach (get_photo_size_options() as $value => $label) : ?>
                                            <option value="<?= esc_attr($value) ?>" <?php selected($size, $value); ?>><?= esc_html($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="<?= esc_attr($name . '[focus]') ?>">
                                        <?php foreach (get_photo_focus_options() as $value => $label) : ?>
                                            <option value="<?= esc_attr($value) ?>" <?php selected($focus, $value); ?>><?= esc_html($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p class="photo-bulk__actions">
                    <?php submit_button('Alle speichern', 'primary', 'submit', false); ?>
                </p>
            </form>
        <?php endif; ?>
    </div>
    <?php
}

// -> Speichern (admin-post.php?action=photo_bulk_save, nur für eingeloggte Benutzer)
add_action('admin_post_photo_bulk_save', function (): void {
    // Nonce: Schutz vor gefälschten Anfragen (CSRF)
    check_admin_referer('photo_bulk_save', 'photo_bulk_nonce');
    if (!current_user_can('edit_posts')) {
        wp_die('Keine Berechtigung.');
    }

    $photos = isset($_POST['photos']) && is_array($_POST['photos']) ? wp_unslash($_POST['photos']) : [];
    $sizes = get_photo_size_options();
    $focuses = get_photo_focus_options();
    $valid_terms = get_terms(['taxonomy' => 'photo_category', 'hide_empty' => false, 'fields' => 'ids']);
    $valid_terms = is_wp_error($valid_terms) ? [] : array_map('intval', $valid_terms);
    $updated = 0;

    foreach ($photos as $id => $data) {
        $id = (int) $id;
        // Nur echte Fotos, die die Person bearbeiten darf
        if (!is_array($data) || get_post_type($id) !== 'photo' || !current_user_can('edit_post', $id)) {
            continue;
        }
        $changed = false;

        // Titel
        $title = sanitize_text_field((string) ($data['title'] ?? ''));
        if ($title !== '' && $title !== get_post_field('post_title', $id, 'raw')) {
            wp_update_post(['ID' => $id, 'post_title' => $title]);
            $changed = true;
        }

        // Kategorien (nur bestehende Begriffe; keine angehakt = alle entfernen)
        $cats = array_map('intval', (array) ($data['cats'] ?? []));
        $cats = array_values(array_intersect($cats, $valid_terms));
        $current = wp_get_object_terms($id, 'photo_category', ['fields' => 'ids']);
        $current = is_wp_error($current) ? [] : array_map('intval', $current);
        sort($cats);
        sort($current);
        if ($cats !== $current) {
            wp_set_object_terms($id, $cats, 'photo_category');
            $changed = true;
        }

        // Grösse und Ausschnitt (nur erlaubte Werte)
        $size = sanitize_key((string) ($data['size'] ?? ''));
        if (array_key_exists($size, $sizes) && $size !== (carbon_get_post_meta($id, 'grid_size') ?: 'auto')) {
            carbon_set_post_meta($id, 'grid_size', $size);
            $changed = true;
        }
        $focus = sanitize_key((string) ($data['focus'] ?? ''));
        if (array_key_exists($focus, $focuses) && $focus !== (carbon_get_post_meta($id, 'focus') ?: 'center')) {
            carbon_set_post_meta($id, 'focus', $focus);
            $changed = true;
        }

        if ($changed) {
            $updated++;
        }
    }

    // Zurück zur Seite (nur interne Adressen erlaubt)
    $redirect = wp_validate_redirect(
        (string) wp_unslash($_POST['redirect'] ?? ''),
        admin_url('edit.php?post_type=photo&page=' . PHOTO_BULK_SLUG)
    );
    wp_safe_redirect(add_query_arg('updated', $updated, $redirect));
    exit;
});
