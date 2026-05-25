<?php

/**
 * Move projects with year <= 2023 to "In review" (unlisted).
 * Projects with year >= 2024 stay listed (Published).
 * Run: php scripts/set-projects-in-review.php
 */

$root = dirname(__DIR__);
chdir($root);

require $root . '/kirby/bootstrap.php';

const CUTOFF_YEAR = 2023;

$kirby = new Kirby([
    'roots' => [
        'index' => $root,
    ],
]);
$kirby->impersonate('kirby');

$work = $kirby->page('work');
if (!$work) {
    fwrite(STDERR, "Work page not found.\n");
    exit(1);
}

$counts = [
    'moved_to_in_review' => 0,
    'already_in_review' => 0,
    'remain_published' => 0,
    'remain_draft' => 0,
    'skipped_no_year' => 0,
];

$projectIds = $work->children()
    ->filterBy('intendedTemplate', 'project')
    ->pluck('id');

foreach ($projectIds as $id) {
    $project = $kirby->page($id);
    if (!$project) {
        continue;
    }

    $yearValue = trim((string)$project->year()->value());

    if ($yearValue === '') {
        $counts['skipped_no_year']++;
        echo "Skip (no year): {$project->slug()}\n";
        continue;
    }

    if (!ctype_digit($yearValue)) {
        $counts['skipped_no_year']++;
        echo "Skip (invalid year): {$project->slug()} ({$yearValue})\n";
        continue;
    }

    $year = (int)$yearValue;

    if ($project->isDraft()) {
        $counts['remain_draft']++;
        continue;
    }

    if ($year > CUTOFF_YEAR) {
        if ($project->isListed()) {
            $counts['remain_published']++;
        } elseif ($project->isUnlisted()) {
            $counts['already_in_review']++;
        }
        continue;
    }

    if ($project->isUnlisted()) {
        $counts['already_in_review']++;
        continue;
    }

    if ($project->isListed()) {
        $project->changeStatus('unlisted');
        $project = $kirby->page($id);
        if (!$project) {
            continue;
        }
        $counts['moved_to_in_review']++;
        echo "In review: {$project->slug()} ({$year})\n";
    }
}

echo "\n--- Summary ---\n";
foreach ($counts as $key => $value) {
    echo str_replace('_', ' ', $key) . ": {$value}\n";
}
