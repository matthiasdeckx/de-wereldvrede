<?php

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\StructureObject;

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
     *   credits_label: string|null,
     *   credits_names: string|null,
     *   has_trailer: bool,
     *   trailer_vimeo: string,
     *   trailer_file_url: string|null,
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
            'credits_label' => null,
            'credits_names' => null,
            'has_trailer' => false,
            'trailer_vimeo' => '',
            'trailer_file_url' => null,
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
            $creditsNames = $project->writers_directors()->isNotEmpty()
                ? $project->writers_directors()->value()
                : null;

            return array_merge([
                'mode' => 'project',
                'bg' => $bg,
                'category' => $category,
                'title_type' => $titleType,
                'title_logo' => $titleLogo,
                'title_text' => $project->title()->value(),
                'credits_label' => $creditsNames ? ui_t('home.writer_director') : null,
                'credits_names' => $creditsNames,
                'has_trailer' => $hasTrailer,
                'trailer_vimeo' => $hasTrailer ? $project->trailer_vimeo()->value() : '',
                'trailer_file_url' => $hasTrailer ? $project->trailer_file()->toFile()?->url() : null,
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
            'credits_label' => $feature->credits_label()->isNotEmpty()
                ? $feature->credits_label()->value()
                : null,
            'credits_names' => $feature->credits()->isNotEmpty() ? $feature->credits()->value() : null,
            'has_trailer' => $hasTrailer,
            'trailer_vimeo' => $hasTrailer ? $feature->trailer_vimeo()->value() : '',
            'trailer_file_url' => $hasTrailer ? $feature->trailer_file()->toFile()?->url() : null,
            'cta' => $cta,
        ], home_feature_bg($bg));
    }
}

Kirby::plugin('de-wereldvrede/home-features', []);
