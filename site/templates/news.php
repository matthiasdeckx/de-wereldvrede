<?php
snippet('header');

$articles = $page->children()->listed()->sortBy('published_date', 'desc');
$perPage = 12;
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
      class="c-news-grid"
      data-news-grid
      data-news-per-page="<?= $perPage ?>"
      data-news-loaded-message="<?= esc(ui_t('news.loaded_more'), 'attr') ?>"
    >
      <?php foreach ($articles as $index => $article): ?>
        <?php $image = $article->cover_image()->toFile() ?: $article->hero_image()->toFile(); ?>
        <article class="c-news-card" data-news-card<?= $index >= $perPage ? ' hidden' : '' ?>>
          <a href="<?= $article->url() ?>" class="c-news-card__link">
            <?php if ($image): ?>
              <?php snippet('objects/image', [
                'image' => $image,
                'class' => 'c-news-card__image',
                'srcset' => 'small',
                'sizes' => '(min-width: 900px) 33vw, 100vw',
                'crop' => false,
              ]) ?>
            <?php endif ?>
            <h2 class="c-news-card__title t-display t-uppercase"><?= $article->title()->html() ?></h2>
            <div class="c-news-card__meta t-mono t-uppercase">
              <span><?= ui_t('news.read_more') ?></span>
              <?php if ($article->published_date()->isNotEmpty()): ?>
                <time datetime="<?= $article->published_date()->toDate('Y-m-d') ?>"><?= $article->published_date()->toDate('j F Y') ?></time>
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
