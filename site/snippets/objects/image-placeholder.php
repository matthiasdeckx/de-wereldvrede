<?php

$aspectRatio = $aspectRatio ?? '16 / 9';
$class = trim('c-image c-image--empty ' . ($class ?? ''));
?>
<div class="<?= esc($class) ?>" style="aspect-ratio: <?= esc($aspectRatio) ?>;" aria-hidden="true"></div>
