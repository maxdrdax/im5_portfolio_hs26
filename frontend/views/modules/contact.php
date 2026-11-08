<?php
// -> Modul «Kontakt»: E-Mail und alle Socials aus den Globals
$data = $args['data'];
$email = $data['email'] ?? false;
$socials = $data['socials'] ?? [];
?>

<section data-module="contact">
    <ul class="contact-list">
        <?php if ($email) : ?>
            <li>
                <a href="<?= esc_url('mailto:' . $email) ?>"><?= esc_html($email) ?> <?= get_icon('arrow', 'up-right') ?></a>
            </li>
        <?php endif; ?>
        <?php foreach ($socials as $social) : ?>
            <li>
                <a href="<?= esc_url($social['url']) ?>" target="_blank" rel="noopener">
                    <?= esc_html($social['title']) ?> <?= get_icon('arrow', 'up-right') ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
