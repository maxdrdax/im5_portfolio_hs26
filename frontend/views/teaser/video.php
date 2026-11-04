<?php
/**
 * Wiederverwendbares Video: immer ohne Ton und in Schleife.
 * Aufruf: get_template_part('frontend/views/teaser/video', null, [...])
 *
 * $args:
 * - video:  Array aus get_video_by_id() (Pflicht)
 * - mode:   'inview' = spielt, sobald sichtbar (Hero, Galerie)
 *           'hover'  = spielt nur beim Hover (Karussell, Directory)
 * - poster: Bild-URL als Vorschau (optional)
 * - class:  CSS-Klasse (optional)
 *
 * Gestartet wird per JavaScript (main.js). preload="none": Das Video lädt erst, wenn es gebraucht wird.
 */
$video = $args['video'] ?? false;
if (empty($video['src'])) {
    return;
}
$mode = ($args['mode'] ?? 'inview') === 'hover' ? 'hover' : 'inview';
$poster = $args['poster'] ?? '';
$class = $args['class'] ?? '';
?>

<video class="<?= esc_attr($class) ?>" data-video="<?= esc_attr($mode) ?>"
       muted loop playsinline preload="none" aria-hidden="true"
       <?php if (!empty($video['width']) && !empty($video['height'])) : ?>width="<?= esc_attr($video['width']) ?>" height="<?= esc_attr($video['height']) ?>"<?php endif; ?>
       <?php if ($poster) : ?>poster="<?= esc_url($poster) ?>"<?php endif; ?>>
    <source src="<?= esc_url($video['src']) ?>" type="<?= esc_attr($video['mime']) ?>">
</video>
