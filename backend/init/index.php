<?php
/**
 * In dieser Datei sind Funktionen drin, welche das Backend aufräumen
 * und optimieren. Diese Datei muss in der Regel nicht bearbeitet werden.
 */

// -> add image sizes
add_image_size('preload', 20, 20);
add_image_size('xxs', 100, 100);
add_image_size('xs', 300, 300);
add_image_size('s', 600, 600);
add_image_size('m', 1200, 1200);
add_image_size('l', 1800, 1800);
add_image_size('xl', 2800, 2800);

// -> enable block editor and clean up
add_filter('use_block_editor_for_post_type', function () {
    return false;
});
add_filter('init', function () {
    remove_post_type_support('page', 'editor');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
});
add_action(
    'wp_enqueue_scripts',
    function () {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('wc-blocks-style');
        wp_dequeue_style('global-styles');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
    },
    100,
);

// -> allow uploading svg images
function wp_init_extend_upload_mimes ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}

add_filter('upload_mimes', 'wp_init_extend_upload_mimes');

// -> remove posts from admin
function remove_posts_from_admin ($args, $post_type) {
    if ($post_type === 'post') {
        $args['public'] = false;
        $args['show_ui'] = false;
        $args['show_in_menu'] = false;
        $args['show_in_admin_bar'] = false;
        $args['show_in_nav_menus'] = false;
        $args['can_export'] = false;
        $args['has_archive'] = false;
        $args['exclude_from_search'] = true;
        $args['publicly_queryable'] = false;
        $args['show_in_rest'] = false;
    }
    return $args;
}

add_filter('register_post_type_args', 'remove_posts_from_admin', 10, 2);

// -> remove categories and tags from admin
function remove_categories_and_tags_from_admin ($args, $taxonomy) {
    if (in_array($taxonomy, ['category', 'post_tag'], true)) {
        $args['public'] = false;
        $args['show_ui'] = false;
        $args['show_in_menu'] = false;
        $args['show_in_nav_menus'] = false;
        $args['show_admin_column'] = false;
        $args['show_in_rest'] = false;
        $args['meta_box_cb'] = false;
    }
    return $args;
}

add_filter('register_taxonomy_args', 'remove_categories_and_tags_from_admin', 10, 2);

// -> remove wp comment functionality
function remove_comments_admin_menu (): void {
    remove_menu_page('edit-comments.php'); // remove comments
}

add_action('admin_menu', 'remove_comments_admin_menu');
function remove_comment_support (): void {
    remove_post_type_support('post', 'comments');
    remove_post_type_support('page', 'comments');
}

add_action('init', 'remove_comment_support', 100);
function remove_comments_in_admin_bar (): void {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}

add_action('wp_before_admin_bar_render', 'remove_comments_in_admin_bar');

// -> remove unused backend items
function remove_backend_items (): void {
    global $submenu;
    if (isset($submenu['themes.php'])) {
        foreach ($submenu['themes.php'] as $key => $item) {
            $slug = $item[2];
            if (
                str_contains($slug, 'customize.php') ||
                str_contains($slug, 'theme-editor.php') ||
                str_contains($slug, 'site-editor.php') ||
                str_contains($slug, 'font-library.php') ||
                str_contains($slug, 'post_type=wp_template') ||
                str_contains($slug, 'post_type=wp_font')
            ) {
                unset($submenu['themes.php'][$key]);
            }
        }
    }
}

add_action('admin_menu', 'remove_backend_items', 999);

// -> block direct access to disabled pages
function block_disabled_design_pages (): void {
    global $pagenow;
    $blocked_pages = [
        'theme-editor.php',
        'customize.php',
        'site-editor.php',
        'font-library.php'
    ];
    if (in_array($pagenow, $blocked_pages, true)) {
        wp_safe_redirect(admin_url('themes.php'));
        exit;
    }
}

add_action('admin_init', 'block_disabled_design_pages');
