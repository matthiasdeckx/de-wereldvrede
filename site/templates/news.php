<?php
snippet('header');

$articles = $page->children()->listed()->sortBy('published_date', 'desc');
$perPage = 12;
$cardImageLayout = $page->card_image_layout()->or('original')->value();
if (!in_array($cardImageLayout, ['original', 'landscape', 'portrait'], true)) {
  $cardImageLayout = 'original';
}
?>

<main class="c-site-main c-news">
  <div class="g-container">
    <h1 class="u-visually-hidden"><?= $page->title()->html() ?></h1>
    <div
      class="u-visually-hidden"
      aria-live="polite"
      data-news-live
    ></div>
    <div
      class="c-news-grid c-news-grid--card-image-<?= esc($cardImageLayout, 'attr') ?>"
      data-news-grid
      data-news-card-image-layout="<?= esc($cardImageLayout, 'attr') ?>"
      data-news-per-page="<?= $perPage ?>"
      data-news-loaded-message="<?= esc(ui_t('news.loaded_more'), 'attr') ?>"
    >
      <?php foreach ($articles as $index => $article): ?>
        <?php $image = $article->cover_image()->toFile() ?: $article->hero_image()->toFile(); ?>
        <article class="c-news-card" data-news-card<?= $index >= $perPage ? ' hidden' : '' ?>>
          <a href="<?= $article->url() ?>" class="c-news-card__link" data-news-open data-no-swup>
            <?php if ($image): ?>
              <?php
              $isPortrait = $cardImageLayout === 'original' && $image->height() > $image->width();
              $shouldCrop = $cardImageLayout !== 'original' || $isPortrait;
              snippet('objects/image', [
                'image' => $image,
                'class' => trim('c-news-card__image' . ($isPortrait ? ' c-news-card__image--portrait' : '')),
                'wrapperClass' => $isPortrait ? 'c-image--portrait' : '',
                'srcset' => 'small',
                'sizes' => '(min-width: 900px) 33vw, 100vw',
                'crop' => $shouldCrop,
              ]) ?>
            <?php endif ?>
            <h2 class="c-news-card__title t-display t-uppercase"><?= $article->title()->html() ?></h2>
            <div class="c-news-card__meta t-mono t-uppercase">
              <span class="c-news-card__read-more">
                <span class="c-news-card__read-more-text"><?= ui_t('news.read_more') ?></span>
              </span>
              <?php if ($article->published_date()->isNotEmpty()): ?>
                <time datetime="<?= $article->published_date()->toDate('Y-m-d') ?>"><?= format_news_published_label($article->published_date()) ?></time>
              <?php endif ?>
            </div>
          </a>
        </article>
      <?php endforeach ?>
      <div class="c-news-grid__sentinel" data-news-sentinel aria-hidden="true"></div>
    </div>
  </div>
</main>

<?php snippet('footer') ?>
