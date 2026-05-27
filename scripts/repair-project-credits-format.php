<?php

/**
 * Fix formatting flaws in imported project credits (role casing, name spacing).
 * Run: php scripts/repair-project-credits-format.php
 * Dry run: php scripts/repair-project-credits-format.php --dry-run
 */

$root = dirname(__DIR__);
chdir($root);

require $root . '/kirby/bootstrap.php';

const SMALL_ROLE_WORDS = [
    'a', 'an', 'and', 'by', 'de', 'du', 'en', 'et', 'for', 'in', 'la', 'le', 'les', 'of', 'the', 'with',
];

function formatCreditRole(string $role): string
{
    $role = trim(preg_replace('/\s+/u', ' ', str_replace("\xc2\xa0", ' ', $role)));

    if ($role === '') {
        return $role;
    }

    if (preg_match('/^(.+?)(\s*\(.+\))$/u', $role, $match)) {
        return formatCreditRoleWords($match[1]) . $match[2];
    }

    return formatCreditRoleWords($role);
}

function formatCreditRoleWords(string $role): string
{
    $words = preg_split('/\s/u', $role, -1, PREG_SPLIT_NO_EMPTY);
    $formatted = [];

    foreach ($words as $index => $word) {
        if ($word === '&') {
            $formatted[] = '&';
            continue;
        }

        if (preg_match('/^[A-Z0-9&\/\-]+$/u', $word) && preg_match('/[A-Z]/', $word)) {
            $formatted[] = $word;
            continue;
        }

        $lower = mb_strtolower($word, 'UTF-8');

        if ($index === 0 || !in_array($lower, SMALL_ROLE_WORDS, true)) {
            $first = mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8');
            $rest = mb_substr($lower, 1, null, 'UTF-8');
            $formatted[] = $first . $rest;
        } else {
            $formatted[] = $lower;
        }
    }

    return implode(' ', $formatted);
}

function formatCreditNames(string $names): string
{
    $names = str_replace("\xc2\xa0", ' ', $names);
    $names = preg_replace('/^\s+/u', '', $names);
    $names = preg_replace('/\s+$/u', '', $names);

    return $names;
}

function repairCreditsStructure($creditsField): array
{
    $repaired = [];
    $changed = false;

    foreach ($creditsField->toStructure() as $credit) {
        $role = $credit->role()->value();
        $names = $credit->names()->value();

        $newRole = formatCreditRole($role);
        $newNames = formatCreditNames($names);

        if ($newRole !== $role || $newNames !== $names) {
            $changed = true;
        }

        $repaired[] = [
            'role' => $newRole,
            'names' => $newNames,
        ];
    }

    return [$repaired, $changed];
}

$dryRun = in_array('--dry-run', $argv ?? [], true);

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

$updated = 0;
$skipped = 0;
$samples = [];

foreach ($work->childrenAndDrafts() as $page) {
    if ($page->intendedTemplate()->name() !== 'project') {
        continue;
    }

    if ($page->credits()->isEmpty()) {
        $skipped++;
        continue;
    }

    [$repairedCredits, $changed] = repairCreditsStructure($page->credits());

    if (!$changed) {
        $skipped++;
        continue;
    }

    $beforeAfter = [
        'slug' => $page->slug(),
        'changes' => [],
    ];

    foreach ($page->credits()->toStructure() as $index => $credit) {
        $oldRole = $credit->role()->value();
        $oldNames = $credit->names()->value();
        $newRole = $repairedCredits[$index]['role'] ?? $oldRole;
        $newNames = $repairedCredits[$index]['names'] ?? $oldNames;

        if ($oldRole !== $newRole || $oldNames !== $newNames) {
            $beforeAfter['changes'][] = [
                'role' => ['before' => $oldRole, 'after' => $newRole],
                'names' => ['before' => $oldNames, 'after' => $newNames],
            ];
        }
    }

    if (count($samples) < 5) {
        $samples[] = $beforeAfter;
    }

    echo ($dryRun ? 'Would update' : 'Updating') . ": {$page->slug()}\n";

    if (!$dryRun) {
        try {
            $page->update(['credits' => $repairedCredits]);
            $updated++;
        } catch (Throwable $e) {
            echo "  Failed: {$e->getMessage()}\n";
        }
    } else {
        $updated++;
    }
}

if (!$dryRun) {
    $kirby->cache('pages')->flush();
}

echo "\n--- Summary ---\n";
echo ($dryRun ? 'Would update' : 'Updated') . ": {$updated}\n";
echo "Skipped (no changes / no credits): {$skipped}\n";

if ($samples !== []) {
    echo "\n--- Sample changes ---\n";
    foreach ($samples as $sample) {
        echo "\n{$sample['slug']}:\n";
        foreach ($sample['changes'] as $change) {
            if ($change['role']['before'] !== $change['role']['after']) {
                echo "  role:   \"{$change['role']['before']}\" → \"{$change['role']['after']}\"\n";
            }
            if ($change['names']['before'] !== $change['names']['after']) {
                $before = str_replace("\xc2\xa0", '·', $change['names']['before']);
                $after = str_replace("\xc2\xa0", '·', $change['names']['after']);
                echo "  names:  \"{$before}\" → \"{$after}\"\n";
            }
        }
    }
}
