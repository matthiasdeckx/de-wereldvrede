<footer class="c-site-footer t-mono t-uppercase">
  <div class="g-container c-site-footer__grid">
    <section class="c-site-footer__col c-site-footer__col--contact">
      <h2 class="c-site-footer__heading"><?= ui_t('footer.contact') ?></h2>
      <?php if ($site->contact_email_mailto()->isNotEmpty()): ?>
        <a class="c-site-footer__item" href="mailto:<?= $site->contact_email_mailto()->escape() ?>"><?= $site->contact_email_mailto()->html() ?></a>
      <?php endif ?>
      <?php if ($site->contact_address()->isNotEmpty()): ?>
        <?php if ($site->contact_address_url()->isNotEmpty()): ?>
          <?php
          $addressUrl = $site->contact_address_url()->toUrl();
          $addressExternal = Url::isAbsolute($addressUrl) && !Str::startsWith($addressUrl, $site->url());
          ?>
          <a class="c-site-footer__item" href="<?= $addressUrl ?>"<?= $addressExternal ? ' target="_blank" rel="noopener"' : '' ?>><?= $site->contact_address()->kti() ?></a>
        <?php else: ?>
          <div class="c-site-footer__item"><?= $site->contact_address()->kti() ?></div>
        <?php endif ?>
      <?php endif ?>
      <?php if ($site->contact_vat()->isNotEmpty()): ?>
        <div class="c-site-footer__item"><?= $site->contact_vat()->html() ?></div>
      <?php endif ?>
    </section>

    <section class="c-site-footer__col c-site-footer__col--social">
      <h2 class="c-site-footer__heading"><?= ui_t('footer.social') ?></h2>
      <ul class="c-site-footer__list">
        <?php foreach ($site->social_nav()->toStructure() as $social): ?>
          <?php if ($social->link()->isNotEmpty()): ?>
            <li>
              <a class="c-site-footer__item" href="<?= $social->link()->toUrl() ?>" target="_blank" rel="noopener">
                <?= $social->title()->or($social->label())->html() ?>
              </a>
            </li>
          <?php endif ?>
        <?php endforeach ?>
      </ul>
    </section>

    <section class="c-site-footer__col c-site-footer__col--team">
      <h2 class="c-site-footer__heading"><?= ui_t('footer.team') ?></h2>
      <ul class="c-site-footer__list">
        <?php foreach ($site->footer_team()->toStructure() as $member): ?>
          <li class="c-site-footer__item"><?= $member->name()->html() ?></li>
        <?php endforeach ?>
      </ul>
      <?php if ($site->support_text()->isNotEmpty() || $site->support_logo()->isNotEmpty()): ?>
        <div class="c-site-footer__support">
          <?php if ($site->support_text()->isNotEmpty()): ?>
            <p class="c-site-footer__item"><?= $site->support_text()->or('DEVELOPED WITH THE SUPPORT OF')->html() ?></p>
          <?php endif ?>
          <?php if ($logo = $site->support_logo()->toFile()): ?>
            <?php snippet('objects/image', [
              'image' => $logo,
              'class' => 'c-site-footer__support-logo',
              'sizes' => '160px',
              'crop' => false,
            ]) ?>
          <?php endif ?>
        </div>
      <?php endif ?>
    </section>
  </div>

  <div class="g-container">
  <div class="c-site-footer__legal">
    <p class="c-site-footer__item">&copy;<?= date('Y') ?> <?= strtoupper($site->title()->value()) ?>. <?= ui_t('footer.rights') ?></p>
    <?php $disclaimerPage = $site->find('disclaimer') ?>
    <?php if ($disclaimerPage): ?>
      <a class="c-site-footer__item" href="<?= $disclaimerPage->url() ?>"><?= ui_t('footer.disclaimer') ?></a>
    <?php elseif ($site->disclaimer_url()->isNotEmpty()): ?>
      <a class="c-site-footer__item" href="<?= $site->disclaimer_url()->toUrl() ?>"><?= ui_t('footer.disclaimer') ?></a>
    <?php else: ?>
      <span class="c-site-footer__item"><?= ui_t('footer.disclaimer') ?></span>
    <?php endif ?>
  </div>
  </div>

  <a class="c-site-footer__logo-link" href="<?= $site->url() ?>" aria-label="<?= $site->title()->escape() ?>">
    <?php snippet('objects/logo-footer') ?>
  </a>
</footer>
