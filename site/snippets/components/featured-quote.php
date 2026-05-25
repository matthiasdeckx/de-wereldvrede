<?php
/**
 * Featured quote item
 *
 * @var \Kirby\Cms\Field $quote
 * @var \Kirby\Cms\Field $source
 * @var int $stars
 */
$stars = max(0, min(5, (int) ($stars ?? 0)));
?>
<figure class="c-project-featured-quote__item">
  <blockquote class="c-project-featured-quote__text t-display t-xlarge t-uppercase">
    <?= $quote->kti() ?>
  </blockquote>
  <?php if ($source->isNotEmpty() || $stars > 0): ?>
    <figcaption class="c-project-featured-quote__meta">
      <?php if ($stars > 0): ?>
        <div class="c-project-featured-quote__stars" role="img" aria-label="<?= $stars ?> out of 5 stars">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="c-project-featured-quote__star<?= $i <= $stars ? ' is-filled' : '' ?>" aria-hidden="true">★</span>
          <?php endfor ?>
        </div>
      <?php endif ?>
      <?php if ($source->isNotEmpty()): ?>
        <cite class="c-project-featured-quote__source t-mono t-uppercase"><?= $source->html() ?></cite>
      <?php endif ?>
    </figcaption>
  <?php endif ?>
</figure>
