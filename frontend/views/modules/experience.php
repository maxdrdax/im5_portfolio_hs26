<?php
// -> Modul «Erfahrung»: Stationen mit Tags und Download Lebenslauf
$data = $args['data'];
$cv = $data['cv'] ?? false;
?>

<section data-module="experience">
    <?php if (!empty($data['title'])) : ?>
        <h2 class="module-title"><?= format_accent($data['title']) ?></h2>
    <?php endif; ?>
    <?php if (!empty($data['text'])) : ?>
        <p class="text"><?= nl2br(esc_html($data['text'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($data['stations'])) : ?>
        <ol class="stations">
            <?php foreach ($data['stations'] as $station) : ?>
                <li class="station">
                    <?php if (!empty($station['period'])) : ?>
                        <p class="period"><?= esc_html($station['period']) ?></p>
                    <?php endif; ?>
                    <h3 class="station-title"><?= esc_html($station['title']) ?></h3>
                    <?php if (!empty($station['institution'])) : ?>
                        <p class="institution"><?= esc_html($station['institution']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($station['text'])) : ?>
                        <p class="text"><?= nl2br(esc_html($station['text'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($station['tags'])) : ?>
                        <ul class="tags">
                            <?php foreach ($station['tags'] as $tag) : ?>
                                <li class="tag"><?= esc_html($tag) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <?php if ($cv) : ?>
        <a class="button" href="<?= esc_url($cv) ?>" download>Lebenslauf als PDF downloaden <span aria-hidden="true">↗</span></a>
    <?php endif; ?>
</section>
