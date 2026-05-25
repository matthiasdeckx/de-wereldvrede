<?php

if (empty($video)) {
  return;
}

$autoplay = $autoplay ?? true;
$loop = $loop ?? $autoplay;
$muted = $muted ?? true;
$controls = $controls ?? false;
$preload = $preload ?? 'metadata';
$class = $class ?? '';
$playsinline = $playsinline ?? true;
$poster = $poster ?? null;

$attrs = [
  'class' => $class,
  'preload' => $preload,
];

if ($autoplay) {
  $attrs['autoplay'] = true;
}
if ($loop) {
  $attrs['loop'] = true;
}
if ($muted) {
  $attrs['muted'] = true;
}
if ($controls) {
  $attrs['controls'] = true;
}
if ($playsinline) {
  $attrs['playsinline'] = true;
}
if (!empty($poster)) {
  $attrs['poster'] = $poster;
}

?>
<video <?= \Kirby\Toolkit\Html::attr($attrs) ?>>
  <source src="<?= $video->url() ?>" type="<?= $video->mime() ?>">
</video>
