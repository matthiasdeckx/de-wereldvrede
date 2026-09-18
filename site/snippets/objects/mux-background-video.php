<?php

/**
 * Mux background hero video.
 *
 * @var string|null $id Mux playback ID
 * @var string $class
 * @var string|null $variant desktop|mobile|null
 * @var string $maxResolution
 */

$id = $id ?? null;
$class = $class ?? 'c-home-hero__video';
$variant = $variant ?? null;
$maxResolution = $maxResolution ?? '1080p';

if (empty($id)) {
  return;
}

$stream = 'https://stream.mux.com/' . $id . '.m3u8';
$thumb = 'https://image.mux.com/' . $id . '/thumbnail.webp?time=0';
$classes = $class;
if ($variant) {
  $classes .= ' c-home-hero__video--' . $variant;
}

?>
<mux-background-video
  class="<?= esc($classes, 'attr') ?>"
  data-hero-mux="<?= esc($variant ?: 'desktop', 'attr') ?>"
  data-mux-src="<?= esc($stream, 'attr') ?>"
  max-resolution="<?= esc($maxResolution, 'attr') ?>"
  preload="none"
  audio
>
  <img
    src="<?= esc($thumb, 'attr') ?>"
    alt=""
    decoding="async"
    aria-hidden="true"
  >
</mux-background-video>
