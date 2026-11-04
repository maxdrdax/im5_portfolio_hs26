<?php
// -> Modul «Projekte»: Karussell (Highlights) oder Liste mit Filter und Hover-Vorschau
$data = $args['data'];
$projects = $data['projects'] ?? [];
$layout = ($data['layout'] ?? 'carousel') === 'list' ? 'list' : 'carousel';

if (empty($projects)) {
    return;
}

// Gibt es mindestens ein ausgewähltes Projekt? Sonst ist «Alle» der Standardfilter.
$has_featured = in_array(true, array_column($projects, 'featured'), true);
?>

<section data-module="projects" data-layout-type="<?= esc_attr($layout) ?>"<?php if ($layout === 'list') : ?> data-filter-group data-directory<?php endif; ?>>
    <?php if (!empty($data['title'])) : ?>
        <h2 class="module-title"><?= format_accent($data['title']) ?></h2>
    <?php endif; ?>

    <?php if ($layout === 'carousel') : ?>
        <?php // Akkordeon: alle Projekte als schmale Streifen, aufgeklappt wird nur beim Hover/Fokus (Steuerung in main.js) ?>
        <ul class="carousel" data-accordion>
            <?php foreach ($projects as $index => $project) : ?>
                <?php // data-hover-video: Beim Hover über die Karte startet das Titelvideo ?>
                <li class="card" data-accordion-item data-hover-video>
                    <a class="card-link" href="<?= esc_url($project['url']) ?>">
                        <span class="card-media">
                            <?php if ($project['cover']) : ?>
                                <img src="<?= esc_url($project['cover']['src']) ?>" alt="<?= esc_attr($project['cover']['alt']) ?>"
                                     width="<?= esc_attr($project['cover']['width'] ?? '') ?>" height="<?= esc_attr($project['cover']['height'] ?? '') ?>"
                                     loading="<?= $index === 0 ? 'eager' : 'lazy' ?>">
                            <?php endif; ?>
                            <?php get_template_part('frontend/views/teaser/video', null, [
                                'video' => $project['cover_video'],
                                'mode' => 'hover',
                                'class' => 'card-video',
                            ]); ?>
                        </span>

                        <?php if (!empty($project['year'])) : ?>
                            <span class="card-year pill pill--light"><?= esc_html($project['year']) ?></span>
                        <?php endif; ?>

                        <span class="card-info">
                            <?php if (!empty($project['categories'])) : ?>
                                <span class="card-categories">
                                    <?php foreach ($project['categories'] as $category) : ?>
                                        <span class="pill pill--accent"><?= esc_html($category['name']) ?></span>
                                    <?php endforeach; ?>
                                </span>
                            <?php endif; ?>
                            <span class="card-title"><?= esc_html($project['title']) ?></span>
                        </span>

                        <span class="card-arrow" aria-hidden="true">↗</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

    <?php else : ?>
        <div class="filter" role="group" aria-label="Projekte filtern">
            <?php // Standardfilter (statisch im Theme), der aktive Button hat aria-pressed="true" ?>
            <?php if ($has_featured) : ?>
                <button type="button" data-filter="featured" aria-pressed="true">Ausgewählte Projekte</button>
            <?php endif; ?>
            <button type="button" data-filter="all" aria-pressed="<?= $has_featured ? 'false' : 'true' ?>">Alle</button>
            <?php foreach ($data['categories'] ?? [] as $category) : ?>
                <button type="button" data-filter="<?= esc_attr($category['slug']) ?>" aria-pressed="false">
                    <?= esc_html($category['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <table class="directory">
            <?php // Spaltenbreiten wie Figma (4 : 2 : 3 Rasterspalten); die Kopfzeile ist nur für Screenreader ?>
            <colgroup>
                <col class="col-title">
                <col class="col-category">
                <col class="col-year">
            </colgroup>
            <thead class="screen-reader-text">
                <tr>
                    <th scope="col">Projekt</th>
                    <th scope="col">Kategorie</th>
                    <th scope="col">Jahr</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project) : ?>
                    <tr data-categories="<?= esc_attr(implode(' ', array_column($project['categories'], 'slug'))) ?>"
                        data-featured="<?= $project['featured'] ? 'true' : 'false' ?>"
                        data-preview-target="<?= esc_attr($project['id']) ?>">
                        <?php // Der Link deckt per CSS die ganze Zeile ab (.row-link::after) ?>
                        <td><a class="row-link" href="<?= esc_url($project['url']) ?>"><?= esc_html($project['title']) ?></a></td>
                        <td><?= esc_html(implode(', ', array_column($project['categories'], 'name'))) ?></td>
                        <td><?= esc_html($project['year'] ?: '— / —') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php // Hover-Vorschau: ein Container, der dem Mauszeiger folgt; pro Projekt ein Eintrag (Bild + optional Video) ?>
        <div class="directory-preview" data-directory-preview aria-hidden="true">
            <?php foreach ($projects as $project) : ?>
                <?php if (!$project['cover'] && !$project['cover_video']) { continue; } ?>
                <div class="preview-item" data-preview-id="<?= esc_attr($project['id']) ?>">
                    <?php if ($project['cover']) : ?>
                        <img src="<?= esc_url($project['cover']['src']) ?>" alt="" loading="lazy">
                    <?php endif; ?>
                    <?php get_template_part('frontend/views/teaser/video', null, [
                        'video' => $project['cover_video'],
                        'mode' => 'hover',
                        'class' => 'preview-video',
                    ]); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
