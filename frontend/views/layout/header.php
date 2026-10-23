<!DOCTYPE html>
<html lang="<?php bloginfo('language') ?>">
<head>
    <meta charset="<?php bloginfo('charset') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= get_template_directory_uri() ?>/frontend/styles/main.css">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover" />
    <title><?php bloginfo('name') ?></title>
    <?php wp_head(); ?>
</head>
<body>

<?php
$menu = get_menu_by_slug('main');
?>

<header data-layout="header">
    <span class="header-name">MAX HUTMACHER</span>
    <nav class="nav-container"
        data-menu="<?php echo $menu->slug; ?>">
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
</header>
