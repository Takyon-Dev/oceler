<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MturkHit;
use App\MTurk\MTurk;
use Illuminate\Support\Facades\DB;

class MTurkProcessQualification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mturk:process-qualification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process MTurk qualifications';

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
            ->where('status', 'Approved')
            ->where('qualification_processed', false)
            ->get();

        if ($hits->isEmpty()) {
            $this->info('No qualifications to process.');
            return;
        }

        $mturks = [];
        foreach ($hits as $key => $hit) {
            try {
                if (!isset($mturks[$key])) {
                    $mturks[$key] = new MTurk();
                }
                $result = $mturks[$key]->processQualification($hit);
                if ($result) {
                    $this->info("Processed qualification for worker {$hit->worker_id}");
                } else {
                    $this->error("Failed to process qualification for worker {$hit->worker_id}");
                }
            } catch (\Exception $e) {
                $this->error("Error processing qualification for worker {$hit->worker_id}: {$e->getMessage()}");
            }
        }
    }
}
