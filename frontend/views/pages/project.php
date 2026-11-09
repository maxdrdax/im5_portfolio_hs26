<?php
// -> Detailseite eines Projekts; alle Daten kommen aus dem Getter
$project = get_project_by_id(get_the_ID());
$cover = $project['cover'];
$has_role = !empty($project['role_text']) || !empty($project['role_image']);
$category_names = implode(', ', array_column($project['categories'], 'name'));
// Volles Video (YouTube/Vimeo): Embed-Code ist bereits durch wp_kses() gefiltert und kann direkt ausgegeben werden
$embed = get_video_embed($project['video_url']);
?>

<main data-page="project">
    <?php // 1. Hero: Titelbild bzw. Titelvideo, Pfeil unten rechts (Figma) ?>
    <section class="project-hero">
        <?php if ($cover) : ?>
            <img class="image" src="<?= esc_url($cover['src']) ?>" alt="<?= esc_attr($cover['alt']) ?>"
                 width="<?= esc_attr($cover['width'] ?? '') ?>" height="<?= esc_attr($cover['height'] ?? '') ?>"
                 fetchpriority="high"<?= !empty($cover['animated']) ? ' data-animated' : '' ?>>
        <?php endif; ?>

        <?php get_template_part('frontend/views/teaser/video', null, [
            'video' => $project['cover_video'],
            'mode' => 'inview',
            'poster' => $cover['src'] ?? '',
            'class' => 'image video',
        ]); ?>

        <button class="scroll-arrow" type="button" data-scroll-next aria-label="Nach unten scrollen">
            <?= get_icon('arrow-long', 'down') // Icon aus dem Theme, sicher (siehe get_icon) ?>
        </button>
    </section>

    <?php // 2. Projektinfos: Kategorie und Titel links, Eckdaten rechts; darunter der Projektbeschrieb ?>
    <section class="project-intro">
        <div class="project-header">
            <?php // Ausgeblendet (Feld «Titel unter dem Hero ausblenden»): nur noch für Screenreader ?>
            <div class="project-heading<?= $project['hide_title'] ? ' screen-reader-text' : '' ?>">
                <?php if ($category_names) : ?>
                    <p class="project-category"><?= esc_html($category_names) ?></p>
                <?php endif; ?>
                <h1 class="project-title"><?= esc_html($project['title']) ?></h1>
            </div>

            <dl class="facts">
                <?php if ($project['year']) : ?>
                    <div class="fact"><dt>Jahr</dt><dd><?= esc_html($project['year']) ?></dd></div>
                <?php endif; ?>
                <?php if ($project['context']) : ?>
                    <div class="fact"><dt>Kontext</dt><dd><?= esc_html($project['context']) ?></dd></div>
                <?php endif; ?>
                <?php if ($category_names) : ?>
                    <div class="fact"><dt>Kategorie</dt><dd><?= esc_html($category_names) ?></dd></div>
                <?php endif; ?>
                <?php if ($project['role']) : ?>
                    <div class="fact"><dt>Meine Rolle</dt><dd><?= esc_html($project['role']) ?></dd></div>
                <?php endif; ?>
            </dl>
        </div>

        <?php // Projektbeschrieb und Meine Rolle im gleichen Raster (.project-block):
              // Zwischentitel links, Text rechts in einer Spalte, Trennlinie darüber ?>
        <?php if ($project['description']) : ?>
            <div class="project-block project-description">
                <h2 class="project-subtitle"><?= format_accent($project['description_title']) ?></h2>
                <div class="project-block-body">
                    <div class="rich-text"><?= format_rich_text($project['description']) ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php // 3. Meine Rolle (optional): Text und/oder Bild; beides leer = nicht ausgeben ?>
        <?php if ($has_role) : ?>
            <div class="project-block project-role">
                <h2 class="project-subtitle"><?= format_accent($project['role_title']) ?></h2>
                <div class="project-block-body">
                    <?php if ($project['role_text']) : ?>
                        <div class="rich-text"><?= format_rich_text($project['role_text']) ?></div>
                    <?php endif; ?>
                    <?php if ($project['role_image']) : ?>
                        <img class="role-image" src="<?= esc_url($project['role_image']['src']) ?>" alt="<?= esc_attr($project['role_image']['alt']) ?>"
                             width="<?= esc_attr($project['role_image']['width'] ?? '') ?>" height="<?= esc_attr($project['role_image']['height'] ?? '') ?>"
                             loading="lazy" decoding="async">
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <?php // 4. Volles Video (YouTube/Vimeo) ?>
    <?php if ($embed) : ?>
        <section class="project-video">
            <h2 class="project-subtitle project-subtitle--ruled"><?= format_accent($project['video_title']) ?></h2>
            <div class="video-wrapper"><?= $embed ?></div>
        </section>
    <?php endif; ?>

    <?php // 5. Galerie als horizontales Akkordeon (Steuerung in main.js) ?>
    <?php
    // Nur Bilder und Videos (andere Dateitypen überspringen); vorher filtern, damit der Zähler stimmt
    $gallery = array_values(array_filter($project['gallery'] ?? [], function ($media) {
        return in_array($media['type'] ?? '', ['image', 'video'], true);
    }));
    ?>
    <?php if (!empty($gallery)) : ?>
        <section class="project-gallery">
            <?php // Titel trennt die Galerie sichtbar von «Meine Rolle» (sonst wirkt sie wie ein Teil davon) ?>
            <h2 class="project-subtitle project-subtitle--ruled"><?= format_accent($project['gallery_title']) ?></h2>
            <ul class="gallery-strip" data-strip>
                <?php foreach ($gallery as $index => $media) : ?>
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
    <?php endif; ?>

    <?php // 6. Weiterführende Informationen: Linkliste (Stil wie die Projektliste), leer = nicht ausgeben ?>
    <?php if (!empty($project['links'])) : ?>
        <?php // data-directory: gleiche Hover-Vorschau wie die Projektliste (main.js), Bereich = [data-preview-area] ?>
        <section class="project-links" data-directory>
            <h2 class="project-subtitle project-subtitle--ruled"><?= format_accent($project['links_title']) ?></h2>
            <ul class="link-list" data-preview-area>
                <?php foreach ($project['links'] as $index => $link) : ?>
                    <?php // Ohne Vorschaubild leerer Wert: Vorschau blendet über dieser Zeile aus ?>
                    <li data-preview-target="<?= $link['preview'] ? esc_attr('link-' . $index) : '' ?>">
                        <a class="link-row" href="<?= esc_url($link['url']) ?>"
                           <?php if ($link['external']) : ?>target="_blank" rel="noopener"<?php endif; ?>>
                            <span class="link-title"><?= esc_html($link['title']) ?></span>
                            <?php if ($link['source'] !== '') : ?>
                                <span class="link-source"><?= esc_html($link['source']) ?></span>
                            <?php endif; ?>
                            <?= get_icon('arrow', 'up-right', 'link-icon') ?>
                            <?php if ($link['external']) : ?>
                                <span class="screen-reader-text">(öffnet in neuem Tab)</span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php // Hover-Vorschau: folgt dem Mauszeiger; nur Links mit Vorschaubild ?>
            <div class="directory-preview" data-directory-preview aria-hidden="true">
                <?php foreach ($project['links'] as $index => $link) : ?>
                    <?php if (!$link['preview']) { continue; } ?>
                    <div class="preview-item<?= $link['preview_logo'] ? ' preview-item--logo' : '' ?>" data-preview-id="link-<?= esc_attr($index) ?>">
                        <img src="<?= esc_url($link['preview']['src']) ?>" alt="" loading="lazy">
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>
