<?php snippet('header') ?>

<main class="c-site-main c-work">
  <div class="g-container">
    <h1 class="u-visually-hidden"><?= $page->title()->html() ?></h1>

    <?php
      $projects = $page->children()->listed();

      $projectCountForType = function (string $type) use ($projects): int {
        return $projects->filter(
          fn ($p) => in_array($type, $p->project_type()->split(','), true)
        )->count();
      };

      $projectCountForStatus = function (string $status) use ($projects): int {
        return $projects->filter(
          fn ($p) => in_array($status, $p->project_status()->split(','), true)
        )->count();
      };

      $types = array_values(array_filter(
        array_map('trim', $site->project_types()->split(',')),
        fn (string $type) => $type !== '' && $projectCountForType($type) > 0
      ));

      $statuses = array_values(array_filter(
        array_map('trim', $site->project_statuses()->split(',')),
        fn (string $status) => $status !== '' && $projectCountForStatus($status) > 0
      ));
    ?>

    <?php if ($projects->isNotEmpty()): ?>
    <div class="c-work-filters">
      <div class="c-work-filters__group" role="group" aria-label="<?= esc(ui_t('work.filter.type'), 'attr') ?>">
        <button type="button" class="c-work-filters__btn is-active t-mono t-uppercase" data-filter-type="all">All (<?= $projects->count() ?>)</button>
        <?php foreach ($types as $type): ?>
          <button type="button" class="c-work-filters__btn t-mono t-uppercase" data-filter-type="<?= esc($type, 'attr') ?>"><?= esc($type) ?> (<?= $projectCountForType($type) ?>)</button>
        <?php endforeach ?>
      </div>
      <?php if ($statuses !== []): ?>
      <div class="c-work-filters__group" role="group" aria-label="<?= esc(ui_t('work.filter.status'), 'attr') ?>">
        <?php foreach ($statuses as $status): ?>
          <button type="button" class="c-work-filters__btn t-mono t-uppercase" data-filter-status="<?= esc($status, 'attr') ?>" data-filter-label="<?= esc($status, 'attr') ?>"><?= esc($status) ?> (<?= $projectCountForStatus($status) ?>)</button>
        <?php endforeach ?>
      </div>
      <?php endif ?>
    </div>
    <?php endif ?>

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
                'srcset' => 'small',
                'sizes' => '(min-width: 900px) 33vw, 100vw',
                'crop' => true,
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
      <?php endforeach ?>
    </div>
  </div>
</main>

<?php snippet('footer') ?>
