<?php

namespace oceler\Console\Commands;
use Illuminate\Console\Command;
use DB;

class MTurkProcessBonus extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MTurkProcessBonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes HIT assignment bonuses using the MTurk API';

    private $hits;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $log = env('CRON_OUTPUT_LOG', '');
        $file = fopen($log,"a");
        echo fwrite($file,"Running ProcessBonus cmd\n");
        fclose($file);

        parent::__construct();

        $active_players = DB::table('trial_user')->lists('user_id');
        $this->hits = \oceler\MturkHit::whereNotIn('user_id', $active_players)
                                 ->where('bonus_processed', '=', 0)
                                 ->where('trial_id', '>', 0)
                                 ->orWhere('trial_id', '=', -1)
                                 ->whereNotIn('user_id', $active_players)
                                 ->where('bonus_processed', '=', 0)
                                 ->get();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $mturks = [];
        foreach ($this->hits as $key => $hit) {
          $mturks[$key] = new \oceler\MTurk\MTurk();
          $mturks[$key]->hit = $hit;
          $mturks[$key]->process_bonus();
        }
    }
}
