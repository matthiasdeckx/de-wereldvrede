<?php
if (get('overlay') === '1') {
  echo snippet('components/news-article-content', ['page' => $page], true);
  return;
}
?>

<?php snippet('header') ?>

<main class="c-site-main c-news-article">
  <?php snippet('components/news-article-content', ['page' => $page]) ?>
</main>

<?php snippet('footer') ?>
