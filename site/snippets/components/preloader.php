<?php

// Homepage intro animation: add video files to src/assets/preloader/ (see preloader.json), then npm run development.
// Renders any sources that exist on disk (webm, mov, and/or mp4). For alpha: WebM (VP9) for Chrome/Firefox, HEVC (hvc1) .mov for Safari.
// Safari does NOT support ProRes 4444 alpha in <video> — re-encode with preloader.json _encodeSafari command.

$configPath = kirby()->root('index') . '/assets/preloader/preloader.json';
$config = is_file($configPath) ? json_decode(file_get_contents($configPath), true) : null;

if (!is_array($config) || empty($config['enabled'])) {
  return;
}

$assetExists = static function (?string $publicPath): bool {
  if (empty($publicPath)) {
    return false;
  }

  return is_file(kirby()->root('index') . '/' . ltrim($publicPath, '/'));
};

$sources = $config['sources'] ?? [];
$background = $config['background'] ?? 'transparent';
$width = (int) ($config['width'] ?? 320);
$height = isset($config['height']) ? (int) $config['height'] : null;
$poster = $config['poster'] ?? null;
$allowSkip = ($config['allowSkip'] ?? true) === true;
$style = '--preloader-width: ' . $width . 'px';

if ($height) {
  $style .= '; --preloader-height: ' . $height . 'px';
}

$sources = array_filter([
  'webm' => $assetExists($sources['webm'] ?? null) ? ($sources['webm'] ?? null) : null,
  'mov' => $assetExists($sources['mov'] ?? null) ? ($sources['mov'] ?? null) : null,
  'mp4' => $assetExists($sources['mp4'] ?? null) ? ($sources['mp4'] ?? null) : null,
]);

$hasVideo = $sources !== [];
$hasIntroMedia = $hasVideo || $assetExists($poster);

if (!$hasIntroMedia) {
  return;
}

?>
<div
  class="c-preloader c-preloader--<?= esc($background, 'attr') ?><?= $allowSkip ? ' c-preloader--skippable' : '' ?>"
  data-preloader
  data-preloader-config="<?= esc(json_encode($config), 'attr') ?>"
  aria-hidden="true"
  <?= $allowSkip ? 'tabindex="0"' : '' ?>
  style="<?= esc($style, 'attr') ?>"
>
  <div class="c-preloader__media">
    <?php if (!empty($poster)): ?>
      <img
        class="c-preloader__poster"
        data-preloader-poster
        src="<?= url($poster) ?>"
        alt=""
        width="<?= $width ?>"
        <?= $height ? 'height="' . $height . '"' : '' ?>
        hidden
      >
    <?php endif ?>

    <?php if ($hasVideo): ?>
      <video
        class="c-preloader__video"
        data-preloader-video
        muted
        playsinline
        preload="auto"
        <?= !empty($poster) ? 'poster="' . url($poster) . '"' : '' ?>
      >
        <?php if (!empty($sources['webm'])): ?>
          <source src="<?= url($sources['webm']) ?>" type="video/webm">
        <?php endif ?>
        <?php if (!empty($sources['mov'])): ?>
          <source src="<?= url($sources['mov']) ?>" type='video/quicktime; codecs="hvc1"'>
        <?php endif ?>
        <?php if (!empty($sources['mp4'])): ?>
          <source src="<?= url($sources['mp4']) ?>" type='video/mp4; codecs="hvc1"'>
        <?php endif ?>
      </video>
    <?php endif ?>
  </div>

  <?php if ($allowSkip): ?>
    <span class="u-visually-hidden" data-preloader-skip-hint><?= ui_t('preloader.skip') ?></span>
  <?php endif ?>
</div>
