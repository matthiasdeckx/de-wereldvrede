<?php

$class = trim('c-icon-external ' . ($class ?? ''));
$width = (int) ($width ?? 8);
$height = (int) ($height ?? 8);

?>
<svg class="<?= esc($class) ?>" width="<?= $width ?>" height="<?= $height ?>" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <path d="M4.625 0.25H7.25V2.875M7.25 0.25L2.875 4.625M2.875 0.25H0.25V7.25H7.25V4.625" stroke="currentColor" stroke-width="0.5"/>
</svg>
