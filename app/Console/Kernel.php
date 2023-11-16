<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('seminar:reminder')->everyFiveMinutes()
        //     ->emailOutputOnFailure('tripletens.kc@gmail.com')->runInBackground();

        // $schedule->command('seminar:check-status')->everyMinute()
        //     ->emailOutputOnFailure('tripletens.kc@gmail.com')->runInBackground();
        // 4Liberty@2023atlas
        // php artisan queue:work & 
        // while true; do 
        // echo "=> Running scheduler"
        // php artisan schedule:work || true;
        // echo "=> sleeping for 60 seconds"
        // sleep 60;
        // done
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        // $this->load(__DIR__.'/Commands');

        // require base_path('routes/console.php');
    }
}
