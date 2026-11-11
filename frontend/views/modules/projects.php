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

// Anzahl pro Filter aus den schon geladenen Projekten zählen (keine zusätzliche Datenbankabfrage)
$featured_count = count(array_filter(array_column($projects, 'featured')));
$category_counts = [];
foreach ($projects as $project) {
    foreach ($project['categories'] as $category) {
        $category_counts[$category['slug']] = ($category_counts[$category['slug']] ?? 0) + 1;
    }
}
?>

<section data-module="projects" data-layout-type="<?= esc_attr($layout) ?>"<?php if ($layout === 'list') : ?> data-filter-group data-directory<?php endif; ?>>
    <?php if (!empty($data['title'])) : ?>
        <h2 class="module-title"><?= format_accent($data['title']) ?></h2>
    <?php endif; ?>

    <?php if ($layout === 'carousel') : ?>
        <?php // Akkordeon: alle Projekte als schmale Streifen (Steuerung in main.js).
              // Mausrad/Trackpad über den Highlights klappt ein Projekt nach dem anderen auf; dazu Hover, Tastatur, Tippen. ?>
        <ul class="carousel" data-accordion>
            <?php foreach ($projects as $index => $project) : ?>
                <?php // Erstes Projekt schon im HTML offen (kein Aufklappen beim Laden); Titelvideo spielt, solange offen ?>
                <li class="card<?= $index === 0 ? ' is-active' : '' ?>" data-accordion-item>
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

                        <span class="card-arrow"><?= get_icon('arrow', 'up-right') ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php // Zähler «01 / 05» mit Fortschrittslinie: zeigt, dass es weitergeht und wo man ist (main.js aktualisiert).
              // Bleibt unten im Fenster kleben, solange die Highlights sichtbar sind (CSS sticky). ?>
        <div class="carousel-progress" aria-hidden="true" data-accordion-progress>
            <span class="carousel-progress-count">
                <span data-progress-current>01</span> / <?= esc_html(sprintf('%02d', count($projects))) ?>
            </span>
            <span class="carousel-progress-bar">
                <span class="carousel-progress-fill" data-progress-fill style="--progress: <?= esc_attr(1 / max(count($projects), 1)) ?>"></span>
            </span>
        </div>

        <?php // Label, das der Maus über den Highlights folgt (main.js). Beim ersten Besuch «Scrollen» als Anleitung,
              // nach dem ersten Blättern «Ansehen». Nur Dekoration: Der Link selbst ist für Screenreader und
              // Tastatur schon eindeutig, darum aria-hidden. ?>
        <span class="cursor-label pill pill--accent" aria-hidden="true" data-cursor-label="highlights">
            <span class="cursor-label-text cursor-label-text--scroll">Scrollen <?= get_icon('arrow', 'down') ?></span>
            <span class="cursor-label-text cursor-label-text--view">Ansehen</span>
        </span>

    <?php else : ?>
        <div class="filter" role="group" aria-label="Projekte filtern">
            <?php // Standardfilter (statisch im Theme), der aktive Button hat aria-pressed="true" ?>
            <?php if ($has_featured) : ?>
                <button type="button" data-filter="featured" aria-pressed="true">Ausgewählte Projekte<?= get_filter_count($featured_count, 'Projekt', 'Projekte') ?></button>
            <?php endif; ?>
            <button type="button" data-filter="all" aria-pressed="<?= $has_featured ? 'false' : 'true' ?>">Alle<?= get_filter_count(count($projects), 'Projekt', 'Projekte') ?></button>
            <?php foreach ($data['categories'] ?? [] as $category) : ?>
                <?php $count = $category_counts[$category['slug']] ?? 0; ?>
                <?php if ($count === 0) { continue; } // Kategorie ohne Projekt in dieser Liste: kein Filter ?>
                <button type="button" data-filter="<?= esc_attr($category['slug']) ?>" aria-pressed="false"><?= esc_html($category['name']) ?><?= get_filter_count($count, 'Projekt', 'Projekte') ?></button>
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
                        data-preview-target="<?= esc_attr($project['id']) ?>"
                        <?php // Standardfilter «Ausgewählte Projekte» schon im HTML anwenden (kein kurzes Aufblitzen aller Projekte) ?>
                        <?= $has_featured && !$project['featured'] ? 'hidden' : '' ?>>
                        <?php // Der Link deckt per CSS die ganze Zeile ab (.row-link::after) ?>
                        <?php $category_list = implode(', ', array_column($project['categories'], 'name')); ?>
                        <?php // Nur Handy: Vorschaubild 1:1 über dem Titel (auf dem Desktop übernimmt das die Hover-Vorschau) ?>
                        <td class="cell-thumb">
                            <?php if (!empty($project['cover_small']['src'])) : ?>
                                <img src="<?= esc_url($project['cover_small']['src']) ?>" alt="" loading="lazy" decoding="async">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="row-link" href="<?= esc_url($project['url']) ?>"><?= esc_html($project['title']) ?></a>
                            <?php // Nur Handy: Kategorie klein unter dem Titel (die Kategorie-Spalte ist dort ausgeblendet) ?>
                            <span class="row-category"><?= esc_html($category_list) ?></span>
                        </td>
                        <td class="cell-category"><?= esc_html($category_list) ?></td>
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
