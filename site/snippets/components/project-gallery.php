<?php
/**
 * Project-style image gallery grid
 *
 * @var \Kirby\Cms\Files|\Kirby\Content\Field $gallery Files field or collection of images
 */
$gallery = $gallery ?? null;

if (!$gallery || $gallery->isEmpty()) {
  return;
}

$files = $gallery instanceof Kirby\Cms\Files ? $gallery : $gallery->toFiles();

if ($files->isEmpty()) {
  return;
}

$galleryPattern = [
  ['span' => 5, 'start' => 6],
  ['span' => 5, 'start' => 8],
  ['span' => 9, 'start' => 1],
  ['span' => 5, 'start' => 2],
  ['span' => 7, 'start' => 5],
];
?>
<div class="c-project-detail__gallery">
  <?php $galleryIndex = 0; foreach ($files as $image): ?>
    <?php
      if ($galleryIndex === 0) {
        $galleryItemClass = 'c-project-detail__gallery-item c-project-detail__gallery-item--full';
        $galleryItemStyle = '';
      } else {
        $slot = ($galleryIndex - 1) % count($galleryPattern);
        $placement = $galleryPattern[$slot];
        $galleryItemClass = 'c-project-detail__gallery-item';
        $galleryItemStyle = '--gallery-col-start: ' . $placement['start'] . '; --gallery-col-span: ' . $placement['span'];
      }
    ?>
    <div
      class="<?= $galleryItemClass ?>"
      <?php if ($galleryItemStyle): ?> style="<?= esc($galleryItemStyle, 'attr') ?>"<?php endif ?>
    >
      <?php snippet('objects/image', [
        'image' => $image,
        'sizes' => $galleryIndex === 0 ? '(min-width: 1024px) 100vw, 100vw' : '(min-width: 1024px) 50vw, 100vw',
        'crop' => false,
      ]) ?>
    </div>
    <?php $galleryIndex++; ?>
  <?php endforeach ?>
</div>
