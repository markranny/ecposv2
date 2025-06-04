<?php

return [
    'name' => env('APP_NAME', 'My Laravel PWA'),
    'manifest' => [
        'name' => env('APP_NAME', 'My Laravel PWA'),
        'short_name' => 'LaravelPWA',
        'start_url' => '/',
        'background_color' => '#ffffff',
        'theme_color' => '#000000',
        'display' => 'standalone',
        'orientation'=> 'any',
        'status_bar'=> 'black',
        'icons' => [
            '72x72' => [
                'path' => '/images/icons/ec1-72x72.png',
                'purpose' => 'any'
            ],
            '96x96' => [
                'path' => '/images/icons/ec1-96x96.png',
                'purpose' => 'any'
            ],
            '128x128' => [
                'path' => '/images/icons/ec1-128x128.png',
                'purpose' => 'any'
            ],
            '144x144' => [
                'path' => '/images/icons/ec1-144x144.png',
                'purpose' => 'any'
            ],
            '152x152' => [
                'path' => '/images/icons/ec1-152x152.png',
                'purpose' => 'any'
            ],
            '192x192' => [
                'path' => '/images/icons/ec1-192x192.png',
                'purpose' => 'any'
            ],
            '384x384' => [
                'path' => '/images/icons/ec1-384x384.png',
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => '/images/icons/ec1-512x512.png',
                'purpose' => 'any'
            ],
        ],
        'splash' => [
            '640x1136' => '/images/icons/ec2-640x1136.png',
            '750x1334' => '/images/icons/ec2-750x1334.png',
            '828x1792' => '/images/icons/ec2-828x1792.png',
            '1125x2436' => '/images/icons/ec2-1125x2436.png',
            '1242x2208' => '/images/icons/ec2-1242x2208.png',
            '1242x2688' => '/images/icons/ec2-1242x2688.png',
            '1536x2048' => '/images/icons/ec2-1536x2048.png',
            '1668x2224' => '/images/icons/ec2-1668x2224.png',
            '1668x2388' => '/images/icons/ec2-1668x2388.png',
            '2048x2732' => '/images/icons/ec2-2048x2732.png',
        ],
        'custom' => []
    ]
];

