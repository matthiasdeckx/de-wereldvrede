<?php
/**
 * Modular content blocks (gallery, quote, …)
 *
 * @var \Kirby\Cms\Field $blocks
 */
$blocks = $blocks ?? null;

if (!$blocks || $blocks->isEmpty()) {
  return;
}

foreach ($blocks->toBlocks() as $block) {
  echo $block;
}
