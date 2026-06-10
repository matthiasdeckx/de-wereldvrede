<?php

/**
 * Import creator portraits from https://www.dewereldvrede.be/home-creators
 * Run: php scripts/import-legacy-creator-portraits.php
 */

require dirname(__DIR__) . '/kirby/bootstrap.php';

use Kirby\Toolkit\Str;

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

function decodeText(string $value): string
{
    return html_entity_decode(trim($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function normalizeName(string $value): string
{
    $value = decodeText($value);
    $value = mb_strtolower($value, 'UTF-8');
    $value = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $value) ?? $value;
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;

    return trim($value);
}

function parseLegacyCreators(string $html): array
{
    $creators = [];

    if (!preg_match_all(
        '~<a href="#" class="cast-member">.*?<img class="base" src="([^"]+)"[^>]*>.*?<h3>([^<]*)</h3>~s',
        $html,
        $matches,
        PREG_SET_ORDER
    )) {
        return $creators;
    }

    foreach ($matches as $match) {
        $portraitUrl = decodeText($match[1]);
        $name = decodeText($match[2]);

        if ($name === '' || $portraitUrl === '') {
            continue;
        }

        $creators[] = [
            'name' => $name,
            'portrait_url' => $portraitUrl,
            'key' => normalizeName($name),
        ];
    }

    return $creators;
}

function findLegacyCreator(array $legacyByKey, string $name): ?array
{
    $key = normalizeName($name);

    if (isset($legacyByKey[$key])) {
        return $legacyByKey[$key];
    }

    foreach ($legacyByKey as $legacyKey => $legacy) {
        similar_text($key, $legacyKey, $percent);

        if ($percent >= 90) {
            return $legacy;
        }
    }

    return null;
}

function fileExtensionFromUrl(string $url): string
{
    $path = parse_url($url, PHP_URL_PATH) ?: '';
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? $ext : 'jpg';
}

function portraitFilename(string $name, string $ext): string
{
    return 'de-wereldvrede-creator-portrait-' . Str::slug($name) . '.' . $ext;
}

function downloadImage(string $url, string $target): bool
{
    if ($url === '') {
        return false;
    }

    $data = fetchUrl($url);
    if ($data === '') {
        return false;
    }

    return file_put_contents($target, $data) !== false;
}

function attachPortrait($page, string $url, string $filename): ?object
{
    $page = $page->kirby()->page($page->id());
    $ext = fileExtensionFromUrl($url);
    $tmp = sys_get_temp_dir() . '/dwv-' . uniqid('', true) . '.' . $ext;

    if (!downloadImage($url, $tmp)) {
        return null;
    }

    $existing = $page->file($filename);
    if ($existing) {
        $existing->delete();
        $page = $page->kirby()->page($page->id());
    }

    return $page->createFile([
        'source' => $tmp,
        'filename' => $filename,
        'template' => 'image',
    ], true);
}

// --- run ---

$kirby = new Kirby();
$kirby->impersonate('kirby');

$creatorsPage = $kirby->page('creators');

if (!$creatorsPage) {
    fwrite(STDERR, "Creators page not found.\n");
    exit(1);
}

echo "Fetching team portraits from legacy home-creators page…\n";
$html = fetchUrl(BASE_URL . '/home-creators');

if ($html === '') {
    fwrite(STDERR, "Failed to fetch legacy page.\n");
    exit(1);
}

$legacyCreators = parseLegacyCreators($html);
echo count($legacyCreators) . " portraits parsed from legacy site.\n";

if ($legacyCreators === []) {
    fwrite(STDERR, "No portraits parsed.\n");
    exit(1);
}

$legacyByKey = [];

foreach ($legacyCreators as $legacy) {
    $legacyByKey[$legacy['key']] = $legacy;
}

$existing = $creatorsPage->creators()->toStructure()->values(fn ($item) => $item->content()->data());
$updatedRows = [];
$imported = 0;
$skipped = 0;
$failed = 0;
$unmatched = [];

foreach ($existing as $row) {
    $name = $row['name'] ?? '';
    $legacy = findLegacyCreator($legacyByKey, $name);

    if ($legacy === null) {
        $unmatched[] = $name;
        $updatedRows[] = $row;
        continue;
    }

    $currentPortrait = $row['portrait'] ?? [];
    $hasPortrait = is_array($currentPortrait)
        ? $currentPortrait !== []
        : trim((string) $currentPortrait) !== '';

    if ($hasPortrait) {
        $skipped++;
        $updatedRows[] = $row;
        continue;
    }

    $filename = portraitFilename($name, fileExtensionFromUrl($legacy['portrait_url']));
    echo "  → {$name}\n";

    $file = attachPortrait($creatorsPage, $legacy['portrait_url'], $filename);

    if (!$file) {
        fwrite(STDERR, "    ! Failed to download portrait for {$name}\n");
        $failed++;
        $updatedRows[] = $row;
        continue;
    }

    $row['portrait'] = [(string) $file->uuid()];
    $updatedRows[] = $row;
    $imported++;
}

$creatorsPage->update([
    'creators' => $updatedRows,
]);

$kirby->cache('pages')->flush();

$creatorsPage = $kirby->page('creators');
$filledPortraits = 0;
$stillEmpty = [];

foreach ($creatorsPage->creators()->toStructure() as $item) {
    if ($item->portrait()->isNotEmpty()) {
        $filledPortraits++;
        continue;
    }

    $stillEmpty[] = $item->name()->value();
}

$contentFile = $creatorsPage->root() . '/creators.en.txt';

echo "\nImported {$imported} portrait(s).\n";
echo "Skipped (already set): {$skipped}\n";
echo "Failed: {$failed}\n";
echo "Verified {$filledPortraits}/{$creatorsPage->creators()->toStructure()->count()} portraits in content.\n";
echo "Content file: {$contentFile}\n";

if ($unmatched !== []) {
    echo "\nKirby creators without legacy match (" . count($unmatched) . "):\n";
    foreach ($unmatched as $name) {
        echo "  • {$name}\n";
    }
}

if ($stillEmpty !== []) {
    echo "\nCreators still missing portraits (" . count($stillEmpty) . "):\n";
    foreach ($stillEmpty as $name) {
        echo "  • {$name}\n";
    }
}

if ($failed > 0) {
    exit(1);
}
