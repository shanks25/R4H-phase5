<?php

namespace App\Console\Commands;

use App\Model\DriverMaster;
use Illuminate\Console\Command;

class FreeVehicles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:free-vehicles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'free vehicles if assign in any driver';

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
        $da['vehicle_id'] = NULL;
        DriverMaster::where('vehicle_id', '!=', NULL)->update($da);
        echo 'success';
    }
}
