<?php
// -> Intro (Startseite nach dem Hero): kurze Vorstellung in grosser Schrift
// Beim Scrollen färbt sich der Text Wort für Wort von gedimmt zu weiss (main.js, Abschnitt «Intro»).
// Ohne JavaScript oder bei «Bewegung reduzieren» ist der Text einfach weiss.
$data = $args['data'];
$link_text = trim($data['link_text'] ?? '');
?>

<section data-module="intro">
    <?php if (!empty($data['text'])) : ?>
        <?php // format_accent(): erst escapen, dann *Wort* → <em>Wort</em>; nl2br: Zeilenumbrüche aus dem Textfeld ?>
        <p class="intro-text" data-scroll-text><?= nl2br(format_accent($data['text'])) ?></p>
    <?php endif; ?>

    <?php if ($data['link'] && $link_text !== '') : ?>
        <a class="intro-link" href="<?= esc_url($data['link']) ?>">
            <?= esc_html($link_text) ?> <?= get_icon('arrow', 'up-right') ?>
        </a>
    <?php endif; ?>
</section>
