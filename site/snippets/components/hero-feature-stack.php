<?php
/**
 * Shared hero / feature slide content stack.
 *
 * @var bool $show_category
 * @var bool $show_intro
 * @var string|null $category
 * @var string|null $title_type
 * @var \Kirby\Cms\File|null $title_logo
 * @var string|null $title_text
 * @var string $title_heading h1|h2
 * @var string|null $credits_label
 * @var string|null $credits_names
 * @var string|null $intro
 */
$titleHeading = in_array($title_heading ?? 'h2', ['h1', 'h2'], true) ? $title_heading : 'h2';
$titleType = $title_type ?? 'text';
$showCategory = $show_category ?? false;
$showIntro = $show_intro ?? false;
?>
<?php if ($showCategory && !empty($category)): ?>
  <p class="c-hero-feature__category t-mono t-uppercase"><?= esc($category) ?></p>
<?php endif ?>
<?php if ($titleType === 'logo' && ($title_logo ?? null)): ?>
  <img
    class="c-hero-feature__logo"
    src="<?= $title_logo->url() ?>"
    alt="<?= esc($title_text ?? '') ?>"
  >
<?php elseif (!empty($title_text)): ?>
  <?php if ($titleHeading === 'h1'): ?>
    <h1 class="c-hero-feature__title t-display t-xxlarge"><?= esc($title_text) ?></h1>
  <?php else: ?>
    <h2 class="c-hero-feature__title t-display t-xxlarge"><?= esc($title_text) ?></h2>
  <?php endif ?>
<?php endif ?>
<?php if (!empty($credits_label) || !empty($credits_names)): ?>
  <div class="c-hero-feature__credits">
    <?php if (!empty($credits_label)): ?>
      <p class="c-hero-feature__credits-label t-mono t-uppercase"><?= esc($credits_label) ?></p>
    <?php endif ?>
    <?php if (!empty($credits_names)): ?>
      <p class="c-hero-feature__credits-names t-mono t-uppercase"><?= esc($credits_names) ?></p>
    <?php endif ?>
  </div>
<?php endif ?>
<?php if ($showIntro && !empty($intro)): ?>
  <p class="c-hero-feature__intro t-body"><?= esc($intro) ?></p>
<?php endif ?>
