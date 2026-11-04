<?php
/**
 * Backend-Übersicht «Fotos» (Fotos > Alle Fotos):
 * 1. Spalten: Vorschaubild links vom Titel, dazu «Grösse im Raster» und «Bildausschnitt»
 * 2. QuickEdit: Grösse und Ausschnitt direkt in der Liste ändern, ohne das Foto zu öffnen
 *
 * Dokumentation:
 * https://developer.wordpress.org/reference/hooks/manage_post_type_posts_columns/
 * https://developer.wordpress.org/reference/hooks/quick_edit_custom_box/
 */

// -> 1. Spalten festlegen: Vorschaubild vor dem Titel, Grösse und Ausschnitt danach
add_filter('manage_photo_posts_columns', function (array $columns): array {
    $new = [];
    foreach ($columns as $key => $label) {
        if ($key === 'title') {
            $new['photo_thumb'] = 'Bild';
        }
        $new[$key] = $label;
        if ($key === 'title') {
            $new['photo_size'] = 'Grösse im Raster';
            $new['photo_focus'] = 'Bildausschnitt';
        }
    }
    return $new;
});

// -> Spalten befüllen
add_action('manage_photo_posts_custom_column', function (string $column, int $post_id): void {
    switch ($column) {
        case 'photo_thumb':
            $image_id = (int) carbon_get_post_meta($post_id, 'image');
            if ($image_id) {
                // wp_get_attachment_image() erzeugt ein vollständiges, escaptes <img> (inkl. srcset)
                echo wp_get_attachment_image($image_id, 'xxs', false, ['class' => 'photo-thumb', 'alt' => '']);
            }
            break;

        case 'photo_size':
            $options = get_photo_size_options();
            $value = carbon_get_post_meta($post_id, 'grid_size');
            $value = array_key_exists($value, $options) ? $value : 'auto';
            echo esc_html($options[$value]);
            // Wert für QuickEdit (photo-list.js liest ihn aus und setzt die Auswahl vor)
            echo '<span class="hidden" data-photo-size="' . esc_attr($value) . '"></span>';
            break;

        case 'photo_focus':
            $options = get_photo_focus_options();
            $value = carbon_get_post_meta($post_id, 'focus');
            $value = array_key_exists($value, $options) ? $value : 'center';
            echo esc_html($options[$value]);
            echo '<span class="hidden" data-photo-focus="' . esc_attr($value) . '"></span>';
            break;
    }
}, 10, 2);

// -> 2. QuickEdit: zwei Auswahlfelder (wird pro Spalte aufgerufen, darum nur bei «photo_size» ausgeben)
add_action('quick_edit_custom_box', function (string $column, string $post_type): void {
    if ($post_type !== 'photo' || $column !== 'photo_size') {
        return;
    }
    // Nonce: Schutz vor gefälschten Anfragen (CSRF)
    wp_nonce_field('photo_quick_edit', 'photo_quick_edit_nonce');
    ?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <label>
                <span class="title">Grösse</span>
                <select name="photo_grid_size">
                    <?php foreach (get_photo_size_options() as $value => $label) : ?>
                        <option value="<?= esc_attr($value) ?>"><?= esc_html($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span class="title">Ausschnitt</span>
                <select name="photo_focus">
                    <?php foreach (get_photo_focus_options() as $value => $label) : ?>
                        <option value="<?= esc_attr($value) ?>"><?= esc_html($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
    </fieldset>
    <?php
}, 10, 2);

// -> QuickEdit speichern (nur wenn das QuickEdit-Formular abgeschickt wurde)
add_action('save_post_photo', function (int $post_id): void {
    $nonce = isset($_POST['photo_quick_edit_nonce']) ? sanitize_text_field(wp_unslash($_POST['photo_quick_edit_nonce'])) : '';
    if (!$nonce || !wp_verify_nonce($nonce, 'photo_quick_edit')) {
        return;
    }
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    // Nur erlaubte Werte speichern
    $size = isset($_POST['photo_grid_size']) ? sanitize_key(wp_unslash($_POST['photo_grid_size'])) : '';
    if (array_key_exists($size, get_photo_size_options())) {
        carbon_set_post_meta($post_id, 'grid_size', $size);
    }

    $focus = isset($_POST['photo_focus']) ? sanitize_key(wp_unslash($_POST['photo_focus'])) : '';
    if (array_key_exists($focus, get_photo_focus_options())) {
        carbon_set_post_meta($post_id, 'focus', $focus);
    }
});

// -> CSS und JS nur auf der Übersicht «Fotos» laden
add_action('admin_enqueue_scripts', function (string $hook): void {
    $screen = get_current_screen();
    if ($hook !== 'edit.php' || !$screen || $screen->post_type !== 'photo') {
        return;
    }
    $dir = get_template_directory();
    $uri = get_template_directory_uri();

    wp_enqueue_style(
        'portfolio-admin-photo-list',
        $uri . '/backend/admin/photo-list.css',
        [],
        (string) filemtime($dir . '/backend/admin/photo-list.css')
    );
    // Abhängigkeit «inline-edit-post»: das QuickEdit-Script von WordPress
    wp_enqueue_script(
        'portfolio-admin-photo-list',
        $uri . '/backend/admin/photo-list.js',
        ['inline-edit-post'],
        (string) filemtime($dir . '/backend/admin/photo-list.js'),
        true
    );
});
