<?php

use Kirby\Cms\StructureObject;

if (!function_exists('creator_production_items')) {
    /**
     * Merge linked Work projects and custom productions for the creator overlay.
     *
     * @return list<array{title: string, url: string|null, year: string|null, external: bool}>
     */
    function creator_production_items(StructureObject $creator): array
    {
        $items = [];

        foreach ($creator->productions()->toPages() as $page) {
            $items[] = [
                'title' => $page->title()->value(),
                'url' => $page->url(),
                'year' => $page->year()->value() ?: null,
                'external' => false,
            ];
        }

        foreach ($creator->custom_productions()->toStructure() as $entry) {
            $title = trim((string) $entry->title()->value());
            if ($title === '') {
                continue;
            }

            $url = trim((string) $entry->url()->value());
            $year = trim((string) $entry->year()->value());
            $items[] = [
                'title' => $title,
                'url' => $url !== '' ? $url : null,
                'year' => $year !== '' ? $year : null,
                'external' => $url !== '',
            ];
        }

        return $items;
    }
}
