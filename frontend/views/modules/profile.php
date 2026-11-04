<?php
// -> Modul «Profil»: links Name, Funktion, Skills, Steckbrief; rechts Titel und Text
// Zwei Spalten wie Figma (4 : 8 Rasterspalten); das Modul «Erfahrung» darunter nutzt dieselbe rechte Spalte.
$data = $args['data'];
?>

<section data-module="profile">
    <div class="profile-aside">
        <?php if (!empty($data['name']) || !empty($data['position'])) : ?>
            <div class="profile-head">
                <?php if (!empty($data['name'])) : ?>
                    <p class="name"><?= esc_html($data['name']) ?></p>
                <?php endif; ?>
                <?php if (!empty($data['position'])) : ?>
                    <p class="position"><?= esc_html($data['position']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($data['skills'])) : ?>
            <ul class="tags" aria-label="Skills">
                <?php foreach ($data['skills'] as $item) : ?>
                    <li class="tag"><?= esc_html($item['skill']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (!empty($data['facts'])) : ?>
            <dl class="facts">
                <?php foreach ($data['facts'] as $fact) : ?>
                    <div class="fact">
                        <dt><?= esc_html($fact['label']) ?></dt>
                        <dd><?= esc_html($fact['detail'] ?? '') ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>
    </div>

    <div class="profile-main">
        <?php if (!empty($data['title'])) : ?>
            <h2 class="module-title"><?= format_accent($data['title']) ?></h2>
        <?php endif; ?>
        <div class="rich-text"><?= format_rich_text($data['text'] ?? '') ?></div>
    </div>
</section>
