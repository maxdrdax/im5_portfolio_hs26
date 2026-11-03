<?php
// -> Modul «Projekte»: Karussell (Highlights) oder Liste mit Filter und Hover-Vorschau
$data = $args['data'];
$projects = $data['projects'] ?? [];
$layout = ($data['layout'] ?? 'carousel') === 'list' ? 'list' : 'carousel';

if (empty($projects)) {
    return;
}
?>

<section data-module="projects" data-layout-type="<?= esc_attr($layout) ?>"<?php if ($layout === 'list') : ?> data-filter-group<?php endif; ?>>
    <?php if (!empty($data['title'])) : ?>
        <h2 class="module-title"><?= format_accent($data['title']) ?></h2>
    <?php endif; ?>

    <?php if ($layout === 'carousel') : ?>
        <ul class="carousel">
            <?php foreach ($projects as $project) : ?>
                <li class="card">
                    <a href="<?= esc_url($project['url']) ?>">
                        <?php if ($project['cover']) : ?>
                            <img src="<?= esc_url($project['cover']['src']) ?>" alt="<?= esc_attr($project['cover']['alt']) ?>"
                                 width="<?= esc_attr($project['cover']['width'] ?? '') ?>" height="<?= esc_attr($project['cover']['height'] ?? '') ?>"
                                 loading="lazy">
                        <?php endif; ?>
                        <span class="card-title"><?= esc_html($project['title']) ?> <span aria-hidden="true">↗</span></span>
                        <?php if (!empty($project['categories'])) : ?>
                            <span class="card-meta"><?= esc_html(implode(', ', array_column($project['categories'], 'name'))) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

    <?php else : ?>
        <?php if (!empty($data['categories'])) : ?>
            <div class="filter" role="group" aria-label="Projekte filtern">
                <button type="button" data-filter="all" aria-pressed="true">Alle</button>
                <?php foreach ($data['categories'] as $category) : ?>
                    <button type="button" data-filter="<?= esc_attr($category['slug']) ?>" aria-pressed="false">
                        <?= esc_html($category['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <table class="directory">
            <thead>
                <tr>
                    <th scope="col">Projekt</th>
                    <th scope="col">Kategorie</th>
                    <th scope="col">Jahr</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project) : ?>
                    <tr data-categories="<?= esc_attr(implode(' ', array_column($project['categories'], 'slug'))) ?>">
                        <td>
                            <a href="<?= esc_url($project['url']) ?>"><?= esc_html($project['title']) ?> <span aria-hidden="true">↗</span></a>
                            <?php if ($project['cover']) : ?>
                                <?php // Hover-Vorschau ?>
                                <img class="preview" src="<?= esc_url($project['cover']['src']) ?>" alt="" loading="lazy">
                            <?php endif; ?>
                        </td>
                        <td><?= esc_html(implode(', ', array_column($project['categories'], 'name'))) ?></td>
                        <td><?= esc_html($project['year']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
