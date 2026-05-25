<?php

use Kirby\Cms\App as Kirby;

return Kirby::plugin('site/page-theme', [
  'pageMethods' => [
    'usesLightTheme' => function () {
      if ($this->page_theme()->isNotEmpty()) {
        return $this->page_theme()->value() === 'light';
      }

      return in_array(
        $this->intendedTemplate()->name(),
        ['work', 'news', 'news-article', 'creators'],
        true
      );
    },
  ],
]);
