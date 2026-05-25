<?php snippet('header') ?>

<main class="c-site-main c-work">
  <div class="g-container">
    <h1 class="u-visually-hidden"><?= $page->title()->html() ?></h1>

    <?php
      $projects = $page->children()->listed();
      $types = $site->project_types()->split(',');
      $statuses = $site->project_statuses()->split(',');
    ?>

    <div class="c-work-filters">
      <div class="c-work-filters__group" role="group" aria-label="<?= esc(ui_t('work.filter.type'), 'attr') ?>">
        <button type="button" class="c-work-filters__btn is-active t-mono t-uppercase" data-filter-type="all">All (<?= $projects->count() ?>)</button>
        <?php foreach ($types as $type): ?>
          <?php $type = trim($type); if (!$type) continue; ?>
          <?php $count = $projects->filter(fn($p) => in_array($type, $p->project_type()->split(','), true))->count(); ?>
          <button type="button" class="c-work-filters__btn t-mono t-uppercase" data-filter-type="<?= esc($type, 'attr') ?>"><?= esc($type) ?> (<?= $count ?>)</button>
        <?php endforeach ?>
      </div>
      <div class="c-work-filters__group" role="group" aria-label="<?= esc(ui_t('work.filter.status'), 'attr') ?>">
        <?php foreach ($statuses as $status): ?>
          <?php
            $status = trim($status);
            if (!$status) continue;
            $count = $projects->filter(fn($p) => in_array($status, $p->project_status()->split(','), true))->count();
          ?>
          <button type="button" class="c-work-filters__btn t-mono t-uppercase" data-filter-status="<?= esc($status, 'attr') ?>" data-filter-label="<?= esc($status, 'attr') ?>"><?= esc($status) ?> (<?= $count ?>)</button>
        <?php endforeach ?>
      </div>
    </div>

    <div class="c-work-grid" data-work-grid>
      <?php foreach ($projects as $project): ?>
        <?php $cover = $project->cover()->toFile(); ?>
        <article
          class="c-work-card"
          data-type="<?= esc(implode(',', $project->project_type()->split(',')), 'attr') ?>"
          data-status="<?= esc(implode(',', $project->project_status()->split(',')), 'attr') ?>"
        >
          <a class="c-work-card__link" href="<?= $project->url() ?>">
            <?php if ($cover): ?>
              <?php snippet('objects/image', [
                'image' => $cover,
                'class' => 'c-work-card__image',
                'sizes' => '(min-width: 900px) 33vw, 100vw',
                'crop' => true,
              ]) ?>
            <?php endif ?>
            <h2 class="c-work-card__title t-display t-uppercase"><?= $project->title()->html() ?></h2>
            <?php if ($project->subtitle()->isNotEmpty()): ?>
              <p class="c-work-card__subtitle t-mono t-uppercase"><?= $project->subtitle()->html() ?></p>
            <?php endif ?>
          </a>
        </article>
      <?php endforeach ?>
    </div>
  </div>
</main>

<?php snippet('footer') ?>
