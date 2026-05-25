<?php snippet('header') ?>

<main class="c-site-main c-creators">
  <h1 class="u-visually-hidden"><?= $page->title()->html() ?></h1>
  <ul class="c-creators-list">
    <?php foreach ($page->creators()->toStructure() as $index => $creator): ?>
      <li>
        <button
          type="button"
          class="c-creators-list__name t-display t-xxlarge t-uppercase"
          data-creator-open
          data-creator-index="<?= $index ?>"
        ><?= $creator->name()->html() ?></button>
      </li>
    <?php endforeach ?>
  </ul>

  <template id="creators-data">
    <?= json_encode($page->creators()->toStructure()->map(function ($c) {
      return [
        'name' => $c->name()->value(),
        'role' => $c->role()->value(),
        'bio' => $c->bio()->value(),
        'portrait' => $c->portrait()->toFile()?->url(),
        'productions' => $c->productions()->toPages()->map(fn($p) => [
          'title' => $p->title()->value(),
          'url' => $p->url(),
        ])->values(),
      ];
    })->values()) ?>
  </template>
</main>

<?php snippet('footer') ?>
