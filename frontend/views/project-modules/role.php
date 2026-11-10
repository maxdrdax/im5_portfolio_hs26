<?php
// -> Projektmodul «Meine Rolle»: gleiches Raster wie der Projektbeschrieb, optionales Bild (3:2) unter dem Text
$data = $args['data'];
$image = $data['image'];
?>
<section class="project-section">
    <div class="project-block project-role">
        <h2 class="project-subtitle"><?= format_accent($data['title']) ?></h2>
        <div class="project-block-body">
            <?php if (has_rich_text($data['text'])) : ?>
                <div class="rich-text"><?= format_rich_text($data['text']) ?></div>
            <?php endif; ?>
            <?php if ($image) : ?>
                <img class="role-image" src="<?= esc_url($image['src']) ?>" alt="<?= esc_attr($image['alt']) ?>"
                     width="<?= esc_attr($image['width'] ?? '') ?>" height="<?= esc_attr($image['height'] ?? '') ?>"
                     loading="lazy" decoding="async">
            <?php endif; ?>
        </div>
    </div>
</section>
