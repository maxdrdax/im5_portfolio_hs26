<?php
// -> Modul «Hero Text»: Titel mit Akzent und optionalem Text
$data = $args['data'];
?>

<section data-module="hero-text">
    <h1 class="title"><?= format_accent($data['title'] ?? '') ?></h1>
    <?php if (!empty($data['text'])) : ?>
        <p class="text"><?= nl2br(esc_html($data['text'])) ?></p>
    <?php endif; ?>
</section>
