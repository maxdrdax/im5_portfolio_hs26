<?php $data = $args['data']; ?>

<section data-module="home-logos" aria-label="Kunden und Partner">
    <p class="home-eyebrow">Ausgewählte Zusammenarbeit</p>
    <div class="logo-grid">
        <?php foreach ($data['items'] ?? [] as $item) : ?>
            <?php if (empty($item['logo'])) { continue; } ?>
            <?php if (!empty($item['link'])) : ?><a href="<?= esc_url($item['link']) ?>"><?php endif; ?>
                <span class="logo-item">
                    <img src="<?= esc_url($item['logo']['src']) ?>" alt="<?= esc_attr($item['logo']['alt'] ?: $item['title']) ?>" loading="lazy">
                    <span class="screen-reader-text"><?= esc_html($item['title']) ?></span>
                </span>
            <?php if (!empty($item['link'])) : ?></a><?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>
