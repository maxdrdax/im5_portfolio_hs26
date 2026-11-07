<?php
// -> Header: Schriftzug (Link auf Startseite), «Kontakt» und Menü-Button
// -> Overlay-Menü: Hauptnavigation «main» mit Hintergrund pro Menüpunkt, E-Mail und Socials
$logo = get_global_logo();
$menu = get_menu_by_slug('main');
$email = get_global_email();
$socials = get_global_socials();

// «Kontakt» im Header = Eintrag aus «main», der auf die Seite «kontakt» verweist (CMS-Konzept D)
$contact = null;
foreach ($menu as $item) {
    if ($item['slug'] === 'kontakt') {
        $contact = $item;
        break;
    }
}

// Startbild im Overlay (wichtig fürs Handy ohne Hover): aktuelle Seite, sonst der erste Eintrag
$active_id = $menu[0]['ID'] ?? 0;
foreach ($menu as $item) {
    if ($item['current']) {
        $active_id = $item['ID'];
        break;
    }
}

// Hintergründe fürs Overlay: gemeinsamer Hintergrund aus den Globals (hat Vorrang),
// sonst einer pro Menüpunkt (CMS-Konzept D)
$menu_bg = get_global_menu_background();
$backgrounds = [];
if ($menu_bg) {
    $backgrounds[] = ['id' => 'all', 'image' => $menu_bg['image'], 'video' => $menu_bg['video'], 'active' => true];
} else {
    foreach ($menu as $item) {
        $backgrounds[] = [
            'id' => $item['ID'],
            'image' => $item['image'] ?? false,
            'video' => $item['video'] ?? false,
            'active' => $item['ID'] === $active_id,
        ];
    }
}
?>

<header data-layout="header">
    <a class="logo-link" href="<?= esc_url(home_url('/')) ?>">
        <span class="header-name"><?= esc_html($logo) ?></span>
    </a>

    <?php if (!empty($menu)) : ?>
        <div class="header-actions">
            <?php if ($contact) : ?>
                <a class="header-link" href="<?= esc_url($contact['url']) ?>"<?php if ($contact['current']) : ?> aria-current="page"<?php endif; ?>>
                    <?= esc_html($contact['title']) ?>
                </a>
            <?php endif; ?>

            <?php // Echter Button: per Tastatur bedienbar, aria-expanded sagt Screenreadern, ob das Menü offen ist ?>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="menu-overlay" data-menu-toggle>
                <?php // Beide Wörter liegen übereinander und blenden über (CSS); sichtbar ist je nach aria-expanded eines ?>
                <span class="menu-toggle-label">
                    <span class="menu-toggle-text menu-toggle-text--closed">Menü</span>
                    <span class="menu-toggle-text menu-toggle-text--open">Schliessen</span>
                </span>
                <span class="menu-toggle-bars" aria-hidden="true"></span>
            </button>
        </div>
    <?php endif; ?>
</header>

<?php if (!empty($menu)) : ?>
    <?php // inert: Geschlossen ist das Overlay für Tab-Taste und Screenreader nicht erreichbar (main.js entfernt es beim Öffnen) ?>
    <?php // data-menu-fixed: gemeinsamer Hintergrund, der beim Hover nicht wechselt (main.js) ?>
    <div class="menu-overlay" id="menu-overlay" data-menu<?php if ($menu_bg) : ?> data-menu-fixed<?php endif; ?> inert>
        <?php // Hintergründe: gemeinsam oder einer pro Menüpunkt (Crossfade per CSS). Bilder laden erst beim ersten Öffnen (data-src). ?>
        <div class="menu-backgrounds" aria-hidden="true">
            <?php foreach ($backgrounds as $bg) : ?>
                <div class="menu-bg<?php if ($bg['active']) : ?> is-active<?php endif; ?>" data-menu-bg="<?= esc_attr($bg['id']) ?>">
                    <?php if (!empty($bg['image']['src'])) : ?>
                        <img data-src="<?= esc_url($bg['image']['src']) ?>" alt=""
                             <?php if (!empty($bg['image']['width'])) : ?>width="<?= esc_attr($bg['image']['width']) ?>" height="<?= esc_attr($bg['image']['height']) ?>"<?php endif; ?>
                             decoding="async">
                    <?php endif; ?>
                    <?php if (!empty($bg['video'])) : ?>
                        <?php get_template_part('frontend/views/teaser/video', null, [
                            'video' => $bg['video'],
                            'mode' => 'manual',
                        ]); ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <nav class="menu-nav" aria-label="Hauptnavigation">
            <?php // <ol>: Die Reihenfolge ist Teil der Gestaltung (01–04); die Nummern selbst liest der Screenreader nicht vor ?>
            <ol class="menu-list">
                <?php foreach ($menu as $index => $item) : ?>
                    <li class="menu-item">
                        <a class="menu-link" href="<?= esc_url($item['url']) ?>" data-menu-target="<?= esc_attr($item['ID']) ?>"<?php if ($item['current']) : ?> aria-current="page"<?php endif; ?><?php if ($item['target'] === '_blank') : ?> target="_blank" rel="noopener"<?php endif; ?>>
                            <span class="menu-nr" aria-hidden="true"><?= esc_html(sprintf('%02d', $index + 1)) ?></span>
                            <span class="menu-word">
                                <?php // Sichtbares Wort: wird beim Hover «gescrambelt» (main.js), darum für Screenreader ausgeblendet ?>
                                <span class="menu-word-text" aria-hidden="true" data-scramble><?= esc_html($item['title']) ?></span>
                                <?php // Für Screenreader: immer das richtige Wort ?>
                                <span class="screen-reader-text"><?= esc_html($item['title']) ?></span>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>

        <?php if ($email || !empty($socials)) : ?>
            <div class="menu-foot">
                <?php if ($email) : ?>
                    <a href="<?= esc_url('mailto:' . $email) ?>"><?= esc_html($email) ?></a>
                <?php endif; ?>
                <?php if (!empty($socials)) : ?>
                    <ul class="menu-socials">
                        <?php foreach ($socials as $social) : ?>
                            <li>
                                <a href="<?= esc_url($social['url']) ?>" target="_blank" rel="noopener">
                                    <?= esc_html($social['title']) ?> <span aria-hidden="true">↗</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
