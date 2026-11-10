<?php
// -> Projektmodul «Projektbeschrieb»: Zwischentitel links (1/3), Text rechts (2/3), Trennlinie darüber
$data = $args['data'];
?>
<section class="project-section">
    <div class="project-block project-description">
        <h2 class="project-subtitle"><?= format_accent($data['title']) ?></h2>
        <div class="project-block-body">
            <div class="rich-text"><?= format_rich_text($data['text']) ?></div>
        </div>
    </div>
</section>
