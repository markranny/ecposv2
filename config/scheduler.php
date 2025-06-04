<?php

// config/scheduler.php

use App\Console\Commands\GenerateInventorySummary;
use Illuminate\Support\Facades\Schedule;

return function (Schedule $schedule) {
    // Generate inventory summary daily at 1:00 AM
    $schedule->command(GenerateInventorySummary::class)
        ->dailyAt('01:00')
        ->appendOutputTo(storage_path('logs/inventory-summary.log'));
    
    // Optional: If you want to run it for the previous day as well
    // $schedule->command(GenerateInventorySummary::class, ['--date' => 'yesterday'])
    //     ->dailyAt('01:30');
};
















