<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Would you like the install button to appear on all pages?
      Set true/false
    |--------------------------------------------------------------------------
    */

    'install-button' => true,

    /*
    |--------------------------------------------------------------------------
    | PWA Manifest Configuration
    |--------------------------------------------------------------------------
    |  php artisan erag:update-manifest
    */

    'manifest' => [
        'name' => 'YourMoment',
        'short_name' => 'YourMoment',
        'background_color' => '#ffffff',
        'display' => 'standalone',
        'description' => 'Share your precious moments with friends.',
        'theme_color' => '#16a34a',
        'icons' => [
            [
                'src' => '/icons/icon-192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
            ],
            [
                'src' => '/icons/icon-512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Configuration
    |--------------------------------------------------------------------------
    | Toggles the application's debug mode based on the environment variable
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Livewire Integration
    |--------------------------------------------------------------------------
    | Set to true if you're using Livewire in your application to enable
    | Livewire-specific PWA optimizations or features.
    */

    'livewire-app' => false,
];
