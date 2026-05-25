<?php

use Kirby\Cms\App as Kirby;
use Kirby\Toolkit\Str;

if (function_exists('ui_translation_file_defaults') === false) {
  function ui_translation_file_defaults(string $lang): array
  {
    static $cache = [];

    $lang = $lang === 'fr' ? 'fr' : 'en';

    if (isset($cache[$lang]) === true) {
      return $cache[$lang];
    }

    $kirby = kirby();
    $path = $kirby->root('languages') . '/' . $lang . '.php';

    if (is_file($path) === false) {
      return $cache[$lang] = [];
    }

    $data = include $path;

    return $cache[$lang] = $data['translations'] ?? [];
  }
}

if (function_exists('ui_translation_default') === false) {
  function ui_translation_default(string $key, string $lang): string
  {
    $defaults = ui_translation_file_defaults($lang);

    return $defaults[$key] ?? '';
  }
}

if (function_exists('uiTranslationOverrides') === false) {
  function uiTranslationOverrides(): array
  {
    static $overrides = null;

    if ($overrides !== null) {
      return $overrides;
    }

    $overrides = [];
    $site = site();

    if ($site === null) {
      return $overrides;
    }

    $field = $site->content()->get('ui_translation_overrides');
    if ($field === null || $field->isEmpty()) {
      return $overrides;
    }

    foreach ($field->toStructure() as $item) {
      $key = trim((string)$item->key()->value());
      if ($key === '') {
        continue;
      }

      $overrides[$key] = [
        'en' => trim((string)$item->en()->value()),
        'fr' => trim((string)$item->fr()->value()),
      ];
    }

    return $overrides;
  }
}

if (function_exists('ui_t') === false) {
  function ui_t(string $key, string|array|null $fallback = null): string|array|\Closure|null
  {
    $default = t($key, $fallback);
    $lang = kirby()->language()?->code() ?? 'en';
    // Str::before('fr', '_') returns '' when no underscore exists — breaks lookups for short codes.
    if (str_contains($lang, '_') === true) {
      $lang = Str::before($lang, '_');
    }

    $overrides = uiTranslationOverrides();
    $custom = $overrides[$key][$lang] ?? null;

    if (is_string($custom) === true && $custom !== '') {
      return $custom;
    }

    return $default;
  }
}

if (function_exists('ui_tt') === false) {
  function ui_tt(
    string $key,
    array $replace = [],
    string|array|null $fallback = null
  ): string {
    return Str::template((string)ui_t($key, $fallback), $replace, ['fallback' => '-']);
  }
}

return Kirby::plugin('site/ui-translations', [
  'siteMethods' => [
    'uiTranslationDefaultEn' => function (string $key = ''): string {
      $key = trim($key);
      if ($key === '') {
        return '—';
      }
      $text = ui_translation_default($key, 'en');

      return $text !== '' ? $text : '—';
    },
    'uiTranslationDefaultFr' => function (string $key = ''): string {
      $key = trim($key);
      if ($key === '') {
        return '—';
      }
      $text = ui_translation_default($key, 'fr');

      return $text !== '' ? $text : '—';
    },
    'uiTranslationSelectOptions' => function (): array {
      $en = ui_translation_file_defaults('en');
      $out = [];
      foreach ($en as $key => $label) {
        $out[$key] = $key . ' — ' . $label;
      }

      return $out;
    },
  ],
]);
