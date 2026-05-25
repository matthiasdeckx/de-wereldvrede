<?php snippet('header') ?>

<main class="c-site-main c-about">
  <div class="g-container">
    <h1 class="u-visually-hidden"><?= $page->title()->html() ?></h1>

    <?php if ($page->intro_text()->isNotEmpty()): ?>
      <section class="c-about__intro t-display t-xlarge t-uppercase"><?= $page->intro_text()->kti() ?></section>
    <?php endif ?>

    <?php if ($page->about_body()->isNotEmpty()): ?>
      <section class="c-about__section">
        <div class="c-split-section">
          <p class="c-split-section__label t-mono t-uppercase"><?= $page->about_label()->or('DE WERELDVREDE')->html() ?></p>
          <div class="c-split-section__content t-rich-text"><?= $page->about_body()->kt() ?></div>
        </div>
      </section>
    <?php endif ?>

    <?php if ($page->team()->isNotEmpty()): ?>
      <?php
        $teamGrid = [
          ['start' => 1, 'span' => 4, 'row' => 1, 'offset' => 2],
          ['start' => 6, 'span' => 3, 'row' => 1, 'offset' => 0],
          ['start' => 9, 'span' => 4, 'row' => 1, 'offset' => 4.5],
          ['start' => 3, 'span' => 4, 'row' => 2, 'offset' => 3.5],
          ['start' => 8, 'span' => 3, 'row' => 3, 'offset' => 0.75],
          ['start' => 1, 'span' => 3, 'row' => 4, 'offset' => 2.25],
          ['start' => 10, 'span' => 3, 'row' => 4, 'offset' => 4],
        ];
      ?>
      <section class="c-about__section c-about__section--team">
        <div class="c-about__team" role="list">
          <?php foreach ($page->team()->toStructure() as $index => $member): ?>
            <?php
              $layout = $teamGrid[$index % count($teamGrid)];
              $portrait = $member->image()->toFile();
            ?>
            <figure
              class="c-about__team-member"
              role="listitem"
              style="--team-col-start: <?= (int) $layout['start'] ?>; --team-col-span: <?= (int) $layout['span'] ?>; --team-row-start: <?= (int) $layout['row'] ?>; --team-offset: <?= (float) $layout['offset'] ?>;"
            >
              <?php if ($portrait): ?>
                <div class="c-about__team-portrait">
                  <?php snippet('objects/image', [
                    'image' => $portrait,
                    'class' => 'c-about__team-portrait-img',
                    'sizes' => '(min-width: 1025px) 25vw, (min-width: 641px) 50vw, 100vw',
                    'crop' => true,
                  ]) ?>
                </div>
              <?php endif ?>
              <figcaption class="c-about__team-caption t-mono t-uppercase">
                <?php if ($member->name()->isNotEmpty()): ?>
                  <span class="c-about__team-name"><?= $member->name()->html() ?></span>
                <?php endif ?>
                <?php if ($member->role()->isNotEmpty()): ?>
                  <span class="c-about__team-role"><?= $member->role()->html() ?></span>
                <?php endif ?>
              </figcaption>
            </figure>
          <?php endforeach ?>
        </div>
      </section>
    <?php endif ?>

    <?php if ($page->awards()->isNotEmpty()): ?>
      <section class="c-about__section c-about__section--awards">
        <div class="c-about__layout">
          <p class="c-about__label t-mono t-uppercase"><?= $page->awards_label()->or('AWARDS & NOMINATIONS')->html() ?></p>
          <div class="c-about__awards" role="list">
            <?php foreach ($page->awards()->toStructure() as $award): ?>
              <?php
                $projectPage = $award->project_page()->toPage();
                $projectName = $award->project()->or($projectPage?->title());
              ?>
              <div class="c-about__award t-mono t-uppercase" role="listitem">
                <span class="c-about__award-title"><?= $award->title()->html() ?></span>
                <?php if ($projectName->isNotEmpty()): ?>
                  <span class="c-about__award-project">
                    <?php if ($projectPage): ?>
                      <a href="<?= $projectPage->url() ?>"><?= $projectName->html() ?></a>
                    <?php else: ?>
                      <?= $projectName->html() ?>
                    <?php endif ?>
                  </span>
                <?php endif ?>
                <?php if ($award->year()->isNotEmpty()): ?>
                  <span class="c-about__award-year"><?= $award->year()->html() ?></span>
                <?php endif ?>
              </div>
            <?php endforeach ?>
          </div>
        </div>
      </section>
    <?php endif ?>
  </div>
</main>

<?php snippet('footer') ?>
