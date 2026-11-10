<?php
// -> Projektmodul «Video»: volles Video (YouTube/Vimeo) per oEmbed
// Der Embed-Code ist im Getter durch wp_kses() gefiltert (get_video_embed) und wird darum direkt ausgegeben.
$data = $args['data'];
?>
<section class="project-video">
    <h2 class="project-subtitle project-subtitle--ruled"><?= format_accent($data['title']) ?></h2>
    <div class="video-wrapper"><?= $data['embed'] ?></div>
</section>
