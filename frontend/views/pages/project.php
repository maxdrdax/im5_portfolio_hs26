<?php
// -> Detailseite eines Projekts; alle Daten kommen aus dem Getter
// Fest: Hero und Kopf (Titel, Eckdaten). Danach die Module in der Reihenfolge aus dem Backend.
$project = get_project_by_id(get_the_ID());
$modules = get_project_modules(get_the_ID());
$cover = $project['cover'];
$category_names = implode(', ', array_column($project['categories'], 'name'));
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

    <?php // 2. Projektinfos: Kategorie und Titel links, Eckdaten rechts ?>
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
    </section>

    <?php // 3. Module (Projektbeschrieb, Meine Rolle, Video, Galerie, Links) in beliebiger Reihenfolge.
          // Der Typ kommt aus der Whitelist im Getter (get_project_modules), darum ist der Pfad sicher. ?>
    <?php foreach ($modules as $module) : ?>
        <?php get_template_part('frontend/views/project-modules/' . $module['type'], null, ['data' => $module]); ?>
    <?php endforeach; ?>
</main>
