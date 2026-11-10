<?php
// -> Projektmodul «Weiterführende Informationen»: Linkliste (Stil wie die Projektliste)
// data-directory: gleiche Hover-Vorschau wie die Projektliste (main.js), Bereich = [data-preview-area]
$data = $args['data'];
// Eindeutige Vorschau-IDs, auch wenn ein Projekt mehrere Link-Module hat
$prefix = 'link-' . $data['index'] . '-';
?>
<section class="project-links" data-directory>
    <h2 class="project-subtitle project-subtitle--ruled"><?= format_accent($data['title']) ?></h2>
    <ul class="link-list" data-preview-area>
        <?php foreach ($data['links'] as $index => $link) : ?>
            <?php // Ohne Vorschaubild leerer Wert: Vorschau blendet über dieser Zeile aus ?>
            <li data-preview-target="<?= $link['preview'] ? esc_attr($prefix . $index) : '' ?>">
                <a class="link-row" href="<?= esc_url($link['url']) ?>"
                   <?php if ($link['external']) : ?>target="_blank" rel="noopener"<?php endif; ?>>
                    <span class="link-title"><?= esc_html($link['title']) ?></span>
                    <?php if ($link['source'] !== '') : ?>
                        <span class="link-source"><?= esc_html($link['source']) ?></span>
                    <?php endif; ?>
                    <?= get_icon('arrow', 'up-right', 'link-icon') ?>
                    <?php if ($link['external']) : ?>
                        <span class="screen-reader-text">(öffnet in neuem Tab)</span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php // Hover-Vorschau: folgt dem Mauszeiger; nur Links mit Vorschaubild ?>
    <div class="directory-preview" data-directory-preview aria-hidden="true">
        <?php foreach ($data['links'] as $index => $link) : ?>
            <?php if (!$link['preview']) { continue; } ?>
            <div class="preview-item<?= $link['preview_logo'] ? ' preview-item--logo' : '' ?>" data-preview-id="<?= esc_attr($prefix . $index) ?>">
                <img src="<?= esc_url($link['preview']['src']) ?>" alt="" loading="lazy">
            </div>
        <?php endforeach; ?>
    </div>
</section>
