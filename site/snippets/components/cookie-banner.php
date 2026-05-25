<div class="c-cookie-banner c-floating-surface" data-cookie-banner hidden>
  <div class="c-cookie-banner__inner g-container">
    <p class="c-cookie-banner__text t-mono"><?= $site->cookie_text()->or('We use cookies to improve your experience.') ?></p>
    <div class="c-cookie-banner__actions">
      <button type="button" class="t-mono t-uppercase" data-cookie-accept><?= ui_t('cookie.accept') ?></button>
      <button type="button" class="t-mono t-uppercase" data-cookie-deny><?= ui_t('cookie.deny') ?></button>
      <button type="button" class="t-mono t-uppercase" data-cookie-preferences><?= ui_t('cookie.preferences') ?></button>
    </div>
  </div>
</div>
