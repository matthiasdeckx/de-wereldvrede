<?php

/**
 * One-off import of projects from https://www.dewereldvrede.be/projects
 * Run: php scripts/import-legacy-projects.php
 */

require dirname(__DIR__) . '/kirby/bootstrap.php';
require __DIR__ . '/lib/legacy-credits.php';

use Kirby\Cms\Url;

const BASE_URL = 'https://www.dewereldvrede.be';

const PROJECT_TYPES = ['Film', 'Series', 'Short', 'Other', 'Non-scripted'];
const PROJECT_STATUSES = ['Finished', 'In Development'];

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

function absoluteUrl(string $url): string
{
    if ($url === '') {
        return '';
    }

    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        return $url;
    }

    return BASE_URL . ($url[0] === '/' ? $url : '/' . $url);
}

function drupalFileUrl(string $styledUrl): string
{
    if (preg_match(
        '#/styles/[^/]+/public/([^"?]+)#',
        $styledUrl,
        $match
    )) {
        return BASE_URL . '/sites/default/files/' . urldecode($match[1]);
    }

    return preg_replace('#\?itok=[^/]+$#', '', $styledUrl) ?? $styledUrl;
}

function fileUrlFromDrupalStyle(string $html, string $style): string
{
    if (!preg_match(
        '#styles/' . preg_quote($style, '#') . '/public/([^"\'?\s]+)#',
        $html,
        $match
    )) {
        return '';
    }

    return BASE_URL . '/sites/default/files/' . urldecode($match[1]);
}

function parseListingItems(string $html): array
{
    $items = [];

    if (!preg_match_all(
        '#<div class="work-item">(.*?</div>\s*</div>\s*</div>\s*</div>)#s',
        $html,
        $blocks,
        PREG_SET_ORDER
    )) {
        return $items;
    }

    foreach ($blocks as $block) {
        $chunk = $block[1];

        if (!preg_match('#href="(/projects/[^"]+)"#', $chunk, $pathMatch)) {
            continue;
        }

        $path = $pathMatch[1];
        $title = '';
        $subtitle = '';
        $excerpt = '';
        $status = '';
        $coverUrl = '';

        if (preg_match('#<h2>([^<]+)</h2>#', $chunk, $m)) {
            $title = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (preg_match('#<h3>([^<]+)</h3>#', $chunk, $m)) {
            $subtitle = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (preg_match('#<div class="text">\s*(.*?)\s*</div>#s', $chunk, $m)) {
            $excerpt = html_entity_decode(trim(strip_tags($m[1])), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (preg_match('#<div class="status">Status:\s*([^<]+)</div>#i', $chunk, $m)) {
            $status = trim($m[1]);
        }

        if (preg_match(
            "#background-image:url\\('([^']+)'\\)#",
            $chunk,
            $m
        )) {
            $coverUrl = drupalFileUrl(
                absoluteUrl(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            );
        }

        if ($coverUrl === '' && preg_match(
            '#styles/projects_overview/public/([^"?]+)#',
            $chunk,
            $imageMatch
        )) {
            $coverUrl = BASE_URL . '/sites/default/files/' . urldecode($imageMatch[1]);
        }

        $items[$path] = [
            'path' => $path,
            'title' => $title,
            'subtitle' => $subtitle,
            'excerpt' => $excerpt,
            'status' => $status,
            'cover_url' => $coverUrl,
            'available_on' => parseAvailableOnFromHtml($chunk),
        ];
    }

    return $items;
}

function parseAvailableOnFromHtml(string $html): array
{
    $items = [];

    if (!preg_match('#Where to watch:?(.*?)(?:</p>|$)#is', $html, $section)) {
        return $items;
    }

    if (!preg_match_all(
        '#<a[^>]+href="([^"]+)"[^>]*>(?:<img[^>]+alt="([^"]*)")?#i',
        $section[1],
        $matches,
        PREG_SET_ORDER
    )) {
        return $items;
    }

    foreach ($matches as $match) {
        $url = absoluteUrl(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $medium = trim(html_entity_decode($match[2] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($medium === '') {
            $host = parse_url($url, PHP_URL_HOST) ?: '';
            $medium = $host !== '' ? preg_replace('#^www\.#', '', $host) : 'Link';
        }

        $items[] = [
            'medium' => $medium,
            'url' => $url,
        ];
    }

    return $items;
}

function collectAllProjectPaths(): array
{
    $paths = [];
    $queue = [];

    $listingHtml = fetchUrl(BASE_URL . '/projects');
    foreach (parseListingItems($listingHtml) as $item) {
        $paths[$item['path']] = true;
        $queue[] = $item['path'];
    }

    if (preg_match_all('#href="(/projects/[^"]+)"#', $listingHtml, $matches)) {
        foreach ($matches[1] as $path) {
            if (!isset($paths[$path])) {
                $paths[$path] = true;
                $queue[] = $path;
            }
        }
    }

    $seen = [];

    while ($queue !== []) {
        $path = array_shift($queue);
        if (isset($seen[$path])) {
            continue;
        }
        $seen[$path] = true;

        $html = fetchUrl(BASE_URL . $path);
        if ($html === '') {
            continue;
        }

        if (preg_match_all('#href="(/projects/[^"]+)"#', $html, $matches)) {
            foreach ($matches[1] as $found) {
                $paths[$found] = true;
                if (!isset($seen[$found])) {
                    $queue[] = $found;
                }
            }
        }
    }

    $list = array_keys($paths);
    sort($list);

    return $list;
}

function htmlToPlainText(string $html): string
{
    $html = preg_replace('#<br\s*/?>#i', "\n", $html);
    $html = preg_replace('#</p>#i', "\n\n", $html);
    $html = preg_replace('#</h[1-6]>#i', "\n\n", $html);
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace("/\r\n|\r/", "\n", $text);
    $text = preg_replace("/[ \t]+\n/", "\n", $text);
    $text = preg_replace("/\n{3,}/", "\n\n", $text);

    return trim($text);
}

function splitBilingualText(string $html): array
{
    $parts = preg_split('#<p>\s*--\s*</p>#i', $html, 2);
    $english = trim($parts[0] ?? '');
    $other = trim($parts[1] ?? '');

    return [
        'english_html' => $english,
        'other_html' => $other,
        'english_text' => htmlToPlainText($english),
        'other_text' => htmlToPlainText($other),
    ];
}

function extractLength(string $value): string
{
    if (preg_match("#\d+\s*x\s*[\d']+#i", $value, $match)) {
        return trim($match[0]);
    }

    if (preg_match("#\d+x'[\d]+#i", $value, $match)) {
        return trim($match[0]);
    }

    return '';
}

function extractWritersDirectors(string $subtitle): string
{
    if (preg_match('#\bby\s+(.+?)\.?$#iu', $subtitle, $match)) {
        return trim(rtrim($match[1], '.'));
    }

    return '';
}

function inferProjectType(string $subtitle, string $synopsis = ''): string
{
    $haystack = strtolower($subtitle . ' ' . $synopsis);

    if (preg_match('#\b(short film|short form|short by|\bshort\b)#', $haystack)) {
        return 'Short';
    }

    if (preg_match('#\b(series|serie|episodes|x\s*\d+\s*\'?)#', $haystack)) {
        return 'Series';
    }

    if (preg_match('#\b(feature film|langspeelfilm|fiction series)#', $haystack)) {
        return str_contains($haystack, 'series') ? 'Series' : 'Film';
    }

    if (preg_match('#\b(documentary|non-scripted|music video|reportage)#', $haystack)) {
        return str_contains($haystack, 'documentary') || str_contains($haystack, 'reportage')
            ? 'Non-scripted'
            : 'Other';
    }

    if (preg_match('#\bfilm\b#', $haystack)) {
        return 'Film';
    }

    return 'Other';
}

function mapProjectStatus(string $legacyStatus): string
{
    $legacyStatus = strtolower(trim($legacyStatus));

    return match (true) {
        str_contains($legacyStatus, 'development') => 'In Development',
        str_contains($legacyStatus, 'post-production') => 'In Development',
        str_contains($legacyStatus, 'released') => 'Finished',
        default => '',
    };
}

function parseQuotes(string $html): array
{
    $quotes = [];

    if (!preg_match(
        '#<section id="quotes-slider"[^>]*>(.*?)</section>#s',
        $html,
        $section
    )) {
        return $quotes;
    }

    if (!preg_match_all(
        '#<div class="quote-item">\s*<p>(.*?)</p>\s*<div class="source">([^<]*)</div>#s',
        $section[1],
        $matches,
        PREG_SET_ORDER
    )) {
        return $quotes;
    }

    foreach ($matches as $match) {
        $quote = htmlToPlainText($match[1]);
        $source = html_entity_decode(trim($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($quote === '') {
            continue;
        }

        $quotes[] = [
            'quote' => $quote,
            'source' => $source,
        ];
    }

    return $quotes;
}

function parseGalleryUrls(string $html): array
{
    $urls = [];

    if (!preg_match(
        '#<section class="stills">(.*?)</section>#s',
        $html,
        $section
    )) {
        return $urls;
    }

    if (!preg_match_all(
        '#styles/still/public/([^"?]+)#',
        $section[1],
        $matches
    )) {
        return $urls;
    }

    foreach (array_unique($matches[1]) as $file) {
        $urls[] = BASE_URL . '/sites/default/files/' . urldecode($file);
    }

    return $urls;
}

function parseTrailerVimeo(string $html): string
{
    if (preg_match(
        '#player\.vimeo\.com/(?:external/(\d+)|progressive_redirect/playback/(\d+))#',
        $html,
        $match
    )) {
        $id = $match[1] !== '' ? $match[1] : $match[2];

        return 'https://vimeo.com/' . $id;
    }

    if (preg_match('#vimeo\.com/(\d+)#', $html, $match)) {
        return 'https://vimeo.com/' . $match[1];
    }

    return '';
}

function extractYear(string $html, string $synopsisText): ?int
{
    if (preg_match_all('#<span class="year">(\d{4})</span>#', $html, $matches)) {
        $years = array_map('intval', $matches[1]);

        return max($years);
    }

    if (preg_match('#\bRELEASE\b[^0-9]{0,40}(20\d{2})#i', $synopsisText, $match)) {
        return (int)$match[1];
    }

    if (preg_match('#\b(20\d{2})\b#', $synopsisText, $match)) {
        return (int)$match[1];
    }

    return null;
}

function parseProjectDetail(string $path, array $listing = []): array
{
    $html = fetchUrl(BASE_URL . $path);

    $result = [
        'path' => $path,
        'title' => $listing['title'] ?? '',
        'subtitle' => $listing['subtitle'] ?? '',
        'status' => $listing['status'] ?? '',
        'excerpt' => $listing['excerpt'] ?? '',
        'cover_url' => $listing['cover_url'] ?? '',
        'available_on' => $listing['available_on'] ?? [],
        'hero_url' => '',
        'synopsis' => '',
        'intro' => '',
        'length' => '',
        'writers_directors' => '',
        'year' => null,
        'project_type' => [],
        'project_status' => [],
        'credits' => [],
        'press' => [],
        'trailer_vimeo' => '',
        'quotes' => [],
        'gallery_urls' => [],
        'title_logo_url' => '',
    ];

    if ($html === '') {
        return $result;
    }

    if (preg_match(
        "#detail-hero-content-wrap[^>]*style=\"background-image:url\\('([^']+)'\\)#",
        $html,
        $match
    )) {
        $hero = absoluteUrl(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $result['hero_url'] = drupalFileUrl($hero);
    }

    if (preg_match('#<section id="detail-hero">(.*?)</section>#s', $html, $heroSection)) {
        if (preg_match('#<h2>([^<]+)</h2>#', $heroSection[1], $match)) {
            $result['title'] = html_entity_decode(trim($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (preg_match('#<h3>([^<]*)</h3>#', $heroSection[1], $match)) {
            $result['subtitle'] = html_entity_decode(trim($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }

    if ($result['cover_url'] === '' && $result['hero_url'] !== '') {
        $result['cover_url'] = $result['hero_url'];
    }

    $result['length'] = extractLength($result['subtitle']);
    $result['writers_directors'] = extractWritersDirectors($result['subtitle']);
    $result['trailer_vimeo'] = parseTrailerVimeo($html);
    $result['quotes'] = parseQuotes($html);
    $result['gallery_urls'] = parseGalleryUrls($html);

    if (preg_match(
        '#<section class="column-content">(.*?)</section>#s',
        $html,
        $section
    )) {
        if (preg_match(
            '#<div class="grid-75[^"]*">\s*<div class="text">\s*(.*?)\s*</div>#s',
            $section[1],
            $main
        )) {
            $split = splitBilingualText($main[1]);
            $result['synopsis'] = $split['english_text'] !== ''
                ? $split['english_text']
                : $split['other_text'];
        }

        if (preg_match(
            '#<div class="text small-column">\s*(.*?)\s*</div>#s',
            $section[1],
            $side
        )) {
            $result['credits'] = parseLegacyCredits($side[1]);
        }
    }

    if ($result['synopsis'] === '' && $result['excerpt'] !== '') {
        $result['synopsis'] = $result['excerpt'];
    }

    $paragraphs = preg_split("/\n{2,}/", $result['synopsis']) ?: [];
    $result['intro'] = trim($paragraphs[0] ?? '');

    $type = inferProjectType($result['subtitle'], $result['synopsis']);
    if (in_array($type, PROJECT_TYPES, true)) {
        $result['project_type'] = [$type];
    }

    $mappedStatus = mapProjectStatus($result['status']);
    if ($mappedStatus !== '' && in_array($mappedStatus, PROJECT_STATUSES, true)) {
        $result['project_status'] = [$mappedStatus];
    }

    $year = extractYear($html, $result['synopsis']);
    if ($year !== null) {
        $result['year'] = $year;
    }

    if ($result['available_on'] === []) {
        $result['available_on'] = parseAvailableOnFromHtml($html);
    }

    return $result;
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

function fileExtensionFromUrl(string $url): string
{
    $path = parse_url($url, PHP_URL_PATH) ?: '';
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true) ? $ext : 'jpg';
}

function freshPage($page)
{
    return $page->kirby()->page($page->id());
}

function attachFile($page, string $url, string $filename, string $field = ''): ?object
{
    if ($url === '') {
        return null;
    }

    $page = freshPage($page);
    $ext = fileExtensionFromUrl($url);
    $tmp = sys_get_temp_dir() . '/dwv-' . uniqid('', true) . '.' . $ext;

    if (!downloadImage($url, $tmp)) {
        return null;
    }

    $file = $page->createFile([
        'source' => $tmp,
        'filename' => $filename,
        'template' => 'image',
    ], true);

    if ($field !== '') {
        $page->update([
            $field => (string)$file->uuid(),
        ]);
    }

    return $file;
}

function attachCoverHeroAndLogo($page, array $data): void
{
    $page = freshPage($page);
    $updates = [];

    if ($data['cover_url'] !== '' && $page->cover()->isEmpty()) {
        $cover = attachFile($page, $data['cover_url'], 'cover.' . fileExtensionFromUrl($data['cover_url']));
        if ($cover) {
            $updates['cover'] = (string)$cover->uuid();
            $page = freshPage($page);
        }
    }

    $heroUrl = $data['hero_url'] !== '' ? $data['hero_url'] : $data['cover_url'];
    if ($heroUrl !== '' && $page->hero_image()->isEmpty()) {
        $hero = attachFile($page, $heroUrl, 'hero.' . fileExtensionFromUrl($heroUrl));
        if ($hero) {
            $updates['hero_image'] = (string)$hero->uuid();
        }
    }

    if ($data['title_logo_url'] !== '' && $page->title_logo()->isEmpty()) {
        $logo = attachFile(
            $page,
            $data['title_logo_url'],
            'title-logo.' . fileExtensionFromUrl($data['title_logo_url'])
        );
        if ($logo) {
            $updates['title_logo'] = (string)$logo->uuid();
            $updates['title_type'] = 'logo';
        }
    }

    if ($updates !== []) {
        freshPage($page)->update($updates);
    }
}

function attachGallery($page, array $urls): int
{
    if ($urls === []) {
        return 0;
    }

    $page = freshPage($page);
    if ($page->gallery()->isNotEmpty()) {
        return 0;
    }

    $uuids = [];
    $index = 1;

    foreach ($urls as $url) {
        $file = attachFile(
            $page,
            $url,
            'gallery-' . str_pad((string)$index, 2, '0', STR_PAD_LEFT) . '.' . fileExtensionFromUrl($url)
        );
        $page = freshPage($page);

        if ($file) {
            $uuids[] = (string)$file->uuid();
            $index++;
        }
    }

    if ($uuids !== []) {
        $page->update([
            'gallery' => $uuids,
        ]);
    }

    return count($uuids);
}

function buildPageContent(array $data): array
{
    $content = [
        'title' => $data['title'],
        'subtitle' => $data['subtitle'],
        'writers_directors' => $data['writers_directors'],
        'intro' => $data['intro'],
        'length' => $data['length'],
        'synopsis' => $data['synopsis'],
        'page_theme' => 'dark',
        'trailer_source' => $data['trailer_vimeo'] !== '' ? 'vimeo' : 'none',
        'trailer_vimeo' => $data['trailer_vimeo'],
    ];

    if (!empty($data['project_type'])) {
        $content['project_type'] = implode(', ', $data['project_type']);
    }

    if (!empty($data['project_status'])) {
        $content['project_status'] = implode(', ', $data['project_status']);
    }

    if ($data['year'] !== null) {
        $content['year'] = $data['year'];
    }

    if (!empty($data['credits'])) {
        $content['credits'] = $data['credits'];
    }

    if (!empty($data['available_on'])) {
        $content['available_on'] = $data['available_on'];
    }

    if (!empty($data['quotes'])) {
        $first = array_shift($data['quotes']);
        $content['featured_quote_text'] = $first['quote'];
        $content['featured_quote_source'] = $first['source'];

        if (!empty($data['quotes'])) {
            $content['featured_quotes'] = array_map(
                fn (array $item) => [
                    'quote' => $item['quote'],
                    'source' => $item['source'],
                    'stars' => '5',
                ],
                $data['quotes']
            );
        }
    }

    return $content;
}

// --- run ---

$kirby = new Kirby();
$kirby->impersonate('kirby');

$work = $kirby->page('work');
if (!$work) {
    fwrite(STDERR, "Work page not found.\n");
    exit(1);
}

echo "Discovering projects on legacy site…\n";
$paths = collectAllProjectPaths();
echo count($paths) . " project URLs discovered.\n";

$listingHtml = fetchUrl(BASE_URL . '/projects');
$listingByPath = parseListingItems($listingHtml);

$imported = 0;
$skipped = 0;
$failed = 0;
$importedSlugs = [];
$failedPaths = [];

foreach ($paths as $path) {
    $slug = Url::slug(basename($path));
    $listing = $listingByPath[$path] ?? [];

    if ($kirby->page('work/' . $slug)) {
        echo "Skip (exists): {$slug}\n";
        $skipped++;
        continue;
    }

    echo "Import: {$path}…\n";

    try {
        $data = parseProjectDetail($path, $listing);

        if ($data['title'] === '') {
            echo "  Failed: no title\n";
            $failed++;
            $failedPaths[] = $path;
            continue;
        }

        $page = $work->createChild([
            'slug' => $slug,
            'template' => 'project',
            'draft' => false,
            'content' => buildPageContent($data),
        ]);

        if ($page->isDraft()) {
            $page->publish();
        }

        attachCoverHeroAndLogo($page, $data);
        attachGallery($page, $data['gallery_urls']);

        $imported++;
        $importedSlugs[] = $slug;
    } catch (Throwable $e) {
        echo "  Failed: {$e->getMessage()}\n";
        $failed++;
        $failedPaths[] = $path;
    }
}

echo "Backfilling empty type/status on existing projects…\n";
$backfilled = 0;

foreach ($paths as $path) {
    $slug = Url::slug(basename($path));
    $page = $kirby->page('work/' . $slug);

    if (!$page) {
        continue;
    }

    $listing = $listingByPath[$path] ?? [];
    $data = parseProjectDetail($path, $listing);
    $updates = [];

    if ($page->project_type()->isEmpty() && !empty($data['project_type'])) {
        $updates['project_type'] = implode(', ', $data['project_type']);
    }

    if ($page->project_status()->isEmpty() && !empty($data['project_status'])) {
        $updates['project_status'] = implode(', ', $data['project_status']);
    }

    if ($page->year()->isEmpty() && $data['year'] !== null) {
        $updates['year'] = $data['year'];
    }

    if (projectCreditsLookBundled($page->credits()) && !empty($data['credits'])) {
        $updates['credits'] = $data['credits'];
    }

    if ($updates !== []) {
        $page->update($updates);
        echo "  Meta: {$slug}\n";
        $backfilled++;
    }
}

$kirby->cache('pages')->flush();

echo "\nDone.\n";
echo "Backfilled metadata: {$backfilled}\n";
echo "Imported: {$imported}\n";
echo "Skipped (existing slug): {$skipped}\n";
echo "Failed: {$failed}\n";

if ($importedSlugs !== []) {
    echo "Sample slugs: " . implode(', ', array_slice($importedSlugs, 0, 8));
    if (count($importedSlugs) > 8) {
        echo ' …';
    }
    echo "\n";
}

if ($failedPaths !== []) {
    echo "Failed paths: " . implode(', ', $failedPaths) . "\n";
}
