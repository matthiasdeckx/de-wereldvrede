<?php
/**
 * News article body — shared by full page and overlay fragment.
 *
 * @var \Kirby\Cms\Page $page
 */
$page = $page ?? null;

if (!$page) {
  return;
}

$formatNewsInfoValue = static function ($value): string {
  $value = trim((string)$value);
  if ($value === '') {
    return '';
  }

  if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
    return '<a href="mailto:' . esc($value) . '">' . esc($value) . '</a>';
  }

  if (
    filter_var($value, FILTER_VALIDATE_URL) ||
    str_starts_with($value, 'http://') ||
    str_starts_with($value, 'https://') ||
    str_starts_with($value, 'mailto:')
  ) {
    $href = str_starts_with($value, 'mailto:') ? $value : $value;

    $icon = snippet('objects/icon-external', [], true);

    return '<a href="' . esc($href) . '" target="_blank" rel="noopener">' . esc($value) . ' ' . $icon . '</a>';
  }

  return esc($value);
};

$externalLinks = [];

foreach ($page->external_links()->toStructure() as $item) {
  if ($item->url()->isEmpty()) {
    continue;
  }

  $externalLinks[] = [
    'label' => $item->label()->isNotEmpty()
      ? $item->label()->value()
      : ui_t('project.external_link'),
    'url' => $item->url()->toUrl(),
  ];
}

if ($externalLinks === [] && $page->external_url()->isNotEmpty()) {
  $externalLinks[] = [
    'label' => ui_t('project.external_link'),
    'url' => $page->external_url()->toUrl(),
  ];
}
?>

<article class="g-container c-news-article__layout">
  <header class="c-news-article__header">
    <h1 class="c-news-article__title t-display t-xlarge t-uppercase" id="news-article-title"><?= $page->title()->html() ?></h1>
    <?php if ($page->info()->isNotEmpty()): ?>
      <dl class="c-news-article__meta t-mono t-uppercase">
        <?php foreach ($page->info()->toStructure() as $item): ?>
          <?php if ($item->label()->isNotEmpty() && $item->value()->isNotEmpty()): ?>
            <div>
              <dt><?= $item->label()->html() ?></dt>
              <dd><?= $formatNewsInfoValue($item->value()->value()) ?></dd>
            </div>
          <?php endif ?>
        <?php endforeach ?>
      </dl>
    <?php endif ?>
  </header>
  <div class="c-news-article__main">
    <?php if ($image = $page->hero_image()->toFile()): ?>
      <?php
      $isPortrait = $image->height() > $image->width();
      snippet('objects/image', [
        'image' => $image,
        'wrapperClass' => $isPortrait ? 'c-image--portrait' : '',
        'sizes' => '66vw',
        'crop' => $isPortrait,
      ]) ?>
    <?php endif ?>
    <?php if ($page->published_date()->isNotEmpty()): ?>
      <time
        class="c-news-article__date t-mono t-uppercase"
        datetime="<?= $page->published_date()->toDate('Y-m-d') ?>"
      ><?= format_news_published_label($page->published_date()) ?></time>
    <?php endif ?>
    <div class="c-news-article__body t-body-lg t-rich-text"><?= $page->body()->kti() ?></div>
    <?php if ($page->content_blocks()->isNotEmpty()): ?>
      <div class="c-news-article__blocks">
        <?php snippet('components/content-blocks', ['blocks' => $page->content_blocks()]) ?>
      </div>
    <?php endif ?>
    <?php if ($externalLinks !== []): ?>
      <div class="c-news-article__external-links">
        <?php foreach ($externalLinks as $link): ?>
          <a
            class="c-external-link t-mono t-uppercase"
            href="<?= esc($link['url']) ?>"
            target="_blank"
            rel="noopener"
          ><?= esc($link['label']) ?> <?php snippet('objects/icon-external') ?></a>
        <?php endforeach ?>
      </div>
    <?php endif ?>
  </div>
</article>
