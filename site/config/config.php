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
    'format' => 'webp',
    'interlace' => true,
    'srcsets' => [
      'default' => [
        '480w' => ['width' => 480, 'quality' => 90],
        '960w' => ['width' => 960, 'quality' => 90],
        '1440w' => ['width' => 1440, 'quality' => 90],
        '2160w' => ['width' => 2160, 'quality' => 90],
      ],
      'small' => [
        '480w' => ['width' => 480, 'quality' => 90],
        '960w' => ['width' => 960, 'quality' => 90],
        '1440w' => ['width' => 1440, 'quality' => 90],
      ],
      'max' => [
        '480w' => ['width' => 480, 'quality' => 90],
        '960w' => ['width' => 960, 'quality' => 90],
        '1440w' => ['width' => 1440, 'quality' => 90],
        '2160w' => ['width' => 2160, 'quality' => 90],
        '3240w' => ['width' => 3240, 'quality' => 90],
      ],
      'portrait' => [
        '160w' => ['width' => 160, 'quality' => 80],
        '240w' => ['width' => 240, 'quality' => 80],
        '320w' => ['width' => 320, 'quality' => 80],
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
