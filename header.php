<!DOCTYPE html>
<html lang="<?php bloginfo('language') ?>">
<head>
    <meta charset="<?php bloginfo('charset') ?>">
    <link rel="stylesheet" href="<?= get_template_directory_uri() ?>/frontend/styles/main.css?ver=<?= filemtime(get_template_directory() . '/frontend/styles/main.css') ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover" />
    <title><?php bloginfo('name') ?></title>
    <?php wp_head(); ?>
</head>
<body>
