<?php

namespace oceler\Console\Commands;
use Illuminate\Console\Command;

class MTurkTestConnection extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MTurkTestConnection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tests the connection to the MTurk API';

    private $hits;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->hits = \DB::table('mturk_hits')
                          ->get();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $mturk = new \oceler\MTurk\MTurk();
        $mturk->hits = $this->hits;
        $mturk->testConnection();
    }
}
