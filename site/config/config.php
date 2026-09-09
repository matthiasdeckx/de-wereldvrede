<?php

return [
  'debug' => true,
  'languages' => true,
  'kirbytext.video.options' => [
    'vimeo' => [
      'transparent' => 0,
    ],
  ],
  'cache' => [
    'pages' => [
      'active' => false,
    ],
  ],
  'thumbs' => [
    'quality' => 90,
    // No global format: logos/PNGs with alpha keep their source format.
    // Photo srcsets opt into WebP explicitly below.
    'interlace' => true,
    'srcsets' => [
      'default' => [
        '480w' => ['width' => 480, 'quality' => 90, 'format' => 'webp'],
        '960w' => ['width' => 960, 'quality' => 90, 'format' => 'webp'],
        '1440w' => ['width' => 1440, 'quality' => 90, 'format' => 'webp'],
        '2160w' => ['width' => 2160, 'quality' => 90, 'format' => 'webp'],
      ],
      'small' => [
        '480w' => ['width' => 480, 'quality' => 90, 'format' => 'webp'],
        '960w' => ['width' => 960, 'quality' => 90, 'format' => 'webp'],
        '1440w' => ['width' => 1440, 'quality' => 90, 'format' => 'webp'],
      ],
      // Keep original format (PNG alpha, SVG, etc.) — do not force WebP.
      'logo' => [
        '480w' => ['width' => 480, 'quality' => 90],
        '960w' => ['width' => 960, 'quality' => 90],
        '1440w' => ['width' => 1440, 'quality' => 90],
      ],
      'max' => [
        '480w' => ['width' => 480, 'quality' => 90, 'format' => 'webp'],
        '960w' => ['width' => 960, 'quality' => 90, 'format' => 'webp'],
        '1440w' => ['width' => 1440, 'quality' => 90, 'format' => 'webp'],
        '2160w' => ['width' => 2160, 'quality' => 90, 'format' => 'webp'],
        '3240w' => ['width' => 3240, 'quality' => 90, 'format' => 'webp'],
      ],
      'portrait' => [
        '160w' => ['width' => 160, 'quality' => 80, 'format' => 'webp'],
        '240w' => ['width' => 240, 'quality' => 80, 'format' => 'webp'],
        '320w' => ['width' => 320, 'quality' => 80, 'format' => 'webp'],
      ],
    ],
  ],
  'session' => [
    'durationNormal' => 432000,
    'durationLong' => 1814400,
    'timeout' => 3600,
    'cookieName' => 'kirby_session',
    'gcInterval' => 100,
  ],
  'isaactopo.xmlsitemap.ignore' => ['error'],
];
