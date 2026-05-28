<?php
$formatNewsPublishedLabel = static function ($field): string {
  if ($field->isEmpty()) {
    return '';
  }

  $published = $field->toDate('Y-m-d');
  $publishedTs = strtotime($published . ' 00:00:00');
  $todayTs = strtotime('today');
  $days = max(0, (int) floor(($todayTs - $publishedTs) / 86400));

  if ($days === 0) {
    return (string) ui_t('news.published.today');
  }

  if ($days === 1) {
    return (string) ui_t('news.published.yesterday');
  }

  if ($days < 7) {
    return (string) ui_tt('news.published.days_ago', ['count' => $days]);
  }

  if ($days < 14) {
    return (string) ui_t('news.published.week_ago');
  }

  if ($days < 60) {
    $weeks = (int) floor($days / 7);

    return (string) ui_tt('news.published.weeks_ago', ['count' => $weeks]);
  }

  return strtoupper($field->toDate('j F Y'));
};

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
?>

<?php snippet('header') ?>

<main class="c-site-main c-news-article">
  <article class="g-container c-news-article__layout">
    <header class="c-news-article__header">
      <h1 class="c-news-article__title t-display t-xlarge t-uppercase"><?= $page->title()->html() ?></h1>
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
        <?php snippet('objects/image', ['image' => $image, 'sizes' => '66vw', 'crop' => false]) ?>
      <?php endif ?>
      <?php if ($page->published_date()->isNotEmpty()): ?>
        <time
          class="c-news-article__date t-mono t-uppercase"
          datetime="<?= $page->published_date()->toDate('Y-m-d') ?>"
        ><?= $formatNewsPublishedLabel($page->published_date()) ?></time>
      <?php endif ?>
      <div class="c-news-article__body t-body-lg t-rich-text"><?= $page->body()->kti() ?></div>
      <?php if ($page->content_blocks()->isNotEmpty()): ?>
        <div class="c-news-article__blocks">
          <?php snippet('components/content-blocks', ['blocks' => $page->content_blocks()]) ?>
        </div>
      <?php endif ?>
      <?php if ($page->external_url()->isNotEmpty()): ?>
        <a class="c-btn t-mono t-uppercase" href="<?= $page->external_url()->toUrl() ?>" target="_blank" rel="noopener"><?= ui_t('project.external_link') ?> <?php snippet('objects/icon-external') ?></a>
      <?php endif ?>
    </div>
    <aside class="c-news-article__aside">
      <?php if ($image = $page->secondary_image()->toFile()): ?>
        <?php snippet('objects/image', ['image' => $image, 'sizes' => '50vw', 'crop' => false]) ?>
      <?php endif ?>
      <?php if ($page->pull_quote()->isNotEmpty()): ?>
        <blockquote class="c-news-article__quote t-display t-xlarge t-uppercase"><?= $page->pull_quote()->kti() ?></blockquote>
        <?php if ($page->pull_quote_caption()->isNotEmpty()): ?>
          <cite class="t-mono t-uppercase"><?= $page->pull_quote_caption()->html() ?></cite>
        <?php endif ?>
      <?php endif ?>
    </aside>
  </article>
</main>

<?php snippet('footer') ?>
