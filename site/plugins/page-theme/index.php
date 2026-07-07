<?php

use Kirby\Cms\App as Kirby;

return Kirby::plugin('site/page-theme', [
  'pageMethods' => [
    'usesLightTheme' => function () {
      $template = $this->intendedTemplate()->name();

      if (in_array($template, ['news', 'news-article'], true)) {
        return true;
      }

      if ($template === 'project') {
        return false;
      }

      if ($this->page_theme()->isNotEmpty()) {
        return $this->page_theme()->value() === 'light';
      }

      return in_array(
        $this->intendedTemplate()->name(),
        ['work', 'creators'],
        true
      );
    },
  ],
]);
