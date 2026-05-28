<?php
/**
 * Featured quote section (project page + modular blocks)
 *
 * @var \Kirby\Cms\Field $quote
 * @var \Kirby\Cms\Field $source
 * @var int $stars
 */
$quote = $quote ?? null;
$source = $source ?? null;
$stars = (int) ($stars ?? 0);

if (!$quote || $quote->isEmpty()) {
  return;
}
?>
<section class="c-project-featured-quote c-project-featured-quote--single" aria-label="Featured quote">
  <?php snippet('components/featured-quote', [
    'quote' => $quote,
    'source' => $source,
    'stars' => $stars,
  ]) ?>
</section>
