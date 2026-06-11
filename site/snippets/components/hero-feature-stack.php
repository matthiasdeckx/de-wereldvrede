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
 * @var array<int, array{label: string|null, names: string|null}>|null $hero_credits
 * @var string|null $intro
 * @var bool $dynamic_logo_size ratio-aware inline logo caps (homepage features)
 */
$titleHeading = in_array($title_heading ?? 'h2', ['h1', 'h2'], true) ? $title_heading : 'h2';
$titleType = $title_type ?? 'text';
$showCategory = $show_category ?? false;
$showIntro = $show_intro ?? false;
$dynamicLogoSize = $dynamic_logo_size ?? true;
$heroCredits = $hero_credits ?? [];
?>
<?php if ($showCategory && !empty($category)): ?>
  <p class="c-hero-feature__category t-mono t-uppercase"><?= esc($category) ?></p>
<?php endif ?>
<?php if ($titleType === 'logo' && ($title_logo ?? null)): ?>
  <?php $logoStyle = $dynamicLogoSize ? hero_feature_logo_style($title_logo) : null; ?>
  <img
    class="c-hero-feature__logo"
    src="<?= $title_logo->url() ?>"
    alt="<?= esc($title_text ?? '') ?>"
    <?php if ($logoStyle): ?> style="<?= esc($logoStyle, 'attr') ?>"<?php endif ?>
  >
<?php elseif (!empty($title_text)): ?>
  <?php if ($titleHeading === 'h1'): ?>
    <h1 class="c-hero-feature__title t-display t-xxlarge"><?= esc($title_text) ?></h1>
  <?php else: ?>
    <h2 class="c-hero-feature__title t-display t-xxlarge"><?= esc($title_text) ?></h2>
  <?php endif ?>
<?php endif ?>
<?php if ($heroCredits !== []): ?>
  <div class="c-hero-feature__credits">
    <?php foreach ($heroCredits as $credit): ?>
      <?php if (empty($credit['label']) && empty($credit['names'])) continue; ?>
      <div class="c-hero-feature__credit">
        <?php if (!empty($credit['label'])): ?>
          <p class="c-hero-feature__credits-label t-mono t-uppercase"><?= esc($credit['label']) ?></p>
        <?php endif ?>
        <?php if (!empty($credit['names'])): ?>
          <p class="c-hero-feature__credits-names t-mono t-uppercase"><?= esc($credit['names']) ?></p>
        <?php endif ?>
      </div>
    <?php endforeach ?>
  </div>
<?php endif ?>
<?php if ($showIntro && !empty($intro)): ?>
  <p class="c-hero-feature__intro t-body"><?= esc($intro) ?></p>
<?php endif ?>
