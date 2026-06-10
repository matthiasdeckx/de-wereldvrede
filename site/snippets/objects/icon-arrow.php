<?php

/**
 * Horizontal arrow (32×10px). Line shortens on parent hover; SVG size stays fixed.
 *
 * @var string $direction right|left|down|up
 * @var string|null $class
 */

$direction = $direction ?? 'right';
$class = trim('c-icon-arrow ' . ($class ?? ''));

if ($direction === 'left') {
  $class .= ' c-icon-arrow--left';
}

$wrapClass = match ($direction) {
  'down' => ' c-icon-arrow-wrap--down',
  'up' => ' c-icon-arrow-wrap--up',
  default => '',
};

?>
<?php if ($wrapClass !== ''): ?><span class="c-icon-arrow-wrap<?= esc($wrapClass, 'attr') ?>"><?php endif ?>
<svg
  class="<?= esc($class, 'attr') ?>"
  width="32"
  height="10"
  viewBox="0 0 32 10"
  fill="none"
  xmlns="http://www.w3.org/2000/svg"
  aria-hidden="true"
>
  <line
    class="c-icon-arrow__line"
    x1="0"
    y1="4.403"
    x2="30.229"
    y2="4.403"
  />
  <path
    class="c-icon-arrow__head"
    d="M25.575 0.221L30.229 4.403L25.575 8.584"
  />
</svg>
<?php if ($wrapClass !== ''): ?></span><?php endif ?>
