<?php
// -> Modul «Fotogalerie»: alle Fotos im Raster, Filter nach Fotokategorie
$data = $args['data'];
$photos = $data['photos'] ?? [];

if (empty($photos)) {
    return;
}
?>

<section data-module="gallery-photos" data-filter-group>
    <?php if (!empty($data['categories'])) : ?>
        <div class="filter" role="group" aria-label="Fotos filtern">
            <button type="button" data-filter="all" aria-pressed="true">Alle</button>
            <?php foreach ($data['categories'] as $category) : ?>
                <button type="button" data-filter="<?= esc_attr($category['slug']) ?>" aria-pressed="false">
                    <?= esc_html($category['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <ul class="photo-grid">
        <?php foreach ($photos as $photo) : ?>
            <li class="photo" data-format="<?= esc_attr($photo['format']) ?>"
                data-categories="<?= esc_attr(implode(' ', array_column($photo['categories'], 'slug'))) ?>">
                <img src="<?= esc_url($photo['image']['src']) ?>" alt="<?= esc_attr($photo['image']['alt']) ?>"
                     width="<?= esc_attr($photo['image']['width'] ?? '') ?>" height="<?= esc_attr($photo['image']['height'] ?? '') ?>"
                     loading="lazy" decoding="async">
            </li>
        <?php endforeach; ?>
    </ul>
</section>
