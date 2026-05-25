<?php

if (empty($image)) {
  return;
}

$alt = $alt ?? $image->alt()->value();
$srcset = $srcset ?? 'default';
$sizes = $sizes ?? '100vw';
$class = $class ?? '';
$loading = $loading ?? 'lazy';
$decoding = $decoding ?? 'async';
$fetchpriority = $fetchpriority ?? null;
$blurUp = $blurUp ?? true;
$width = $width ?? $image->width();
$height = $height ?? $image->height();
$crop = $crop ?? false;
$objectPosition = $objectPosition ?? null;

$imgSrc = $image->url();
$imgSrcset = $image->srcset($srcset);

$imgClass = $class;
if ($blurUp) {
  $imgClass = trim('c-image__full ' . $imgClass);
}

$attrs = [
  'src' => $imgSrc,
  'srcset' => $imgSrcset,
  'sizes' => $sizes,
  'alt' => $alt,
  'width' => $width,
  'height' => $height,
  'class' => $imgClass,
  'loading' => $loading,
  'decoding' => $decoding,
];

if (!empty($fetchpriority)) {
  $attrs['fetchpriority'] = $fetchpriority;
}

if ($crop && $image->focus()->isNotEmpty()) {
  $objectPosition = $objectPosition ?? $image->focus()->value();
}

$style = [];
if ($crop) {
  $style[] = 'object-fit: cover';
}
if (!empty($objectPosition)) {
  $style[] = 'object-position: ' . $objectPosition;
}

$styleAttr = $style ? ' style="' . implode('; ', $style) . '"' : '';
$lqipUrl = $blurUp ? $image->thumb(['width' => 42, 'quality' => 20])->url() : null;

if ($blurUp && $lqipUrl):
?>
<div class="c-image c-image--blur-up">
  <img class="c-image__placeholder" src="<?= $lqipUrl ?>" alt="" aria-hidden="true" width="<?= $width ?>" height="<?= $height ?>"<?= $styleAttr ?> />
  <img <?= \Kirby\Toolkit\Html::attr($attrs) ?><?= $styleAttr ?>>
</div>
<?php else: ?>
<img <?= \Kirby\Toolkit\Html::attr($attrs) ?><?= $styleAttr ?>>
<?php endif;
