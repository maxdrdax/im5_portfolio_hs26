<?php
// -> Header: Schriftzug (Link auf Startseite) und Hauptnavigation
$logo = get_global_logo();
$menu = get_menu_by_slug('main');
?>

<header data-layout="header">
    <a class="logo-link" href="<?= esc_url(home_url('/')) ?>">
        <span class="header-name"><?= esc_html($logo) ?></span>
    </a>
    <?php if (!empty($menu)) : ?>
        <nav class="nav-container" aria-label="Hauptnavigation">
            <ul class="nav">
                <?php foreach ($menu as $item) : ?>
                    <li class="nav-item">
                        <a href="<?= esc_url($item['url']) ?>"<?php if ($item['target'] === '_blank') : ?> target="_blank" rel="noopener"<?php endif; ?>>
                            <?= esc_html($item['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>
</header>
