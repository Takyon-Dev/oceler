<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MturkHit;
use App\MTurk\MTurk;
use Illuminate\Support\Facades\DB;

class MTurkProcessBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mturk:process-bonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process MTurk bonuses';

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
            ->where('bonus_processed', false)
            ->get();

        if ($hits->isEmpty()) {
            $this->info('No bonuses to process.');
            return;
        }

        $mturk = new MTurk();
        foreach ($hits as $hit) {
            try {
                $result = $mturk->processBonus($hit);
                if ($result) {
                    $this->info("Processed bonus for assignment {$hit->assignment_id}");
                } else {
                    $this->error("Failed to process bonus for assignment {$hit->assignment_id}");
                }
            } catch (\Exception $e) {
                $this->error("Error processing bonus for assignment {$hit->assignment_id}: {$e->getMessage()}");
            }
        }
    }
}
