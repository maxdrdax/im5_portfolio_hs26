<?php
// -> Modul «Logos»: z. B. Berufserfahrung / Ausbildung
// Laufschrift (Marquee): main.js kopiert die Logos und startet die Animation.
// Ohne JavaScript oder mit «Bewegung reduzieren» bleiben die Logos zentriert stehen.
$data = $args['data'];
$logos = array_filter($data['logos'] ?? [], function ($item) {
    return !empty($item['logo']);
});
?>

<section data-module="logos">
    <?php if (!empty($data['title'])) : ?>
        <h2 class="module-title"><?= format_accent($data['title']) ?></h2>
    <?php endif; ?>

    <?php if (!empty($logos)) : ?>
        <div class="logo-marquee" data-marquee>
            <ul class="logo-track" data-marquee-track>
                <?php foreach ($logos as $item) : ?>
                    <li class="logo-item">
                        <?php if (!empty($item['link'])) : ?>
                            <a href="<?= esc_url($item['link']) ?>" target="_blank" rel="noopener">
                        <?php endif; ?>
                        <img src="<?= esc_url($item['logo']['src']) ?>" alt="<?= esc_attr($item['logo']['alt']) ?>" loading="lazy">
                        <?php if (!empty($item['link'])) : ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</section>
