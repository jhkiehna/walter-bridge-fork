<?php

namespace App\Console;

use App\Console\Commands\FetchCalls;
use App\Console\Commands\FetchSendouts;
use App\Console\Commands\FetchInterviews;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\FetchCandidatesCoded;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\KafkaConsume;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        FetchInterviews::class,
        FetchSendouts::class,
        FetchCalls::class,
        FetchCandidatesCoded::class,
        KafkaConsume::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call('App\Services\Walter\SendoutReader@read')
                 ->everyMinute()
                 ->name("ReadSendouts")
                 ->withoutOverlapping();

        $schedule->call('App\Services\Walter\InterviewReader@read')
                 ->everyMinute()
                 ->name("ReadInterviews")
                 ->withoutOverlapping();

        $schedule->call('App\Services\Walter\CandidateCodedReader@read')
                 ->everyMinute()
                 ->name("ReadCandidateCodeds")
                 ->withoutOverlapping();

        $schedule->call('App\Services\Stats\CallReader@read')
                 ->everyMinute()
                 ->name("ReadCalls")
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
