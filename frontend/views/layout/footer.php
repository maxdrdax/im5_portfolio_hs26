<?php
$menu = get_menu_by_slug('legals');
$logo = get_global_logo();
$logo_link = get_global_logo_link();
$copyright = get_global_footer_copyright();
$socials = get_global_socials();
?>

<footer data-layout="footer">
    <?php if ($logo) : ?>
        <a class="logo-link" href="<?= esc_url($logo_link) ?>">
            <img class="logo" src="<?= esc_url($logo['src']) ?>" alt="<?= esc_attr($logo['alt']) ?>">
        </a>
    <?php endif; ?>
    <nav class="nav-container">
        <ul class="nav">
            <?php foreach ($menu as $item) : ?>
                <li class="nav-item">
                    <a target="<?= $item['target'] ?>" href="<?= $item['url'] ?>">
                        <?= $item['title'] ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <p class="copyright"><?= esc_html($copyright) ?></p>
    <div class="socials">
        <?php foreach ($socials as $social) : ?>
            <a target="_blank" href="<?= $social['link'] ?>" aria-label="Link zu <?= $social['title'] ?>">
                <img src="<?= $social['icon']['src'] ?>" alt="<?= $social['icon']['alt'] ?>">
            </a>
        <?php endforeach; ?>
    </div>
</footer>
