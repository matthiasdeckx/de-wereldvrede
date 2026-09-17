<?php snippet('header') ?>

<main class="c-site-main c-work">
  <div class="g-container">
    <h1 class="u-visually-hidden"><?= $page->title()->html() ?></h1>

    <?php
      $projects = $page->children()->listed();

      // Single pass for filter counts (avoids re-scanning all projects per type/status).
      $typeCounts = [];
      $statusCounts = [];
      foreach ($projects as $project) {
        foreach ($project->project_type()->split(',') as $type) {
          $type = trim($type);
          if ($type === '') {
            continue;
          }
          $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
        }
        foreach ($project->project_status()->split(',') as $status) {
          $status = trim($status);
          if ($status === '') {
            continue;
          }
          $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
        }
      }

      $configuredTypes = array_values(array_filter(
        array_map('trim', $site->project_types()->split(',')),
        fn (string $type) => $type !== '' && ($typeCounts[$type] ?? 0) > 0
      ));

      $configuredStatuses = array_values(array_filter(
        array_map('trim', $site->project_statuses()->split(',')),
        fn (string $status) => $status !== '' && ($statusCounts[$status] ?? 0) > 0
      ));
    ?>

    <?php if ($projects->isNotEmpty()): ?>
    <div
      class="c-work-filters c-work-filters--floating"
      data-work-filters
      data-floating-ui-dock
      data-filter-show-label="<?= esc(ui_t('work.filter.show'), 'attr') ?>"
      data-filter-hide-label="<?= esc(ui_t('work.filter.hide'), 'attr') ?>"
    >
      <button
        type="button"
        class="c-work-filters__toggle t-mono t-uppercase"
        data-work-filters-toggle
        aria-expanded="false"
        aria-controls="work-filters-panel"
        aria-label="<?= esc(ui_t('work.filter.show'), 'attr') ?>"
      >
        <span class="c-work-filters__toggle-label" data-work-filters-summary><?= ui_t('work.filter.all') ?> (<?= $projects->count() ?>)</span>
        <span class="c-work-filters__toggle-icon" aria-hidden="true">
          <span class="c-work-filters__toggle-icon-open">+</span>
          <span class="c-work-filters__toggle-icon-close">−</span>
        </span>
      </button>
      <div class="c-work-filters__panel" id="work-filters-panel" data-work-filters-panel>
        <div class="c-work-filters__group" role="group" aria-label="<?= esc(ui_t('work.filter.type'), 'attr') ?>">
          <button type="button" class="c-work-filters__btn is-active t-mono t-uppercase" data-filter-type="all"><?= ui_t('work.filter.all') ?> (<?= $projects->count() ?>)</button>
          <?php foreach ($configuredTypes as $type): ?>
            <button type="button" class="c-work-filters__btn t-mono t-uppercase" data-filter-type="<?= esc($type, 'attr') ?>"><?= esc($type) ?> (<?= $typeCounts[$type] ?>)</button>
          <?php endforeach ?>
        </div>
        <?php if ($configuredStatuses !== []): ?>
        <div class="c-work-filters__group" role="group" aria-label="<?= esc(ui_t('work.filter.status'), 'attr') ?>">
          <?php foreach ($configuredStatuses as $status): ?>
            <button type="button" class="c-work-filters__btn t-mono t-uppercase" data-filter-status="<?= esc($status, 'attr') ?>" data-filter-label="<?= esc($status, 'attr') ?>"><?= esc($status) ?> (<?= $statusCounts[$status] ?>)</button>
          <?php endforeach ?>
        </div>
        <?php endif ?>
      </div>
    </div>
    <?php endif ?>

    <div class="c-work-grid" data-work-grid>
      <?php $cardIndex = 0; ?>
      <?php foreach ($projects as $project): ?>
        <?php
          $cover = $project->cover()->toFile();
          $isPriorityCard = $cardIndex < 3;
        ?>
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
                'srcset' => 'card',
                'sizes' => '(min-width: 900px) 33vw, 100vw',
                'crop' => true,
                'loading' => $isPriorityCard ? 'eager' : 'lazy',
                'fetchpriority' => $isPriorityCard ? 'high' : null,
              ]) ?>
            <?php else: ?>
              <?php snippet('objects/image-placeholder') ?>
            <?php endif ?>
            <h2 class="c-work-card__title t-display t-uppercase"><?= $project->title()->html() ?></h2>
            <?php if ($project->subtitle()->isNotEmpty()): ?>
              <p class="c-work-card__subtitle t-mono t-uppercase"><?= $project->subtitle()->html() ?></p>
            <?php endif ?>
          </a>
        </article>
        <?php $cardIndex++; ?>
      <?php endforeach ?>
    </div>
  </div>
</main>

<?php snippet('footer') ?>
