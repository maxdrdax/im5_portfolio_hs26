<?php
// -> Modul «Erfahrung»: Stationen mit Tags und Download Lebenslauf
// Steht in der rechten Spalte (wie Figma), jede Station mit Kopf links (Titel, Institution, Zeitraum)
// und Inhalt rechts (Text, Tags). Links der Stationen läuft eine Zeitlinie, die sich beim Scrollen füllt.
$data = $args['data'];
$cv = $data['cv'] ?? false;
?>

<section data-module="experience">
    <div class="experience-content">
        <?php if (!empty($data['title']) || !empty($data['text'])) : ?>
            <div class="experience-head">
                <?php if (!empty($data['title'])) : ?>
                    <h2 class="module-title"><?= format_accent($data['title']) ?></h2>
                <?php endif; ?>
                <?php if (!empty($data['text'])) : ?>
                    <p class="text"><?= nl2br(esc_html($data['text'])) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($data['stations'])) : ?>
            <?php // Zeitlinie links: Linie (::before/::after) und Punkt pro Station (.station::before) im CSS,
                  // Füllung beim Scrollen über --progress und .is-reached (main.js, Abschnitt «Zeitlinie») ?>
            <ol class="stations" data-timeline>
                <?php foreach ($data['stations'] as $station) : ?>
                    <li class="station">
                        <div class="station-head">
                            <h3 class="station-title"><?= esc_html($station['title']) ?></h3>
                            <?php if (!empty($station['institution']) || !empty($station['period'])) : ?>
                                <p class="station-meta">
                                    <?php if (!empty($station['institution'])) : ?>
                                        <span class="institution"><?= esc_html($station['institution']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($station['period'])) : ?>
                                        <span class="period"><?= esc_html($station['period']) ?></span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="station-body">
                            <?php if (!empty($station['text'])) : ?>
                                <p class="text"><?= nl2br(esc_html($station['text'])) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($station['tags'])) : ?>
                                <ul class="tags">
                                    <?php foreach ($station['tags'] as $tag) : ?>
                                        <li class="tag"><?= esc_html($tag) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>

        <?php if ($cv) : ?>
            <a class="button" href="<?= esc_url($cv) ?>" download>Lebenslauf als PDF downloaden</a>
        <?php endif; ?>
    </div>
</section>
