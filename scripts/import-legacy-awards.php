<?php

/**
 * Import awards & nominations from https://www.dewereldvrede.be/company-profile
 * Run: php scripts/import-legacy-awards.php
 */

require dirname(__DIR__) . '/kirby/bootstrap.php';

const BASE_URL = 'https://www.dewereldvrede.be';

const PROJECT_ALIASES = [
    'bevergem' => 'bevergem',
    'the natives' => 'bevergem',
    'bevergem/the natives' => 'bevergem',
    'cargo' => 'cargo',
    'guest' => 'guest',
    'ijsland' => 'ijsland',
    'paroles' => 'paroles',
    'stephanie' => 'stephanie',
    'mont blanc' => '',
    'albatros' => 'albatros',
    'lockdown' => 'lockdown',
    'roomies' => 'roomies',
    'julie keeps quiet' => 'julie-zwijgt',
    'julie zwijgt' => 'julie-zwijgt',
    'holy rosita' => 'holy-rosita',
    'ada' => 'ada',
];

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

function normalizeKey(string $value): string
{
    $value = decodeText($value);
    $value = mb_strtolower($value, 'UTF-8');
    $value = preg_replace('/[^\p{L}\p{N}\s\/]/u', ' ', $value) ?? $value;
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;

    return trim($value);
}

function parseLegacyAwards(string $html): array
{
    $awards = [];

    if (!preg_match(
        '#<section class="awards">(.*?)</section>#s',
        $html,
        $section
    )) {
        return $awards;
    }

    if (!preg_match_all(
        '#<motion class="award"[^>]*>.*?<span class="from">([^<]*)</span>\s*<span class="year">(\d{4})</span>#s',
        $section[1],
        $matches,
        PREG_SET_ORDER
    )) {
        // Legacy markup uses div.award, not motion
        preg_match_all(
            '#<motion class="award"[^>]*>.*?<span class="from">([^<]*)</span>\s*<span class="year">(\d{4})</span>#s',
            $section[1],
            $matches,
            PREG_SET_ORDER
        );
    }

    if ($matches === []) {
        preg_match_all(
            '#<div class="award"[^>]*>.*?<span class="from">([^<]*)</span>\s*<span class="year">(\d{4})</span>#s',
            $section[1],
            $matches,
            PREG_SET_ORDER
        );
    }

    foreach ($matches as $match) {
        $raw = decodeText($match[1]);
        $year = (int)$match[2];
        $parsed = parseAwardLine($raw);

        if ($parsed === null) {
            echo "Skip (unparsed): {$raw}\n";
            continue;
        }

        $awards[] = [
            'title' => $parsed['title'],
            'project' => $parsed['project'],
            'year' => $year,
        ];
    }

    return $awards;
}

function parseAwardLine(string $raw): ?array
{
    $raw = ltrim($raw, "- \t");

    if ($raw === '') {
        return null;
    }

    $parts = preg_split('/\s+-\s+/', $raw, 2);

    if (!is_array($parts) || count($parts) < 2) {
        return null;
    }

    return [
        'project' => trim($parts[0]),
        'title' => trim($parts[1]),
    ];
}

function buildProjectIndex($workPage): array
{
    $index = [
        'by_slug' => [],
        'by_title' => [],
    ];

    foreach ($workPage->children() as $page) {
        $slug = preg_replace('/^\d+_/', '', $page->slug()) ?? $page->slug();
        $index['by_slug'][$slug] = $page;
        $index['by_title'][normalizeKey($page->title()->value())] = $page;
    }

    return $index;
}

function resolveProjectPage(string $projectName, array $index): ?object
{
    $normalized = normalizeKey($projectName);

    if ($normalized === '') {
        return null;
    }

    if (array_key_exists($normalized, PROJECT_ALIASES)) {
        $slug = PROJECT_ALIASES[$normalized];
        if ($slug === '') {
            return null;
        }

        return $index['by_slug'][$slug] ?? null;
    }

    if (isset($index['by_title'][$normalized])) {
        return $index['by_title'][$normalized];
    }

  foreach (preg_split('#/\s*#', $normalized) ?: [] as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }

        if (array_key_exists($part, PROJECT_ALIASES)) {
            $slug = PROJECT_ALIASES[$part];
            if ($slug !== '' && isset($index['by_slug'][$slug])) {
                return $index['by_slug'][$slug];
            }
        }

        if (isset($index['by_title'][$part])) {
            return $index['by_title'][$part];
        }

        if (isset($index['by_slug'][$part])) {
            return $index['by_slug'][$part];
        }
    }

    $best = null;
    $bestScore = 0.0;

    foreach ($index['by_title'] as $title => $page) {
        similar_text($normalized, $title, $percent);

        if ($percent > $bestScore) {
            $bestScore = $percent;
            $best = $page;
        }
    }

    return $bestScore >= 85.0 ? $best : null;
}

function buildAwardRows(array $legacyAwards, array $projectIndex): array
{
    $rows = [];

    foreach ($legacyAwards as $award) {
        $projectPage = resolveProjectPage($award['project'], $projectIndex);
        $row = [
            'title' => $award['title'],
            'year' => $award['year'],
            'project' => '',
            'project_page' => [],
        ];

        if ($projectPage) {
            $row['project_page'] = [(string)$projectPage->uuid()];
        } else {
            $row['project'] = $award['project'];
        }

        $rows[] = $row;
    }

    return $rows;
}

// --- run ---

$kirby = new Kirby();
$kirby->impersonate('kirby');

$about = $kirby->page('about');
$work = $kirby->page('work');

if (!$about) {
    fwrite(STDERR, "About page not found.\n");
    exit(1);
}

if (!$work) {
    fwrite(STDERR, "Work page not found.\n");
    exit(1);
}

echo "Fetching awards from legacy company profile…\n";
$html = fetchUrl(BASE_URL . '/company-profile');

if ($html === '') {
    fwrite(STDERR, "Failed to fetch legacy page.\n");
    exit(1);
}

$legacyAwards = parseLegacyAwards($html);
echo count($legacyAwards) . " awards parsed from legacy site.\n";

if ($legacyAwards === []) {
    fwrite(STDERR, "No awards parsed.\n");
    exit(1);
}

$projectIndex = buildProjectIndex($work);
$rows = buildAwardRows($legacyAwards, $projectIndex);

$linked = 0;
$textOnly = 0;

foreach ($rows as $row) {
    if (!empty($row['project_page'])) {
        $linked++;
    } else {
        $textOnly++;
    }
}

$about->update([
    'awards' => $rows,
]);

$kirby->cache('pages')->flush();

echo "Updated about page awards structure.\n";
echo "Imported: " . count($rows) . " rows\n";
echo "Linked to work projects: {$linked}\n";
echo "Text-only project names: {$textOnly}\n\n";

echo "Sample rows:\n";
foreach (array_slice($rows, 0, 5) as $row) {
    $project = !empty($row['project_page'])
        ? '→ ' . ($kirby->page($row['project_page'][0])?->title()->value() ?? $row['project_page'][0])
        : $row['project'];

    echo "  • {$row['title']} | {$project} | {$row['year']}\n";
}

echo "\nSample linked rows:\n";
$linkedSamples = array_values(array_filter($rows, fn ($row) => !empty($row['project_page'])));
foreach (array_slice($linkedSamples, 0, 3) as $row) {
    $page = $kirby->page($row['project_page'][0]);
    echo "  • {$row['title']} → {$page?->title()->value()} ({$page?->slug()}) | {$row['year']}\n";
}

echo "\nSample text-only rows:\n";
$textSamples = array_values(array_filter($rows, fn ($row) => empty($row['project_page'])));
foreach (array_slice($textSamples, 0, 3) as $row) {
    echo "  • {$row['title']} | {$row['project']} | {$row['year']}\n";
}
