<?php

/**
 * Move news articles with published_date <= 2023-12-31 to "In review" (unlisted).
 * Run: php scripts/set-news-in-review.php
 */

$root = dirname(__DIR__);
chdir($root);

require $root . '/kirby/bootstrap.php';

const CUTOFF = '2023-12-31';

$kirby = new Kirby([
    'roots' => [
        'index' => $root,
    ],
]);
$kirby->impersonate('kirby');

$news = $kirby->page('news');
if (!$news) {
    fwrite(STDERR, "News page not found.\n");
    exit(1);
}

$counts = [
    'moved_to_in_review' => 0,
    'already_in_review' => 0,
    'remain_listed' => 0,
    'remain_draft' => 0,
    'skipped_no_date' => 0,
];

$articleIds = $news->children()
    ->filterBy('intendedTemplate', 'news-article')
    ->pluck('id');

foreach ($articleIds as $id) {
    $article = $kirby->page($id);
    if (!$article) {
        continue;
    }
    $dateValue = trim((string)$article->published_date()->value());

    if ($dateValue === '') {
        $counts['skipped_no_date']++;
        echo "Skip (no published_date): {$article->slug()}\n";
        continue;
    }

    $published = strtotime($dateValue);
    $cutoff = strtotime(CUTOFF);

    if ($published === false) {
        $counts['skipped_no_date']++;
        echo "Skip (invalid date): {$article->slug()} ({$dateValue})\n";
        continue;
    }

    if ($article->isDraft()) {
        $counts['remain_draft']++;
        continue;
    }

    if ($published > $cutoff) {
        if ($article->isListed()) {
            $counts['remain_listed']++;
        } elseif ($article->isUnlisted()) {
            $counts['already_in_review']++;
        }
        continue;
    }

    if ($article->isUnlisted()) {
        $counts['already_in_review']++;
        continue;
    }

    if ($article->isListed()) {
        $article->changeStatus('unlisted');
        $counts['moved_to_in_review']++;
        echo "In review: {$article->slug()} ({$dateValue})\n";
    }
}

echo "\n--- Summary ---\n";
foreach ($counts as $key => $value) {
    echo str_replace('_', ' ', $key) . ": {$value}\n";
}
