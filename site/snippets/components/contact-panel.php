<div
  id="contact-panel"
  class="c-contact-panel t-mono t-uppercase"
  data-contact-panel
  hidden
  aria-hidden="true"
  role="region"
  aria-labelledby="contact-panel-title"
>
  <div class="c-contact-panel__inner">
    <div class="c-contact-panel__grid">
      <section class="c-contact-panel__col c-contact-panel__col--contact">
        <h2 id="contact-panel-title" class="c-contact-panel__heading"><?= ui_t('footer.contact') ?></h2>
        <?php if ($site->contact_email_mailto()->isNotEmpty()): ?>
          <a class="c-contact-panel__item" href="mailto:<?= $site->contact_email_mailto()->escape() ?>"><?= $site->contact_email_mailto()->html() ?></a>
        <?php endif ?>
        <?php if ($site->contact_address()->isNotEmpty()): ?>
          <?php if ($site->contact_address_url()->isNotEmpty()): ?>
            <?php
            $addressUrl = $site->contact_address_url()->toUrl();
            $addressExternal = Url::isAbsolute($addressUrl) && !Str::startsWith($addressUrl, $site->url());
            ?>
            <a class="c-contact-panel__item" href="<?= $addressUrl ?>"<?= $addressExternal ? ' target="_blank" rel="noopener"' : '' ?>><?= $site->contact_address()->kti() ?></a>
          <?php else: ?>
            <div class="c-contact-panel__item"><?= $site->contact_address()->kti() ?></div>
          <?php endif ?>
        <?php endif ?>
      </section>

      <section class="c-contact-panel__col c-contact-panel__col--social">
        <h2 class="c-contact-panel__heading"><?= ui_t('footer.social') ?></h2>
        <ul class="c-contact-panel__list">
          <?php foreach ($site->social_nav()->toStructure() as $social): ?>
            <?php if ($social->link()->isNotEmpty()): ?>
              <li>
                <a class="c-contact-panel__item" href="<?= $social->link()->toUrl() ?>" target="_blank" rel="noopener">
                  <?= $social->title()->or($social->label())->html() ?>
                </a>
              </li>
            <?php endif ?>
          <?php endforeach ?>
        </ul>
      </section>
    </div>
  </div>
</div>
