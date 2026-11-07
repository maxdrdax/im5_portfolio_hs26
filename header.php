<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <?php // Titel, Styles, Scripts und Font-Preloads kommen über wp_head() (siehe backend/init/_theme.php) ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
