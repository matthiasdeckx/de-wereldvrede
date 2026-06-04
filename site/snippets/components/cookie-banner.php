<div class="c-cookie-banner" data-cookie-banner hidden>
  <div class="c-cookie-banner__inner">
    <div class="c-cookie-banner__text t-caption"><?= $site->cookie_text()->or('We use cookies to improve your experience.')->kti() ?></div>
    <div class="c-cookie-banner__actions">
      <button type="button" class="c-cookie-banner__btn c-cookie-banner__btn--accept t-mono t-uppercase" data-cookie-accept><?= ui_t('cookie.accept') ?></button>
      <button type="button" class="c-cookie-banner__btn t-mono t-uppercase" data-cookie-deny><?= ui_t('cookie.deny') ?></button>
    </div>
  </div>
</div>
