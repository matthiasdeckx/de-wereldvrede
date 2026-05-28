<?php
$title = $site->title()->escape();
$pageTitle = $page->isHomePage() ? $title : $page->title()->escape() . ' | ' . $title;
$navItems = $site->navigation()->toPages();
$isLightPage = $page->usesLightTheme();
$bodyClasses = ['site-page', 'site-page-' . $page->intendedTemplate()];
if ($isLightPage) {
  $bodyClasses[] = 'is-light';
}
if ($page->isHomePage()) {
  $bodyClasses[] = 'site-page-home';
}
?>
<!doctype html>
<html class="no-js" lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="format-detection" content="telephone=no">
  <meta name="referrer" content="strict-origin-when-cross-origin">
  <meta name="theme-color" content="#000000">
  <meta name="color-scheme" content="dark">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="author" content="<?= $site->title()->html() ?>">
  <meta name="robots" content="index,follow">

  <?php snippet('head/meta') ?>
  <?php snippet('head/favicon') ?>

  <link rel="canonical" href="<?= $page->url() ?>">
  <link rel="home" href="<?= $site->url() ?>">
  <link rel="preload" href="/assets/fonts/ABCDiatypeCondensed-Bold.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/assets/fonts/ABCDiatype-Regular.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/assets/fonts/ABCDiatypeMono-Regular.woff2" as="font" type="font/woff2" crossorigin>

  <?= mix('css/main.css') ?>
  <?php snippet('head/analytics') ?>

  <script>(function(H){H.className=H.className.replace(/\bno-js\b/,'js')})(document.documentElement)</script>
</head>

<body class="<?= implode(' ', $bodyClasses) ?>">
  <header class="c-floating-header" data-floating-header>
    <div class="c-floating-header__unit">
      <div class="c-floating-header__inner">
        <a class="c-floating-header__brand" href="<?= $site->url() ?>" aria-label="<?= $site->title()->escape() ?>">
          <img
            class="c-floating-header__logo"
            src="<?= url('assets/images/dw-logo--white.svg') ?>"
            alt=""
            width="1920"
            height="311"
          >
        </a>
        <div class="c-floating-header__actions">
          <nav class="c-floating-header__nav c-floating-header__nav--desktop" aria-label="<?= ui_t('aria.nav_primary') ?>">
            <ul class="c-floating-header__list">
              <?php foreach ($navItems as $item): ?>
                <li>
                  <a href="<?= $item->url() ?>" <?= e($item->isOpen(), 'aria-current="page"') ?> class="t-mono t-uppercase"><?= $item->title()->html() ?></a>
                </li>
              <?php endforeach ?>
              <li class="c-floating-header__slot">
                <button type="button" class="c-floating-header__slot-action c-floating-header__slot-action--default t-mono t-uppercase" data-contact-open><?= ui_t('nav.contact') ?></button>
                <button type="button" class="c-floating-header__slot-action c-floating-header__slot-action--close t-mono t-uppercase" data-overlay-close aria-hidden="true" tabindex="-1"><?= ui_t('nav.close') ?></button>
              </li>
            </ul>
          </nav>
          <div class="c-floating-header__slot c-floating-header__slot--mobile">
            <button type="button" class="c-floating-header__slot-action c-floating-header__slot-action--default t-mono t-uppercase" data-mobile-nav-toggle aria-expanded="false" aria-controls="mobile-nav">
              <?= ui_t('nav.menu') ?>
            </button>
            <button type="button" class="c-floating-header__slot-action c-floating-header__slot-action--close t-mono t-uppercase" data-overlay-close aria-hidden="true" tabindex="-1"><?= ui_t('nav.close') ?></button>
          </div>
        </div>
      </div>
      <div class="c-mobile-nav" id="mobile-nav" hidden aria-hidden="true">
        <nav aria-label="<?= ui_t('aria.nav_mobile') ?>">
          <ul class="c-mobile-nav__list">
            <?php foreach ($navItems as $item): ?>
              <li>
                <a href="<?= $item->url() ?>" <?= e($item->isOpen(), 'aria-current="page"') ?> class="t-mono t-uppercase"><?= $item->title()->html() ?></a>
              </li>
            <?php endforeach ?>
            <li>
              <button type="button" class="t-mono t-uppercase" data-contact-open><?= ui_t('nav.contact') ?></button>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </header>

  <a href="#main" class="u-visually-hidden u-visually-hidden-focusable"><?= ui_t('aria.skip_to_content') ?></a>

  <div id="main" class="transition-fade">
  <div
    class="u-visually-hidden"
    aria-hidden="true"
    data-page-meta
    data-page-theme="<?= $isLightPage ? 'light' : 'dark' ?>"
    data-page-home="<?= $page->isHomePage() ? 'true' : 'false' ?>"
  ></div>
