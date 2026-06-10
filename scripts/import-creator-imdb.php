<?php

/**
 * Import IMDB profile URLs for creators.
 * Run: php scripts/import-creator-imdb.php
 */

require dirname(__DIR__) . '/kirby/bootstrap.php';

const IMDB_BASE = 'https://www.imdb.com/name/';

/**
 * Curated name → IMDB nm ID map (verified Belgian film/TV collaborators).
 * Omit creators with no IMDB profile.
 */
const IMDB_IDS = [
    'amina hatim' => 'nm10081977',
    'una kreso' => 'nm9418554',
    'leonardo van dijl' => 'nm5938185',
    'nathalie teirlinck' => 'nm2434921',
    'gilles coulier' => 'nm3899315',
    'wannes destoop' => 'nm4193732',
    'flo van deuren' => 'nm8294992',
    'nele vandael' => 'nm10081985',
    'kato de boeck' => 'nm6672115',
    'laura van passel' => 'nm5079004',
    'chingiz karibekov' => 'nm8132457',
    'david williamson' => 'nm3898995',
    'benny vandendriessche' => 'nm5911885',
    'pieter van hees' => 'nm0887068',
    'anouk fortunier' => 'nm5938184',
    'sien versteyhe' => 'nm4147108',
    'ramy moharam fouad' => 'nm12042676',
    'deben van dam' => 'nm4732069',
    'dimitri baronheid' => 'nm7906471',
    'tom dupont' => 'nm7624544',
    'pieter de cnudde' => 'nm11178372',
    'laura vandewynckel' => 'nm6995501',
    'gilles de schryver' => 'nm2141341',
    'dominique van malder' => 'nm1502517',
    'nelson polfliet' => 'nm8298666',
    'hyun lories' => 'nm8046864',
    'anke blondé' => 'nm0089091',
    'anke blonde' => 'nm0089091',
    'raf njotea' => 'nm9123162',
    'lander kennis' => 'nm9658470',
];

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

function imdbUrl(string $nmId): string
{
    return IMDB_BASE . $nmId . '/';
}

function findImdbId(string $name): ?string
{
    $key = normalizeName($name);

    if (isset(IMDB_IDS[$key])) {
        return IMDB_IDS[$key];
    }

    foreach (IMDB_IDS as $mapKey => $nmId) {
        similar_text($key, $mapKey, $percent);

        if ($percent >= 90) {
            return $nmId;
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

$existing = $creatorsPage->creators()->toStructure()->values(fn ($item) => $item->content()->data());
$updatedRows = [];
$imported = 0;
$alreadySet = 0;
$noProfile = [];

foreach ($existing as $row) {
    $name = $row['name'] ?? '';
    $currentLink = trim((string) ($row['external_link'] ?? ''));

    if ($currentLink !== '') {
        $alreadySet++;
        $updatedRows[] = $row;
        continue;
    }

    $nmId = findImdbId($name);

    if ($nmId === null) {
        $noProfile[] = $name;
        $updatedRows[] = $row;
        continue;
    }

    $url = imdbUrl($nmId);
    echo "  → {$name}: {$url}\n";

    $row['external_link'] = $url;

    $label = trim((string) ($row['external_link_label'] ?? ''));
    if ($label === '') {
        $row['external_link_label'] = 'IMDB';
    }

    $updatedRows[] = $row;
    $imported++;
}

$creatorsPage->update([
    'creators' => $updatedRows,
]);

$kirby->cache('pages')->flush();

$creatorsPage = $kirby->page('creators');
$filledLinks = 0;
$stillEmpty = [];

foreach ($creatorsPage->creators()->toStructure() as $item) {
    if ($item->external_link()->isNotEmpty()) {
        $filledLinks++;
        continue;
    }

    $stillEmpty[] = $item->name()->value();
}

$contentFile = $creatorsPage->root() . '/creators.en.txt';

echo "\nImported {$imported} IMDB URL(s).\n";
echo "Already set (skipped): {$alreadySet}\n";
echo "No IMDB profile in map: " . count($noProfile) . "\n";
echo "Verified {$filledLinks}/{$creatorsPage->creators()->toStructure()->count()} external links in content.\n";
echo "Content file: {$contentFile}\n";

if ($noProfile !== []) {
    echo "\nCreators without IMDB profile (" . count($noProfile) . "):\n";
    foreach ($noProfile as $name) {
        echo "  • {$name}\n";
    }
}

if ($stillEmpty !== []) {
    echo "\nCreators still missing external_link (" . count($stillEmpty) . "):\n";
    foreach ($stillEmpty as $name) {
        echo "  • {$name}\n";
    }
}
