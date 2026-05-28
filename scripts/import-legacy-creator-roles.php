<?php

/**
 * Import creator roles from https://www.dewereldvrede.be/home-creators
 * Run: php scripts/import-legacy-creator-roles.php
 */

require dirname(__DIR__) . '/kirby/bootstrap.php';

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

function titleCaseSegment(string $segment): string
{
    $segment = mb_strtolower(trim($segment), 'UTF-8');

    return preg_replace_callback(
        '/[\p{L}\']+/u',
        fn (array $match) => mb_convert_case($match[0], MB_CASE_TITLE, 'UTF-8'),
        $segment
    ) ?? $segment;
}

function titleCaseRole(string $role): string
{
    $role = decodeText($role);
    $role = preg_replace('/\s*\([^)]*\)/u', '', $role) ?? $role;
    $role = trim($role);

    if ($role === '') {
        return '';
    }

    $slashParts = preg_split('#\s*/\s*#u', $role) ?: [$role];
    $casedSlashParts = [];

    foreach ($slashParts as $slashPart) {
        $ampParts = preg_split('#\s*&\s*#u', $slashPart) ?: [$slashPart];
        $casedAmpParts = array_map('titleCaseSegment', $ampParts);
        $casedSlashParts[] = implode(' & ', $casedAmpParts);
    }

    return implode(' / ', $casedSlashParts);
}

function parseLegacyCreators(string $html): array
{
    $creators = [];

    if (!preg_match_all(
        '~<a href="#" class="cast-member">.*?<h3>([^<]*)</h3>\s*<h4>([^<]*)</h4>~s',
        $html,
        $matches,
        PREG_SET_ORDER
    )) {
        return $creators;
    }

    foreach ($matches as $match) {
        $name = decodeText($match[1]);
        $role = titleCaseRole($match[2]);

        if ($name === '') {
            continue;
        }

        $creators[] = [
            'name' => $name,
            'role' => $role,
            'key' => normalizeName($name),
        ];
    }

    return $creators;
}

function findLegacyRole(array $legacyByKey, string $name): ?array
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

// --- run ---

$kirby = new Kirby();
$kirby->impersonate('kirby');

$creatorsPage = $kirby->page('creators');

if (!$creatorsPage) {
    fwrite(STDERR, "Creators page not found.\n");
    exit(1);
}

echo "Fetching creators from legacy home-creators page…\n";
$html = fetchUrl(BASE_URL . '/home-creators');

if ($html === '') {
    fwrite(STDERR, "Failed to fetch legacy page.\n");
    exit(1);
}

$legacyCreators = parseLegacyCreators($html);
echo count($legacyCreators) . " creators parsed from legacy site.\n";

if ($legacyCreators === []) {
    fwrite(STDERR, "No creators parsed.\n");
    exit(1);
}

$legacyByKey = [];

foreach ($legacyCreators as $legacy) {
    $legacyByKey[$legacy['key']] = $legacy;
}

$existing = $creatorsPage->creators()->toStructure()->values(fn ($item) => $item->content()->data());
$updatedRows = [];
$updated = 0;
$alreadySet = 0;
$unmatched = [];

foreach ($existing as $row) {
    $name = $row['name'] ?? '';
    $legacy = findLegacyRole($legacyByKey, $name);

    if ($legacy === null) {
        $unmatched[] = $name;
        $updatedRows[] = $row;
        continue;
    }

    $currentRole = trim($row['role'] ?? '');

    if ($currentRole !== '' && $currentRole !== $legacy['role']) {
        echo "  ! {$name}: keeping existing role \"{$currentRole}\" (legacy: \"{$legacy['role']}\")\n";
        $alreadySet++;
        $updatedRows[] = $row;
        continue;
    }

    if ($currentRole === $legacy['role']) {
        $alreadySet++;
        $updatedRows[] = $row;
        continue;
    }

    $row['role'] = $legacy['role'];
    $updatedRows[] = $row;
    $updated++;
}

$legacyOnly = [];

foreach ($legacyCreators as $legacy) {
    $found = false;

    foreach ($existing as $row) {
        if (findLegacyRole([$legacy['key'] => $legacy], $row['name'] ?? '') !== null) {
            $found = true;
            break;
        }
    }

    if (!$found) {
        $legacyOnly[] = $legacy['name'] . ' (' . $legacy['role'] . ')';
    }
}

$creatorsPage->update([
    'creators' => $updatedRows,
]);

$kirby->cache('pages')->flush();

// Re-read from disk so we verify what Panel will show
$creatorsPage = $kirby->page('creators');
$verifyRows = $creatorsPage->creators()->toStructure();
$filledRoles = 0;
$stillEmpty = [];

foreach ($verifyRows as $item) {
    $role = trim($item->role()->value());

    if ($role !== '') {
        $filledRoles++;
        continue;
    }

    $stillEmpty[] = $item->name()->value();
}

$contentFile = $creatorsPage->root() . '/creators.en.txt';

echo "\nUpdated {$updated} creator role(s).\n";
echo "Already set or unchanged: {$alreadySet}\n";
echo "Verified {$filledRoles}/{$verifyRows->count()} roles in content (Panel reads this file).\n";
echo "Content file: {$contentFile}\n";

if ($stillEmpty !== []) {
    fwrite(STDERR, "\nRoles still empty after import (" . count($stillEmpty) . "):\n");
    foreach ($stillEmpty as $name) {
        fwrite(STDERR, "  • {$name}\n");
    }
    exit(1);
}

if ($unmatched !== []) {
    echo "\nKirby creators without legacy match (" . count($unmatched) . "):\n";
    foreach ($unmatched as $name) {
        echo "  • {$name}\n";
    }
}

if ($legacyOnly !== []) {
    echo "\nLegacy creators not in Kirby (" . count($legacyOnly) . "):\n";
    foreach ($legacyOnly as $entry) {
        echo "  • {$entry}\n";
    }
}

echo "\nSample imported roles:\n";
$samples = array_slice(
    array_values(array_filter($updatedRows, fn ($row) => trim($row['role'] ?? '') !== '')),
    0,
    8
);

foreach ($samples as $row) {
    echo "  • {$row['name']}: {$row['role']}\n";
}
