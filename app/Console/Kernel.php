<?php

namespace oceler\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \oceler\Console\Commands\Inspire::class,
        Commands\MTurkTestConnection::class,
        Commands\MTurkProcessAssignments::class,
        Commands\MTurkProcessBonus::class,
        Commands\MTurkProcessQualification::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
      /*
        $schedule->command('inspire')
                 ->hourly();

      */

        $schedule->command('MTurkProcessAssignments')
                 ->everyFiveMinutes();

        $schedule->command('MTurkProcessBonus')
                 ->everyThirtyMinutes();

        $schedule->command('MTurkProcessQualification')
                 ->everyThirtyMinutes();


    }
}
