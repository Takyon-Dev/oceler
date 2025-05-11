<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MTurk\MTurk;

class MTurkTestConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mturk:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test MTurk connection';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $mturk = new MTurk();
        $result = $mturk->testConnection();
        
        if ($result) {
            $this->info('Connection successful!');
        } else {
            $this->error('Connection failed!');
        }
    }
}
