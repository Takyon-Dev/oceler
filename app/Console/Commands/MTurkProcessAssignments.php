<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MturkHit;
use App\MTurk\MTurk;
use Illuminate\Support\Facades\DB;

class MTurkProcessAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mturk:process-assignments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process MTurk assignments';

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
        $active_players = DB::table('trial_user')
            ->where('selected_for_removal', 0)
            ->pluck('user_id')
            ->toArray();

        $hits = MturkHit::whereNotIn('user_id', $active_players)
            ->where('status', 'Submitted')
            ->get();

        if ($hits->isEmpty()) {
            $this->info('No assignments to process.');
            return;
        }

        $mturk = new MTurk();
        foreach ($hits as $hit) {
            try {
                $result = $mturk->processAssignment($hit);
                if ($result) {
                    $this->info("Processed assignment {$hit->assignment_id}");
                } else {
                    $this->error("Failed to process assignment {$hit->assignment_id}");
                }
            } catch (\Exception $e) {
                $this->error("Error processing assignment {$hit->assignment_id}: {$e->getMessage()}");
            }
        }
    }
}
