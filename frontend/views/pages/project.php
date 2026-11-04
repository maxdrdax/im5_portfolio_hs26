<?php
// -> Detailseite eines Projekts; alle Daten kommen aus dem Getter
$project = get_project_by_id(get_the_ID());
$cover = $project['cover'];
$has_role = !empty($project['role_text']) || !empty($project['role_image']);
$category_names = implode(', ', array_column($project['categories'], 'name'));
// Volles Video (YouTube/Vimeo): Embed-Code ist bereits durch wp_kses() gefiltert und kann direkt ausgegeben werden
$embed = get_video_embed($project['video_url']);
$arrow = get_template_directory_uri() . '/frontend/assets/icons/arrow-down.svg';
?>

<main data-page="project">
    <?php // 1. Hero: Titelbild bzw. Titelvideo, Pfeil unten rechts (Figma) ?>
    <section class="project-hero">
        <?php if ($cover) : ?>
            <img class="image" src="<?= esc_url($cover['src']) ?>" alt="<?= esc_attr($cover['alt']) ?>"
                 width="<?= esc_attr($cover['width'] ?? '') ?>" height="<?= esc_attr($cover['height'] ?? '') ?>"
                 fetchpriority="high">
        <?php endif; ?>

        <?php get_template_part('frontend/views/teaser/video', null, [
            'video' => $project['cover_video'],
            'mode' => 'inview',
            'poster' => $cover['src'] ?? '',
            'class' => 'image video',
        ]); ?>

        <button class="scroll-arrow" type="button" data-scroll-next aria-label="Nach unten scrollen">
            <img src="<?= esc_url($arrow) ?>" alt="" width="50" height="62">
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
                    <div class="fact"><dt>Kategorie</dt><dd class="uppercase"><?= esc_html($category_names) ?></dd></div>
                <?php endif; ?>
                <?php if ($project['role']) : ?>
                    <div class="fact"><dt>Meine Rolle</dt><dd><?= esc_html($project['role']) ?></dd></div>
                <?php endif; ?>
            </dl>
        </div>

        <?php if ($project['description']) : ?>
            <div class="project-description">
                <h2 class="project-subtitle">Projektbeschrieb</h2>
                <?php // Text fliesst automatisch auf zwei Spalten (CSS columns) ?>
                <div class="rich-text rich-text--columns"><?= format_rich_text($project['description']) ?></div>
            </div>
        <?php endif; ?>
    </section>

    <?php // 3. Rolle und Team: grüne Fläche, Text links, Bild rechts ?>
    <?php if ($has_role) : ?>
        <section class="project-role">
            <div class="role-text">
                <h2>Meine Rolle und Teamabgrenzung</h2>
                <?php if ($project['role_text']) : ?>
                    <div class="rich-text"><?= format_rich_text($project['role_text']) ?></div>
                <?php endif; ?>
            </div>
            <?php if ($project['role_image']) : ?>
                <div class="role-image">
                    <img src="<?= esc_url($project['role_image']['src']) ?>" alt="<?= esc_attr($project['role_image']['alt']) ?>"
                         width="<?= esc_attr($project['role_image']['width'] ?? '') ?>" height="<?= esc_attr($project['role_image']['height'] ?? '') ?>"
                         loading="lazy">
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php // 4. Volles Video (YouTube/Vimeo) ?>
    <?php if ($embed) : ?>
        <section class="project-video" aria-label="Video">
            <div class="video-wrapper"><?= $embed ?></div>
        </section>
    <?php endif; ?>

    <?php // 5. Galerie als horizontales Akkordeon (Steuerung in main.js) ?>
    <?php if (!empty($project['gallery'])) : ?>
        <section class="project-gallery" aria-label="Galerie">
            <ul class="gallery-strip" data-strip>
                <?php foreach ($project['gallery'] as $index => $media) : ?>
                    <?php if (!in_array($media['type'], ['image', 'video'], true)) { continue; } ?>
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
        </section>
    <?php endif; ?>
</main>
