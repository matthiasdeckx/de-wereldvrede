<?php
/**
 * Backup of the full-screen contact overlay (pre contact-panel).
 * Original active snippet: site/snippets/overlays/contact.php
 * CSS preserved in src/assets/css/components/c.overlay.css (.c-contact-overlay*)
 */
?>
<div class="c-overlay c-overlay--contact" data-overlay="contact" hidden aria-hidden="true">
  <div class="c-overlay__backdrop" data-overlay-close></div>
  <div class="c-overlay__panel c-contact-overlay t-xlarge t-display" role="dialog" aria-modal="true" aria-labelledby="contact-overlay-title">
    <div class="g-container c-contact-overlay__grid">
      <h2 id="contact-overlay-title" class="c-contact-overlay__title u-visually-hidden"><?= ui_t('nav.contact') ?></h2>
      <div class="c-contact-overlay__content">
        <?php if ($site->contact_address()->isNotEmpty()): ?>
          <div class="c-contact-overlay__group c-contact-overlay__group--address">
            <?php if ($site->contact_address_url()->isNotEmpty()): ?>
              <?php
              $addressUrl = $site->contact_address_url()->toUrl();
              $addressExternal = Url::isAbsolute($addressUrl) && !Str::startsWith($addressUrl, $site->url());
              ?>
              <a class="c-contact-overlay__text" href="<?= $addressUrl ?>"<?= $addressExternal ? ' target="_blank" rel="noopener"' : '' ?>><?= $site->contact_address()->kti() ?></a>
            <?php else: ?>
              <div class="c-contact-overlay__text"><?= $site->contact_address()->kti() ?></div>
            <?php endif ?>
          </div>
        <?php endif ?>
        <?php if ($site->contact_email_mailto()->isNotEmpty()): ?>
          <div class="c-contact-overlay__group c-contact-overlay__group--email">
            <a class="c-contact-overlay__text" href="mailto:<?= $site->contact_email_mailto()->escape() ?>"><?= $site->contact_email_mailto()->html() ?></a>
          </div>
        <?php endif ?>
        <?php if ($site->social_nav()->isNotEmpty()): ?>
          <div class="c-contact-overlay__group c-contact-overlay__group--social">
            <?php foreach ($site->social_nav()->toStructure() as $social): ?>
              <?php if ($social->link()->isNotEmpty()): ?>
                <a class="c-contact-overlay__text" href="<?= $social->link()->toUrl() ?>" target="_blank" rel="noopener"><?= $social->title()->or($social->label())->html() ?></a>
              <?php endif ?>
            <?php endforeach ?>
          </div>
        <?php endif ?>
      </div>
    </div>
  </div>
</div>
