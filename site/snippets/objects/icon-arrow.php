<?php

/**
 * Arrow icon. Horizontal: 32×10px. Vertical: 10×22px. Line shortens on parent hover.
 *
 * @var string $direction right|left|down|up
 * @var string|null $class
 */

$direction = $direction ?? 'right';
$class = trim('c-icon-arrow ' . ($class ?? ''));
$isVertical = $direction === 'up' || $direction === 'down';

if ($direction === 'left') {
  $class .= ' c-icon-arrow--left';
}

if ($isVertical) {
  $class .= ' c-icon-arrow--vertical';
}

if ($direction === 'up') {
  $class .= ' c-icon-arrow--up';
}

$wrapClass = match ($direction) {
  'down' => ' c-icon-arrow-wrap--down',
  'up' => ' c-icon-arrow-wrap--up',
  default => '',
};

?>
<?php if ($wrapClass !== ''): ?><span class="c-icon-arrow-wrap<?= esc($wrapClass, 'attr') ?>"><?php endif ?>
<?php if ($isVertical): ?>
<svg
  class="<?= esc($class, 'attr') ?>"
  width="10"
  height="22"
  viewBox="0 0 10 22"
  fill="none"
  xmlns="http://www.w3.org/2000/svg"
  aria-hidden="true"
>
  <?php if ($direction === 'down'): ?>
  <line
    class="c-icon-arrow__line"
    x1="5"
    y1="5.229"
    x2="5"
    y2="19.229"
  />
  <path
    class="c-icon-arrow__head"
    d="M1.573 15.425L5 19.229L8.427 15.425"
  />
  <?php else: ?>
  <line
    class="c-icon-arrow__line"
    x1="5"
    y1="2.771"
    x2="5"
    y2="16.771"
  />
  <path
    class="c-icon-arrow__head"
    d="M1.573 6.575L5 2.771L8.427 6.575"
  />
  <?php endif ?>
</svg>
<?php else: ?>
<svg
  class="<?= esc($class, 'attr') ?>"
  width="32"
  height="10"
  viewBox="0 0 32 10"
  fill="none"
  xmlns="http://www.w3.org/2000/svg"
  aria-hidden="true"
>
  <?php if ($direction === 'left'): ?>
  <line
    class="c-icon-arrow__line"
    x1="1.771"
    y1="4.403"
    x2="32"
    y2="4.403"
  />
  <path
    class="c-icon-arrow__head"
    d="M6.425 0.221L1.771 4.403L6.425 8.584"
  />
  <?php else: ?>
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
  <?php endif ?>
</svg>
<?php endif ?>
<?php if ($wrapClass !== ''): ?></span><?php endif ?>
