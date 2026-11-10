<?php
// -> Projektmodul «Galerie»: horizontales Akkordeon (Steuerung in main.js)
// Die Einträge sind im Getter gefiltert (nur Bilder und Videos), darum stimmt der Zähler.
$data = $args['data'];
$gallery = $data['items'];
?>
<section class="project-gallery">
    <?php // Titel trennt die Galerie sichtbar vom Bereich davor (sonst wirkt sie wie ein Teil davon) ?>
    <h2 class="project-subtitle project-subtitle--ruled"><?= format_accent($data['title']) ?></h2>
    <ul class="gallery-strip" data-strip>
        <?php foreach ($gallery as $media) : ?>
            <?php // tabindex: auch mit der Tastatur (Tab) aufklappbar ?>
            <li class="strip-item" data-strip-item tabindex="0">
                <?php if ($media['type'] === 'video') : ?>
                    <?php get_template_part('frontend/views/teaser/video', null, [
                        'video' => $media,
                        'mode' => 'hover',
                        'preload' => 'metadata',
                    ]); ?>
                <?php else : ?>
                    <img src="<?= esc_url($media['src']) ?>" alt="<?= esc_attr($media['alt']) ?>"
                         width="<?= esc_attr($media['width'] ?? '') ?>" height="<?= esc_attr($media['height'] ?? '') ?>"
                         loading="lazy" decoding="async">
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (count($gallery) > 1) : ?>
        <?php // Zähler wie bei den Highlights; ausgeblendet (is-idle), bis ein Bild offen ist (main.js) ?>
        <div class="carousel-progress is-idle" aria-hidden="true" data-accordion-progress>
            <span class="carousel-progress-count">
                <span data-progress-current>01</span> / <?= esc_html(sprintf('%02d', count($gallery))) ?>
            </span>
            <span class="carousel-progress-bar">
                <span class="carousel-progress-fill" data-progress-fill></span>
            </span>
        </div>

        <?php // Anleitung «Scrollen ↓» am Mauszeiger, bis zum ersten Mal geblättert wurde (main.js).
              // Ohne «Ansehen»: Ein Klick auf ein Bild öffnet nichts, darum blendet die Pille danach aus. ?>
        <span class="cursor-label pill pill--accent" aria-hidden="true" data-cursor-label="gallery">
            <span class="cursor-label-text cursor-label-text--scroll">Scrollen <?= get_icon('arrow', 'down') ?></span>
        </span>

        <?php // Tablet/Handy: Anleitung «← Wischen →» unter den Punkten, bis zum ersten Mal gewischt wurde
              // (nur dort sichtbar, CSS; ausblenden: main.js). Dekoration, darum aria-hidden. ?>
        <p class="strip-hint" aria-hidden="true" data-strip-hint>
            <?= get_icon('arrow', 'left') ?> Wischen <?= get_icon('arrow', 'right') ?>
        </p>
    <?php endif; ?>
</section>
