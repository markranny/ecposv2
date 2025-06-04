<?php

// config/commands.php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Command Registration
    |--------------------------------------------------------------------------
    |
    | Here you may register commands that your application provides.
    | These commands will be available in your Artisan CLI.
    |
    */

    'register' => [
        // Register the inventory commands
        App\Console\Commands\GenerateInventorySummary::class,
        App\Console\Commands\BackfillInventorySummaries::class,
    ],
];