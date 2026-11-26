<?php
/**
 * Reihenfolge der Fotokategorien per Drag-and-Drop (Fotos > Kategorien)
 *
 * Die Reihenfolge bestimmt die Filter auf der Seite Fotos (sort_terms_by_order() in backend/helpers/index.php).
 * Gespeichert wird sie als Term Meta «sort_order» (1, 2, 3 …) direkt an der Kategorie.
 * Ziehen: jquery-ui-sortable (in WordPress enthalten, kein Plugin), Speichern per AJAX mit Nonce.
 * Neue Kategorien kommen automatisch ans Ende.
 *
 * Dokumentation:
 * https://developer.wordpress.org/reference/hooks/wp_ajax_action/
 * https://developer.wordpress.org/reference/hooks/created_taxonomy/
 */

const TERM_ORDER_TAXONOMY = 'photo_category';

// -> Ist die Kategorien-Liste in der eigenen Reihenfolge? (nicht nach Name/Anzahl sortiert, keine Suche)
function is_term_order_view(): bool
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    return $screen
        && $screen->id === 'edit-' . TERM_ORDER_TAXONOMY
        && empty($_GET['orderby'])
        && empty($_GET['s']);
}

// -> Neue Kategorie ans Ende setzen (höchste bisherige Position + 1)
add_action('created_' . TERM_ORDER_TAXONOMY, function (int $term_id): void {
    $terms = get_terms(['taxonomy' => TERM_ORDER_TAXONOMY, 'hide_empty' => false, 'fields' => 'ids']);
    $max = 0;
    foreach (is_wp_error($terms) ? [] : $terms as $id) {
        $max = max($max, (int) get_term_meta((int) $id, 'sort_order', true));
    }
    update_term_meta($term_id, 'sort_order', $max + 1);
});

// -> Liste im Backend in der eigenen Reihenfolge anzeigen (wie die Filter auf der Website)
add_filter('get_terms', function ($terms, $taxonomies, $args) {
    if (
        !is_admin()
        || !is_array($terms)
        || ($args['fields'] ?? 'all') !== 'all'
        || !in_array(TERM_ORDER_TAXONOMY, (array) $taxonomies, true)
        || !is_term_order_view()
    ) {
        return $terms;
    }
    return sort_terms_by_order($terms);
}, 10, 3);

// -> Spalte mit Griff zum Ziehen (vor dem Namen)
add_filter('manage_edit-' . TERM_ORDER_TAXONOMY . '_columns', function (array $columns): array {
    $new = [];
    foreach ($columns as $key => $label) {
        if ($key === 'name') {
            $new['term_order'] = '<span class="screen-reader-text">Reihenfolge</span>';
        }
        $new[$key] = $label;
    }
    return $new;
});

add_filter('manage_' . TERM_ORDER_TAXONOMY . '_custom_column', function (string $output, string $column): string {
    if ($column !== 'term_order') {
        return $output;
    }
    return '<span class="term-order-handle dashicons dashicons-menu" title="Ziehen, um die Reihenfolge zu ändern" aria-hidden="true"></span>';
}, 10, 2);

// -> Script und Styles nur auf dieser Liste laden
add_action('admin_enqueue_scripts', function (string $hook): void {
    if ($hook !== 'edit-tags.php' || !is_term_order_view()) {
        return;
    }
    $dir = get_template_directory();
    $uri = get_template_directory_uri();

    wp_enqueue_style(
        'portfolio-admin-term-order',
        $uri . '/backend/admin/term-order.css',
        [],
        (string) filemtime($dir . '/backend/admin/term-order.css')
    );
    wp_enqueue_script(
        'portfolio-admin-term-order',
        $uri . '/backend/admin/term-order.js',
        ['jquery', 'jquery-ui-sortable', 'wp-a11y'],
        (string) filemtime($dir . '/backend/admin/term-order.js'),
        true
    );
    // Daten für das Script: Adresse für AJAX und Nonce (Schutz vor gefälschten Anfragen)
    wp_localize_script('portfolio-admin-term-order', 'termOrder', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('term_order'),
    ]);
});

// -> Reihenfolge speichern (admin-ajax.php?action=term_order, nur eingeloggt)
add_action('wp_ajax_term_order', function (): void {
    check_ajax_referer('term_order', 'nonce');
    $taxonomy = get_taxonomy(TERM_ORDER_TAXONOMY);
    if (!$taxonomy || !current_user_can($taxonomy->cap->edit_terms)) {
        wp_send_json_error('Keine Berechtigung.', 403);
    }

    $ids = array_map('intval', (array) ($_POST['order'] ?? []));
    $position = 1;
    foreach ($ids as $id) {
        // Nur echte Fotokategorien speichern
        $term = get_term($id, TERM_ORDER_TAXONOMY);
        if ($term instanceof WP_Term) {
            update_term_meta($id, 'sort_order', $position++);
        }
    }
    wp_send_json_success();
});
