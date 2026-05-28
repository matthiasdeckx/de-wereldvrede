<?php

/** @var \Kirby\Cms\Block $block */
if ($block->text()->isEmpty()) {
  return;
}

snippet('components/featured-quote-block', [
  'quote' => $block->text(),
  'source' => $block->source(),
  'stars' => (int) $block->stars()->or('0')->value(),
]);
