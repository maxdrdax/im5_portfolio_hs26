<?php
$menu = get_menu_by_slug('legals');
$logo = get_global_logo();
$socials = get_global_socials();
?>

<footer data-layout="footer">
    <img class="logo" src="<?= $logo['src'] ?>" alt="<?= $logo['alt'] ?>">
    <div class="socials">
        <?php foreach ($socials as $social) : ?>
            <a target="_blank" href="<?= $social['link'] ?>" aria-label="Link zu <?= $social['title'] ?>">
                <img src="<?= $social['icon']['src'] ?>" alt="<?= $social['icon']['alt'] ?>">
            </a>
        <?php endforeach; ?>
    </div>
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
</footer>
