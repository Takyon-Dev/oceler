<?php
namespace oceler\QueueManager;

use DB;
use oceler\Queue;
use oceler\Trial;


class QueueManager
{
  private $INACTIVE_QUEUE_TIME = 6; // In seconds
  private $INACTIVE_TRIAL_TIME = 1800; // 30 mins in seconds

  public function __construct()
  {

  }

  public function manageQueue() {
    // Delete any inactive users from Queue
    $this->deleteInactiveQueueUsers();

    // Get all trials that are active but already have been filled
    // by querying the trial_user table

    $running_trials = DB::table('trial_user')
                        ->get();

    $filled_trials = [];
    foreach ($running_trials as $t) {
      $filled_trials[] = $t->trial_id;
    }

    // Get all active, not-already-filled trials
    $trials = Trial::where('is_active', 1)
                    ->whereNotIn('id', $filled_trials)
                    ->orderBy('created_at', 'asc')
                    ->get();



    // If no trials exist, return
    if(count($trials) == 0){
      return;
    }

    // For each active trial, see if the # of players in the queue
    // is equal to the required # of players for the trial
    foreach($trials as $trial) {

        $queued_players = Queue::where('trial_type', '=', $trial->trial_type)
                                        ->count();

        // If there aren't enough players for this trial type,
        // move on to the next one
        if($queued_players < $trial->num_players){
          continue;
        }

        // Otherwise, take the required amount
        $selected = Queue::where('trial_type', '=', $player->trial_type)
                                  ->orderBy('created_at', 'asc')
                                  ->take($trial->num_players)
                                  ->get();

        // Shuffle the collection of selected players so that
        // their network node positions will essentially
        // be randomized
        $selected = $selected->shuffle();

        // Insert each selected player into the trial_user table
        // along with the group they are part of

        $group = 1;
        $count = 0; // Counts the users added so far
        foreach ($selected as $user) {
            DB::table('trial_user')->insert([
              'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
              'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
              'user_id' => $user->user_id,
              'trial_id' => $trial->id,
              'group_id' => \DB::table('groups')
                            ->where('trial_id', $trial->id)
                            ->where('group', $group)
                            ->value('id')
            ]);
            $count++;
            if($count >= $trial->num_players / $trial->num_groups) $group++;
            // ... And delete that user from the queue
            Queue::where('user_id', '=', $user->user_id)->delete();
        }
    }
  }

  private function deleteInactiveQueueUsers() {
      $INACTIVE_QUEUE_TIME = 6;
      $dt = \Carbon\Carbon::now();
      Queue::where('updated_at', '<', $dt->subSeconds($INACTIVE_QUEUE_TIME))->delete();
  }

  private function deleteInactiveTrialUsers() {
      $dt = \Carbon\Carbon::now();
      DB::table('trial_user')->where('last_ping', '<', $dt->subSeconds($this->INACTIVE_TRIAL_TIME))->delete();
  }


}
