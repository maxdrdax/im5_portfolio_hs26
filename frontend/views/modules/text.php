<?php
// -> Modul «Text»: z. B. Impressum und Datenschutz
$data = $args['data'];
?>

<section data-module="text">
    <?php if (!empty($data['title'])) : ?>
        <h1 class="title"><?= format_accent($data['title']) ?></h1>
    <?php endif; ?>
    <div class="rich-text"><?= format_rich_text($data['text'] ?? '') ?></div>
</section>
