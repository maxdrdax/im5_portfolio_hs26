<?php
// -> Modul «Fotogalerie»: alle Fotos im Raster, Filter nach Fotokategorie
$data = $args['data'];
$photos = $data['photos'] ?? [];

if (empty($photos)) {
    return;
}

// Angezeigte Breite pro Kachelgrösse (für sizes): Desktop 3 Spalten à 411 px, Handy 2 Spalten
$sizes_attr = [
    'small' => '(max-width: 768px) 50vw, 411px',
    'tall' => '(max-width: 768px) 50vw, 411px',
    'wide' => '(max-width: 768px) 100vw, 845px',
    'large' => '(max-width: 768px) 100vw, 845px',
    'panorama' => '(max-width: 1440px) 100vw, 1280px',
];
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
            <?php // data-size: Kachelgrösse im Raster, data-focus: sichtbarer Bildausschnitt (beides im CSS) ?>
            <li class="photo" data-size="<?= esc_attr($photo['size']) ?>" data-focus="<?= esc_attr($photo['focus']) ?>"
                data-categories="<?= esc_attr(implode(' ', array_column($photo['categories'], 'slug'))) ?>">
                <img src="<?= esc_url($photo['image']['src']) ?>" alt="<?= esc_attr($photo['image']['alt']) ?>"
                     <?php if (!empty($photo['image']['srcset'])) : ?>srcset="<?= esc_attr($photo['image']['srcset']) ?>" sizes="<?= esc_attr($sizes_attr[$photo['size']]) ?>"<?php endif; ?>
                     width="<?= esc_attr($photo['image']['width'] ?? '') ?>" height="<?= esc_attr($photo['image']['height'] ?? '') ?>"
                     loading="lazy" decoding="async">
            </li>
        <?php endforeach; ?>
    </ul>
</section>
