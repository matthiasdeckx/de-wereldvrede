<?php

/**
 * One-off import of news articles from https://www.dewereldvrede.be/news
 * Run: php scripts/import-legacy-news.php
 */

require dirname(__DIR__) . '/kirby/bootstrap.php';

use Kirby\Cms\Url;

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
            'timeout' => 30,
            'user_agent' => 'DeWereldvrede-Migration/1.0',
        ],
        'ssl' => [
            'verify_peer' => true,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);

    return $body !== false ? $body : '';
}

function parseListingItems(string $html): array
{
    $items = [];

    if (!preg_match_all(
        '#<a class="item" href="(/news/[^"]+)">\s*<div class="item-wrap">.*?<span class="date"><span>([^<]+)</span></span>.*?<h2>([^<]+)</h2>.*?<div class="text">\s*(.*?)\s*</div>#s',
        $html,
        $matches,
        PREG_SET_ORDER
    )) {
        return $items;
    }

    foreach ($matches as $match) {
        $thumbUrl = '';
        if (preg_match(
            '#styles/news_thumb/public/([^"?]+)#',
            $match[0],
            $imageMatch
        )) {
            $thumbUrl = BASE_URL . '/sites/default/files/' . urldecode($imageMatch[1]);
        }

        $items[$match[1]] = [
            'path' => $match[1],
            'date' => trim($match[2]),
            'title' => html_entity_decode(trim($match[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'excerpt' => html_entity_decode(trim(strip_tags($match[4])), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'cover_url' => $thumbUrl,
        ];
    }

    return array_values($items);
}

function collectAllListingItems(): array
{
    $byPath = [];

    $listingHtml = fetchUrl(BASE_URL . '/news');
    foreach (parseListingItems($listingHtml) as $item) {
        $byPath[$item['path']] = $item;
    }

    $page = 1;
    while (true) {
        $json = fetchUrl(BASE_URL . '/ajax/news/page/' . $page . '/1');
        $payload = json_decode($json, true);

        if (!is_array($payload) || empty($payload['data'])) {
            break;
        }

        foreach (parseListingItems($payload['data']) as $item) {
            $byPath[$item['path']] = $item;
        }

        if (empty($payload['more'])) {
            break;
        }

        $page++;
    }

    return array_values($byPath);
}

function parseEuropeanDate(string $value): string
{
    if (!preg_match('#^(\d{2})\.(\d{2})\.(\d{2})$#', trim($value), $m)) {
        return '';
    }

    $year = (int)$m[3];
    $year += $year < 50 ? 2000 : 1900;

    return sprintf('%04d-%02d-%02d', $year, (int)$m[2], (int)$m[1]);
}

function parseArticleDetail(string $path): array
{
    $html = fetchUrl(BASE_URL . $path);
    $result = [
        'body_html' => '',
        'body_text' => '',
        'info' => [],
    ];

    if (!preg_match(
        '#<div class="column-content">.*?<div class="text">\s*<div class="text">\s*(.*?)\s*</div>\s*</div>#s',
        $html,
        $match
    )) {
        return $result;
    }

    $bodyHtml = trim($match[1]);
    $result['body_html'] = $bodyHtml;
    $result['body_text'] = htmlToPlainText($bodyHtml);
    $result['info'] = extractInfoFromHtml($bodyHtml);

    return $result;
}

function htmlToPlainText(string $html): string
{
    $html = preg_replace('#<br\s*/?>#i', "\n", $html);
    $html = preg_replace('#</p>#i', "\n\n", $html);
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace("/\r\n|\r/", "\n", $text);
    $text = preg_replace("/\n{3,}/", "\n\n", $text);

    return trim($text);
}

function extractInfoFromHtml(string $html): array
{
    $info = [];
    $plain = htmlToPlainText($html);

    if (preg_match('/deadline[^:]*:?\s*([^\n]+)/i', $plain, $match)) {
        $info[] = [
            'label' => 'Deadline',
            'value' => trim($match[1]),
        ];
    }

    if (preg_match_all('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $plain, $emails)) {
        $email = $emails[0][0];
        $info[] = [
            'label' => 'Questions',
            'value' => $email,
        ];
    }

    return $info;
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

    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? $ext : 'jpg';
}

function freshPage($page)
{
    return $page->kirby()->page($page->id());
}

function attachCoverAndHero($page, string $coverUrl): void
{
    if ($coverUrl === '') {
        return;
    }

    $page = freshPage($page);

    $ext = fileExtensionFromUrl($coverUrl);
    $tmpCover = sys_get_temp_dir() . '/dwv-cover-' . uniqid('', true) . '.' . $ext;

    if (!downloadImage($coverUrl, $tmpCover)) {
        return;
    }

    $coverFile = $page->createFile([
        'source' => $tmpCover,
        'filename' => 'cover.' . $ext,
        'template' => 'image',
    ], true);

    $tmpHero = sys_get_temp_dir() . '/dwv-hero-' . uniqid('', true) . '.' . $ext;

    if (!downloadImage($coverUrl, $tmpHero)) {
        $page->update([
            'cover_image' => (string)$coverFile->uuid(),
        ]);
        return;
    }

    $heroFile = $page->createFile([
        'source' => $tmpHero,
        'filename' => 'hero.' . $ext,
        'template' => 'image',
    ], true);

    $page->update([
        'cover_image' => (string)$coverFile->uuid(),
        'hero_image' => (string)$heroFile->uuid(),
    ]);
}

// --- run ---

$kirby = new Kirby();
$kirby->impersonate('kirby');

$news = $kirby->page('news');
if (!$news) {
    fwrite(STDERR, "News page not found.\n");
    exit(1);
}

echo "Fetching article list from legacy site…\n";
$articles = collectAllListingItems();
echo count($articles) . " articles found.\n";

// Remove sample article
$sample = $news->find('sample-article') ?? $news->find('1_sample-article');
if ($sample) {
    $sample->delete(true);
    echo "Removed sample article.\n";
}

$imported = 0;

foreach ($articles as $item) {
    $slug = Url::slug(basename($item['path']));
    $publishedDate = parseEuropeanDate($item['date']);

    if ($publishedDate === '') {
        echo "Skip (no date): {$item['title']}\n";
        continue;
    }

    if ($kirby->page('news/' . $slug)) {
        echo "Skip (exists): {$slug}\n";
        continue;
    }

    echo "Import: {$item['title']}…\n";

    $detail = parseArticleDetail($item['path']);
    $body = $detail['body_text'] !== '' ? $detail['body_text'] : $item['excerpt'];

    $page = $news->createChild([
        'slug' => $slug,
        'template' => 'news-article',
        'draft' => false,
        'content' => [
            'title' => $item['title'],
            'published_date' => $publishedDate,
            'body' => $body,
            'page_theme' => 'light',
        ],
    ]);

    if ($page->isDraft()) {
        $page->publish();
        $page = freshPage($page);
    }

    if (!empty($detail['info'])) {
        $page->update([
            'info' => $detail['info'],
        ]);
        $page = freshPage($page);
    }

    attachCoverAndHero($page, $item['cover_url']);

    $imported++;
}

echo "Backfilling missing images…\n";
$imagesAdded = 0;

foreach ($articles as $item) {
    $slug = Url::slug(basename($item['path']));
    $page = $kirby->page('news/' . $slug);

    if (!$page || $item['cover_url'] === '') {
        continue;
    }

    if ($page->cover_image()->isNotEmpty() || $page->hero_image()->isNotEmpty()) {
        continue;
    }

    echo "Images: {$item['title']}…\n";
    attachCoverAndHero($page, $item['cover_url']);
    $imagesAdded++;
}

$kirby->cache('pages')->flush();

echo "Done. Imported {$imported} articles. Backfilled {$imagesAdded} images.\n";
