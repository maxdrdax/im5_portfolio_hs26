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

    <?php // Link optional: nur mit Seite und Link-Text. Text und Pfeil in einer Zeile (inline-flex im CSS). ?>
    <?php if ($data['link'] && $link_text !== '') : ?>
        <a class="intro-link" href="<?= esc_url($data['link']) ?>">
            <span><?= esc_html($link_text) ?></span><?= get_icon('arrow', 'up-right') ?>
        </a>
    <?php endif; ?>
</section>
