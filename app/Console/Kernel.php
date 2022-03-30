<?php

namespace App\Console;

use App\Console\Commands\CheckArchivedClericalJobs;
use App\Console\Commands\Driverautologout;
use App\Console\Commands\Driverpay;
use App\Console\Commands\ExpireTrip;
use App\Console\Commands\GooglekeyNotification;
use App\Console\Commands\LicenseNotification;
use App\Console\Commands\TripAutoComplete;
use App\Console\Commands\Driverclockout;
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
        //
        ExpireTrip::class,
        TripAutoComplete::class,
        LicenseNotification::class,
        GooglekeyNotification::class,
        Driverpay::class,
        Driverautologout::class,
        CheckArchivedClericalJobs::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('run:makeExpireTrip')->dailyAt('00:00'); //everyMinute();
        $schedule->command('check:licenseexpiry')->dailyAt('00:30');
        $schedule->command('check:googlekeyexpiry')->dailyAt('00:10');
        $schedule->command('trip:autocomplete')->dailyAt('23:59');
        $schedule->command('run:driverautologout')->dailyAt('4:00')->timezone('America/New_York');
        $schedule->command('run:driverautologout')->dailyAt('4:00')->timezone('America/Chicago');
        $schedule->command('run:driverautologout')->dailyAt('4:00')->timezone('America/Los_Angeles');
        $schedule->command('run:driverautologout')->dailyAt('4:00')->timezone('America/Denver');
        $schedule->command('run:driverautologout')->dailyAt('4:00')->timezone('Australia/Canberra');
        $schedule->command('run:driverautologout')->dailyAt('4:00')->timezone('America/Halifax');
        $schedule->command('run:driverautologout')->dailyAt('4:00')->timezone('America/Anchorage');
        $schedule->command('run:driverautologout')->dailyAt('4:00')->timezone('US/Samoa');
        $schedule->command('run:driverautologout')->dailyAt('4:00')->timezone('America/Adak');
        $schedule->command('run:driverautologout')->dailyAt('4:00')->timezone('Asia/Calcutta');

        /*$schedule->command('run:driverclockout')->dailyAt('00:01')->timezone('America/New_York');
        $schedule->command('run:driverclockout')->dailyAt('00:01')->timezone('America/Chicago');
        $schedule->command('run:driverclockout')->dailyAt('00:01')->timezone('America/Los_Angeles');
        $schedule->command('run:driverclockout')->dailyAt('00:01')->timezone('America/Denver');
        $schedule->command('run:driverclockout')->dailyAt('00:01')->timezone('Australia/Canberra');
        $schedule->command('run:driverclockout')->dailyAt('00:01')->timezone('America/Halifax');
        $schedule->command('run:driverclockout')->dailyAt('00:01')->timezone('America/Anchorage');
        $schedule->command('run:driverclockout')->dailyAt('00:01')->timezone('US/Samoa');
        $schedule->command('run:driverclockout')->dailyAt('00:01')->timezone('America/Adak');
        $schedule->command('run:driverclockout')->dailyAt('00:01')->timezone('Asia/Calcutta');*/

        $schedule->command('check:ArchivedJobs')->dailyAt('00:00');
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
