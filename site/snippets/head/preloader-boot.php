<?php

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

$hasIntroMedia = $assetExists($config['poster'] ?? null);

if (!$hasIntroMedia) {
  foreach ($config['sources'] ?? [] as $sourcePath) {
    if ($assetExists($sourcePath)) {
      $hasIntroMedia = true;
      break;
    }
  }
}

if (!$hasIntroMedia) {
  return;
}

$configJson = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
if ($configJson === false) {
  return;
}

?>
<script>
(function () {
  try {
    var cfg = <?= $configJson ?>;
    if (!cfg.enabled) return;
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches && cfg.reducedMotion === "skip") return;
    document.documentElement.classList.add("is-home-intro-active");
  } catch (e) {}
})();
</script>
