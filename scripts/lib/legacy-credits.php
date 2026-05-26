<?php

/**
 * Shared helpers for parsing project credits from legacy Drupal HTML.
 */

function legacyCreditsHtmlToPlainText(string $html): string
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

/**
 * Parse credit rows from legacy markup: <p><strong>role</strong> names</p>
 *
 * @return list<array{role: string, names: string}>
 */
function parseLegacyCredits(string $html): array
{
    $credits = [];

    if (!preg_match_all(
        '#<p>\s*<strong>([^<]+)</strong>\s*(.*?)</p>#isu',
        $html,
        $matches,
        PREG_SET_ORDER
    )) {
        return $credits;
    }

    foreach ($matches as $match) {
        $role = trim(
            html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            " \t\n\r\0\x0B:&"
        );
        $names = legacyCreditsHtmlToPlainText($match[2]);

        if ($role === '' || $names === '') {
            continue;
        }

        $credits[] = [
            'role' => $role,
            'names' => $names,
        ];
    }

    return $credits;
}

function extractLegacyCreditsFromProjectHtml(string $html): array
{
    if (!preg_match(
        '#<section class="column-content">(.*?)</section>#s',
        $html,
        $section
    )) {
        return [];
    }

    if (!preg_match(
        '#<div class="text small-column">\s*(.*?)\s*</div>#s',
        $section[1],
        $side
    )) {
        return [];
    }

    return parseLegacyCredits($side[1]);
}

function projectCreditsLookBundled($creditsField): bool
{
    $structure = $creditsField->toStructure();

    if ($structure->count() !== 1) {
        return false;
    }

    $names = $structure->first()->names()->value();

    return (bool) preg_match(
        '/\b(written by|directed by|produced by|in coproduction|with the support|sales|cast)\b/i',
        $names
    );
}

function legacyProjectPathForSlug(string $slug): string
{
    return '/projects/' . $slug;
}
