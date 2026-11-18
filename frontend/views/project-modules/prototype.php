<?php
// -> Projektmodul «Figma-Prototyp»: ein oder zwei Prototypen als Vorschau mit Link zu Figma
// Keine Einbettung (iframe): Die ganze Fläche ist ein Link und öffnet den Prototyp in einem neuen Tab.
// So lädt die Seite nichts von Figma (schnell, Datenschutz) und der Prototyp läuft in Figma bildschirmfüllend,
// auch auf dem Handy. Die Links sind im Getter geprüft (Whitelist, get_figma_url) und werden trotzdem escaped.
// Zwei Prototypen stehen ab Tablet nebeneinander und gleich hoch (CSS), auf dem Handy untereinander.
$data = $args['data'];
$items = $data['items'];
$has_pair = count($items) > 1;
?>
<section class="project-prototype">
    <h2 class="project-subtitle project-subtitle--ruled"><?= format_accent($data['title']) ?></h2>

    <ul class="prototype-list<?= $has_pair ? ' prototype-list--pair' : '' ?>">
        <?php foreach ($items as $item) : ?>
            <?php
            $poster = $item['poster'];
            // Screenreader: Beschriftung im Linktext; ohne Beschriftung bei zwei Prototypen das Format nennen,
            // damit man die Links unterscheiden kann
            $sr_label = $item['label'] ?: ($has_pair ? ($item['format'] === 'portrait' ? 'Handy' : 'Web') : '');
            ?>
            <li class="prototype-item prototype-item--<?= esc_attr($item['format']) ?>">
                <?php if ($item['label'] !== '') : ?>
                    <?php // Sichtbare Beschriftung; für Screenreader ausgeblendet, sie steht schon im Linktext ?>
                    <p class="prototype-label" aria-hidden="true"><?= esc_html($item['label']) ?></p>
                <?php endif; ?>
                <?php // Ein Link für Bild und Button. Handy: Button unter dem Bild; ab Tablet: Button unten im Bild (CSS) ?>
                <a class="prototype-card" href="<?= esc_url($item['url']) ?>" target="_blank" rel="noopener">
                    <span class="prototype-media">
                        <?php if ($poster) : ?>
                            <?php // Dekorativ (alt leer): Der Linktext beschreibt das Ziel ?>
                            <img class="prototype-poster" src="<?= esc_url($poster['src']) ?>" alt=""
                                 width="<?= esc_attr($poster['width'] ?? '') ?>" height="<?= esc_attr($poster['height'] ?? '') ?>"
                                 loading="lazy" decoding="async">
                        <?php endif; ?>
                    </span>

                    <?php // Sieht aus wie der Lebenslauf-Button (.button), ist aber Teil des Links (span, kein zweiter Link) ?>
                    <span class="button prototype-button">
                        Figma-Prototyp öffnen<?= get_icon('arrow', 'up-right') // Icon aus dem Theme, sicher (siehe get_icon) ?>
                    </span>
                    <span class="screen-reader-text">(<?= $sr_label !== '' ? esc_html($sr_label) . ', ' : '' ?>öffnet in neuem Tab)</span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
