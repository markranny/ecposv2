<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackfillInventorySummaries extends Command
{
    protected $signature = 'inventory:backfill 
                          {--from= : Start date for backfill (format: YYYY-MM-DD)}
                          {--to= : End date for backfill (format: YYYY-MM-DD)}
                          {--days=30 : Number of days to backfill}
                          {--yesterday : Backfill only for yesterday}';
                          
    protected $description = 'Backfill inventory summaries for historical data';

    public function handle()
    {
        try {
            // Handle the "yesterday" option first
            if ($this->option('yesterday')) {
                $from = Carbon::yesterday()->startOfDay();
                $to = Carbon::yesterday()->endOfDay();
                
                $this->info("Starting backfill for yesterday ({$from->format('Y-m-d')})");
                
                // Call the summary generation command for yesterday
                $this->call('inventory:generate-summary', [
                    'date' => $from->format('Y-m-d')
                ]);
                
                $this->info("Completed backfill for yesterday");
                return 0;
            }
            
            // Normal date range processing
            $to = $this->option('to') ? Carbon::parse($this->option('to')) : Carbon::yesterday();
            
            // If from date is provided, use it; otherwise calculate based on days option
            if ($this->option('from')) {
                $from = Carbon::parse($this->option('from'));
            } else {
                $days = (int) $this->option('days');
                $from = Carbon::parse($to)->subDays($days);
            }

            $this->info("Starting backfill from {$from->format('Y-m-d')} to {$to->format('Y-m-d')}");
            
            $currentDate = clone $from;
            $totalDays = $from->diffInDays($to) + 1;
            $bar = $this->output->createProgressBar($totalDays);
            
            $bar->start();
            
            while ($currentDate->lte($to)) {
                $this->info("\nProcessing: " . $currentDate->format('Y-m-d'));
                
                try {
                    // Call the summary generation command for each date
                    $this->call('inventory:generate-summary', [
                        'date' => $currentDate->format('Y-m-d')
                    ]);
                    
                    $this->info("Completed processing for: " . $currentDate->format('Y-m-d'));
                } catch (\Exception $e) {
                    $this->error("Error processing {$currentDate->format('Y-m-d')}: " . $e->getMessage());
                    
                    // Log the error for further investigation
                    Log::error("Backfill error for date {$currentDate->format('Y-m-d')}: {$e->getMessage()}");
                }
                
                // Move to the next day regardless of whether current day succeeded
                $currentDate->addDay();
                $bar->advance();
            }
            
            $bar->finish();
            $this->info("\nBackfill completed.");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Fatal error in backfill process: " . $e->getMessage());
            Log::error("Fatal backfill error: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}