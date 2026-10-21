<?php
$data = $args['data'];
?>

<section data-module="example">
    <img class="image" src="<?= $data['image']['src'] ?>" alt="<?= $data['image']['alt'] ?>">
    <h1 class="title"><?= $data['title'] ?></h1>
</section>
