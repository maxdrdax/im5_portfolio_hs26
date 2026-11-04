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
    <section class="project-hero">
        <?php if ($cover) : ?>
            <?php // Der Schriftzug ist Teil des Titelbilds, darum ist die H1 nur für Screenreader sichtbar ?>
            <img class="image" src="<?= esc_url($cover['src']) ?>" alt="<?= esc_attr($cover['alt']) ?>"
                 width="<?= esc_attr($cover['width'] ?? '') ?>" height="<?= esc_attr($cover['height'] ?? '') ?>"
                 fetchpriority="high">
            <h1 class="screen-reader-text"><?= esc_html($project['title']) ?></h1>
        <?php else : ?>
            <h1 class="title"><?= esc_html($project['title']) ?></h1>
        <?php endif; ?>

        <?php // Optionales Titelvideo über dem Titelbild ?>
        <?php get_template_part('frontend/views/teaser/video', null, [
            'video' => $project['cover_video'],
            'mode' => 'inview',
            'poster' => $cover['src'] ?? '',
            'class' => 'image video',
        ]); ?>

        <button class="scroll-arrow" type="button" data-scroll-next aria-label="Nach unten scrollen">↓</button>
    </section>

    <section class="project-facts">
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
    </section>

    <?php if ($project['description']) : ?>
        <section class="project-description">
            <h2>Projektbeschrieb</h2>
            <div class="rich-text"><?= format_rich_text($project['description']) ?></div>
        </section>
    <?php endif; ?>

    <?php if ($has_role) : ?>
        <section class="project-role">
            <h2>Meine Rolle und Teamabgrenzung</h2>
            <?php if ($project['role_text']) : ?>
                <div class="rich-text"><?= format_rich_text($project['role_text']) ?></div>
            <?php endif; ?>
            <?php if ($project['role_image']) : ?>
                <img src="<?= esc_url($project['role_image']['src']) ?>" alt="<?= esc_attr($project['role_image']['alt']) ?>"
                     width="<?= esc_attr($project['role_image']['width'] ?? '') ?>" height="<?= esc_attr($project['role_image']['height'] ?? '') ?>"
                     loading="lazy">
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if ($embed) : ?>
        <section class="project-video" aria-label="Video">
            <div class="video-wrapper"><?= $embed ?></div>
        </section>
    <?php endif; ?>

    <?php if (!empty($project['gallery'])) : ?>
        <section class="project-gallery" aria-label="Galerie">
            <ul class="gallery-strip">
                <?php foreach ($project['gallery'] as $media) : ?>
                    <li>
                        <?php if ($media['type'] === 'video') : ?>
                            <?php // Galerie-Video: ohne Ton, Schleife, startet sobald sichtbar ?>
                            <?php get_template_part('frontend/views/teaser/video', null, [
                                'video' => $media,
                                'mode' => 'inview',
                            ]); ?>
                        <?php elseif ($media['type'] === 'image') : ?>
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
