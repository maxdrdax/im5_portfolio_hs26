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
                    <?php
                    // Seitenverhältnis (Breite / Höhe) für gleiche optische Grösse (CSS: --logo-ratio).
                    // SVG ohne Grössenangaben: Annahme 2 : 1
                    $w = (int) ($item['logo']['width'] ?? 0);
                    $h = (int) ($item['logo']['height'] ?? 0);
                    $ratio = ($w > 0 && $h > 0) ? round($w / $h, 3) : 2;
                    ?>
                    <li class="logo-item" style="--logo-ratio: <?= esc_attr($ratio) ?>">
                        <?php if (!empty($item['link'])) : ?>
                            <a href="<?= esc_url($item['link']) ?>" target="_blank" rel="noopener">
                        <?php endif; ?>
                        <img src="<?= esc_url($item['logo']['src']) ?>" alt="<?= esc_attr($item['logo']['alt']) ?>" loading="lazy">
                        <?php if (!empty($item['link'])) : ?>
                                <span class="screen-reader-text">(öffnet in neuem Tab)</span>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</section>
