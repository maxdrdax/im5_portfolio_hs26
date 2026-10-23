<?php $data = $args['data']; ?>

<section data-module="home-gallery" aria-label="Portfolio Galerie">
    <?php foreach ($data['items'] ?? [] as $index => $item) : ?>
        <?php if (empty($item['image'])) { continue; } ?>
        <article class="gallery-item">
            <?php if (!empty($item['link'])) : ?><a href="<?= esc_url($item['link']) ?>"><?php endif; ?>
                <figure>
                    <div class="gallery-image">
                        <img src="<?= esc_url($item['image']['src']) ?>" alt="<?= esc_attr($item['image']['alt']) ?>" loading="<?= $index < 2 ? 'eager' : 'lazy' ?>">
                    </div>
                    <figcaption>
                        <h2><?= esc_html($item['title']) ?></h2>
                        <?php if (!empty($item['meta'])) : ?><p><?= esc_html($item['meta']) ?></p><?php endif; ?>
                    </figcaption>
                </figure>
            <?php if (!empty($item['link'])) : ?></a><?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
