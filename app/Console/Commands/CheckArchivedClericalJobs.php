<?php

namespace App\Console\Commands;

use App\Model\Clerical;
use App\Model\Cron;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Model\DriverRequestStep;
use App\Model\DriverMaster;

class CheckArchivedClericalJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:ArchivedJobs';
    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if any job application needs to be convered to archive';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //Cron::Create(['msg'=>'Cron Started to check archive clerical jobs']);
        // get list of job applications which are not approved and are older than 90 dayss
        $jobs =   Clerical::where('is_final_approved','!=',2)->where('created_at', '<=', Carbon::now()->subDays(90)->toDateTimeString())->get();
        foreach ($jobs as $key => $job) {
            $job->is_archived = 1 ;
            $job->save();   
        }

        $drivers =   DriverMaster::where('full_registration_done', 1)->where('created_at', '<=', Carbon::now()->subDays(90)->toDateTimeString())->get();
        foreach ($drivers as $key => $d) {
            $d->is_archived = 1;
            $d->save();   
        }
    }
}
