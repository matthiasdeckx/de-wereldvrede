<?php

/**
 * Repair bundled project credits by re-scraping the legacy site.
 * Run: php scripts/repair-project-credits.php
 * Force all projects: php scripts/repair-project-credits.php --all
 */

require dirname(__DIR__) . '/kirby/bootstrap.php';
require __DIR__ . '/lib/legacy-credits.php';

const BASE_URL = 'https://www.dewereldvrede.be';

function encodeUrlPath(string $url): string
{
    $parts = parse_url($url);

    if (!isset($parts['scheme'], $parts['host'], $parts['path'])) {
        return $url;
    }

    $path = implode('/', array_map(
        fn (string $segment) => rawurlencode(rawurldecode($segment)),
        explode('/', $parts['path'])
    ));

    return $parts['scheme'] . '://' . $parts['host'] . $path
        . (isset($parts['query']) ? '?' . $parts['query'] : '');
}

function fetchUrl(string $url): string
{
    $url = encodeUrlPath($url);

    $context = stream_context_create([
        'http' => [
            'timeout' => 60,
            'user_agent' => 'DeWereldvrede-Migration/1.0',
        ],
        'ssl' => [
            'verify_peer' => true,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);

    return $body !== false ? $body : '';
}

$forceAll = in_array('--all', $argv ?? [], true);

$kirby = new Kirby();
$kirby->impersonate('kirby');

$work = $kirby->page('work');
if (!$work) {
    fwrite(STDERR, "Work page not found.\n");
    exit(1);
}

$fixed = 0;
$skipped = 0;
$failed = 0;

foreach ($work->childrenAndDrafts() as $page) {
    if ($page->intendedTemplate()->name() !== 'project') {
        continue;
    }

    $slug = $page->slug();
    $shouldRepair = $forceAll || projectCreditsLookBundled($page->credits());

    if (!$shouldRepair) {
        $skipped++;
        continue;
    }

    echo "Repair: {$slug}…\n";

    $path = legacyProjectPathForSlug($slug);
    $html = fetchUrl(BASE_URL . $path);

    if ($html === '') {
        echo "  Failed: could not fetch legacy page\n";
        $failed++;
        continue;
    }

    $credits = extractLegacyCreditsFromProjectHtml($html);

    if ($credits === []) {
        echo "  Skipped: no credits found on legacy page\n";
        $skipped++;
        continue;
    }

    try {
        $page->update(['credits' => $credits]);
        echo '  Fixed: ' . count($credits) . " credit rows\n";
        $fixed++;
    } catch (Throwable $e) {
        echo '  Failed: ' . $e->getMessage() . "\n";
        $failed++;
    }
}

$kirby->cache('pages')->flush();

echo "\nDone.\n";
echo "Fixed: {$fixed}\n";
echo "Skipped: {$skipped}\n";
echo "Failed: {$failed}\n";
