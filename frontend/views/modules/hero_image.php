<?php
// -> Modul «Hero Bild»: Vollbild mit optionalem Titel und Scroll-Pfeil
$data = $args['data'];
$image = $data['image'] ?? false;
// Mit Video: Text invertiert seine Farbe dynamisch (siehe CSS .has-video)
$has_video = !empty($data['video']);
?>

<?php // data-title-position: Position des Titels im Bild (CSS), Wert ist im Getter geprüft ?>
<section data-module="hero-image" data-title-position="<?= esc_attr($data['title_position']) ?>"<?php if ($has_video) : ?> class="has-video"<?php endif; ?>>
    <?php if ($image) : ?>
        <?php // Hero ist sofort sichtbar: kein Lazy Loading, dafür hohe Priorität ?>
        <img class="image" src="<?= esc_url($image['src']) ?>" alt="<?= esc_attr($image['alt']) ?>"
             width="<?= esc_attr($image['width'] ?? '') ?>" height="<?= esc_attr($image['height'] ?? '') ?>"
             fetchpriority="high"<?= !empty($image['animated']) ? ' data-animated' : '' ?>>
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

    <button class="scroll-arrow" type="button" data-scroll-next aria-label="Nach unten scrollen">
        <?= get_icon('arrow-long', 'down') // Icon aus dem Theme, sicher (siehe get_icon) ?>
    </button>
</section>
