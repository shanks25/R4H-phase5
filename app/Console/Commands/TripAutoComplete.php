<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TripAutoComplete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trip:autocomplete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'autocomplete the trip';

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
        
    }
}
