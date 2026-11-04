<?php
// -> Modul «Hero Bild»: Vollbild mit optionalem Titel und Scroll-Pfeil
$data = $args['data'];
$image = $data['image'] ?? false;
?>

<section data-module="hero-image">
    <?php if ($image) : ?>
        <?php // Hero ist sofort sichtbar: kein Lazy Loading, dafür hohe Priorität ?>
        <img class="image" src="<?= esc_url($image['src']) ?>" alt="<?= esc_attr($image['alt']) ?>"
             width="<?= esc_attr($image['width'] ?? '') ?>" height="<?= esc_attr($image['height'] ?? '') ?>"
             fetchpriority="high">
    <?php endif; ?>

    <?php // Optionales Video liegt über dem Bild; das Bild bleibt Vorschau und Fallback ?>
    <?php get_template_part('frontend/views/teaser/video', null, [
        'video' => $data['video'] ?? false,
        'mode' => 'inview',
        'poster' => $image['src'] ?? '',
        'class' => 'image video',
    ]); ?>

    <?php if (!empty($data['title'])) : ?>
        <h1 class="title"><?= format_accent($data['title']) ?></h1>
    <?php else : ?>
        <?php // Ohne Titel trotzdem eine H1 für Screenreader und SEO ?>
        <h1 class="screen-reader-text"><?= esc_html(get_the_title()) ?></h1>
    <?php endif; ?>

    <button class="scroll-arrow" type="button" data-scroll-next aria-label="Nach unten scrollen">↓</button>
</section>
