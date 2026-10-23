<?php $data = $args['data']; ?>

<section data-module="home-intro">
    <?php if (!empty($data['eyebrow'])) : ?>
        <p class="home-eyebrow"><?= esc_html($data['eyebrow']) ?></p>
    <?php endif; ?>
    <h1><?= esc_html($data['title']) ?></h1>
    <?php if (!empty($data['description'])) : ?>
        <p class="home-description"><?= esc_html($data['description']) ?></p>
    <?php endif; ?>
</section>
