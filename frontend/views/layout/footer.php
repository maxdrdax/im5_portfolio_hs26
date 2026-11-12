<?php
// -> Footer: Rechtliches, Copyright und Socials mit Icon
$menu = get_menu_by_slug('legals');
$copyright = get_global_copyright();
$socials = get_global_socials(true);
?>

<footer data-layout="footer">
    <?php if (!empty($menu)) : ?>
        <nav class="nav-container" aria-label="Rechtliches">
            <ul class="nav">
                <?php foreach ($menu as $item) : ?>
                    <li class="nav-item">
                        <?php // aria-current: aktuelle Seite (Active State, auch für Screenreader) ?>
                        <a href="<?= esc_url($item['url']) ?>"<?php if ($item['current']) : ?> aria-current="page"<?php endif; ?><?php if ($item['target'] === '_blank') : ?> target="_blank" rel="noopener"<?php endif; ?>>
                            <?= esc_html($item['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <?php if ($copyright) : ?>
        <p class="copyright"><?= esc_html($copyright) ?></p>
    <?php endif; ?>

    <?php if (!empty($socials)) : ?>
        <div class="socials">
            <?php foreach ($socials as $social) : ?>
                <a href="<?= esc_url($social['url']) ?>" target="_blank" rel="noopener" aria-label="<?= esc_attr($social['title'] . ' (öffnet in neuem Tab)') ?>">
                    <img src="<?= esc_url($social['icon']['src']) ?>" alt="" width="48" height="48" loading="lazy">
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</footer>
