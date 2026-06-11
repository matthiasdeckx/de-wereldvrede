<?php

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\StructureObject;
use Kirby\Toolkit\Str;

if (!function_exists('hero_curtain_opacity')) {
    /**
     * Resolve a validated hero curtain opacity value for CSS custom properties.
     */
    function hero_curtain_opacity(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $opacity = (float) Str::float($value);

        if ($opacity < 0.1 || $opacity > 0.3) {
            return null;
        }

        return rtrim(rtrim(sprintf('%.2f', $opacity), '0'), '.') ?: '0';
    }
}

if (!function_exists('hero_feature_logo_style')) {
    /**
     * Ratio-aware max dimensions for hero title logos (constant visual area).
     *
     * Derived from the legacy 48rem × 14rem box so wide logos gain width,
     * tall logos gain height, and square logos stay balanced.
     */
    function hero_feature_logo_style(?File $logo): ?string
    {
        if (!$logo instanceof File) {
            return null;
        }

        $width = $logo->width();
        $height = $logo->height();

        if ($width <= 0 || $height <= 0) {
            return null;
        }

        $ratio = $width / $height;
        $area = 48 * 14;

        $maxWidth = sqrt($area * $ratio);
        $maxHeight = sqrt($area / $ratio);

        $maxWidth = max(12, min(72, $maxWidth));
        $maxHeight = max(8, min(32, $maxHeight));

        return sprintf(
            '--logo-max-width: %.2frem; --logo-max-height: %.2frem',
            $maxWidth,
            $maxHeight
        );
    }
}

if (!function_exists('home_feature_bg')) {
    /**
     * @return array{bg_url: string|null, bg_position: string|null}
     */
    function home_feature_bg(?File $file): array
    {
        if (!$file instanceof File) {
            return ['bg_url' => null, 'bg_position' => null];
        }

        return [
            'bg_url' => $file->url(),
            'bg_position' => $file->focus()->isNotEmpty() ? $file->focus()->value() : null,
        ];
    }
}

if (!function_exists('project_hero_credits')) {
    /**
     * Director / writer rows for project hero and homepage feature slides.
     *
     * @return array<int, array{label: string|null, names: string|null}>
     */
    function project_hero_credits(Page $page): array
    {
        $credits = [];

        if ($page->director()->isNotEmpty()) {
            $credits[] = [
                'label' => ui_t('project.director'),
                'names' => $page->director()->value(),
            ];
        }

        if ($page->writer()->isNotEmpty()) {
            $credits[] = [
                'label' => ui_t('project.writer'),
                'names' => $page->writer()->value(),
            ];
        }

        if ($credits === [] && $page->writers_directors()->isNotEmpty()) {
            $credits[] = [
                'label' => ui_t('home.writer_director'),
                'names' => $page->writers_directors()->value(),
            ];
        }

        return $credits;
    }
}

if (!function_exists('home_feature_custom_credits')) {
    /**
     * @return array<int, array{label: string|null, names: string|null}>
     */
    function home_feature_custom_credits(StructureObject $feature): array
    {
        $credits = [];

        if ($feature->director()->isNotEmpty()) {
            $credits[] = [
                'label' => ui_t('project.director'),
                'names' => $feature->director()->value(),
            ];
        }

        if ($feature->writer()->isNotEmpty()) {
            $credits[] = [
                'label' => ui_t('project.writer'),
                'names' => $feature->writer()->value(),
            ];
        }

        if ($credits !== []) {
            return $credits;
        }

        $label = $feature->credits_label()->value();
        $names = $feature->credits()->value();

        if ($label !== '' || $names !== '') {
            $credits[] = [
                'label' => $label !== '' ? $label : null,
                'names' => $names !== '' ? $names : null,
            ];
        }

        return $credits;
    }
}

if (!function_exists('home_feature_slide')) {
    /**
     * Resolve homepage feature slide display data from structure item.
     *
     * @return array{
     *   mode: string,
     *   bg: File|null,
     *   bg_url: string|null,
     *   bg_position: string|null,
     *   category: string|null,
     *   title_type: string|null,
     *   title_logo: File|null,
     *   title_text: string|null,
     *   hero_credits: array<int, array{label: string|null, names: string|null}>,
     *   has_trailer: bool,
     *   trailer_vimeo: string,
     *   trailer_file_url: string|null,
     *   curtain_opacity: string|null,
     *   cta: array{type: string, url?: string, label?: string}|null
     * }
     */
    function home_feature_slide(StructureObject $feature): array
    {
        $mode = $feature->feature_mode()->or(
            $feature->linked_project()->isNotEmpty() ? 'project' : 'custom'
        )->value();

        $empty = [
            'mode' => $mode,
            'bg' => null,
            'bg_url' => null,
            'bg_position' => null,
            'category' => null,
            'title_type' => null,
            'title_logo' => null,
            'title_text' => null,
            'hero_credits' => [],
            'has_trailer' => false,
            'trailer_vimeo' => '',
            'trailer_file_url' => null,
            'curtain_opacity' => null,
            'cta' => null,
        ];

        if ($mode === 'project') {
            $project = $feature->linked_project()->toPage();
            if (!$project instanceof Page) {
                return $empty;
            }

            $bg = $project->hero_image()->toFile() ?: $project->cover()->toFile();
            $trailerSource = $project->trailer_source()->value();
            $hasTrailer = $trailerSource !== 'none' && $trailerSource !== '';
            $titleType = $project->title_type()->or('text')->value();
            $titleLogo = $titleType === 'logo' ? $project->title_logo()->toFile() : null;
            $types = $project->project_type()->split(',');
            $category = !empty($types) ? strtoupper(trim($types[0])) : null;

            return array_merge([
                'mode' => 'project',
                'bg' => $bg,
                'category' => $category,
                'title_type' => $titleType,
                'title_logo' => $titleLogo,
                'title_text' => $project->title()->value(),
                'hero_credits' => project_hero_credits($project),
                'has_trailer' => $hasTrailer,
                'trailer_vimeo' => $hasTrailer ? $project->trailer_vimeo()->value() : '',
                'trailer_file_url' => $hasTrailer ? $project->trailer_file()->toFile()?->url() : null,
                'curtain_opacity' => hero_curtain_opacity($project->hero_curtain_opacity()->value()),
                'cta' => [
                    'type' => 'project',
                    'url' => $project->url(),
                    'label' => ui_t('home.view_project'),
                ],
            ], home_feature_bg($bg));
        }

        $trailerSource = $feature->trailer_source()->value();
        $hasTrailer = $trailerSource !== 'none' && $trailerSource !== '';
        $buttonType = $feature->button_type()->or('url')->value();
        $cta = null;

        if ($buttonType === 'url' && $feature->button_url()->isNotEmpty()) {
            $cta = [
                'type' => 'url',
                'url' => $feature->button_url()->value(),
                'label' => ui_t('home.feature_button'),
            ];
        } elseif ($buttonType === 'coming_soon') {
            $cta = [
                'type' => 'coming_soon',
                'label' => ui_t('home.coming_soon'),
            ];
        }

        $bg = $feature->background_image()->toFile();

        return array_merge([
            'mode' => 'custom',
            'bg' => $bg,
            'category' => $feature->category()->isNotEmpty() ? $feature->category()->value() : null,
            'title_type' => 'text',
            'title_logo' => null,
            'title_text' => $feature->title()->isNotEmpty() ? $feature->title()->value() : null,
            'hero_credits' => home_feature_custom_credits($feature),
            'has_trailer' => $hasTrailer,
            'trailer_vimeo' => $hasTrailer ? $feature->trailer_vimeo()->value() : '',
            'trailer_file_url' => $hasTrailer ? $feature->trailer_file()->toFile()?->url() : null,
            'curtain_opacity' => hero_curtain_opacity($feature->hero_curtain_opacity()->value()),
            'cta' => $cta,
        ], home_feature_bg($bg));
    }
}

Kirby::plugin('de-wereldvrede/home-features', []);
