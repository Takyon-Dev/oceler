<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;
use \oceler\Trial;
use \oceler\Queue;
use View;
use Auth;
use DB;
use Response;
use Session;
use Input;
use Log;


class TrialController extends Controller
{
    /**
     * Gets all trials (in order from
     * latest to earliest) and displays them.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $trials = Trial::orderBy('id', 'desc')
                      ->with('users')
                      ->with('archive')
                      ->get();

      return View::make('layouts.admin.trials')
                  ->with('trials', $trials);
    }

    /**
     * Displays the New Trial form, in which the
     * trial configuration is set.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $factoidsets = \oceler\Factoidset::lists('name', 'id');
      $networks = \oceler\Network::lists('name', 'id');
      $namesets = \oceler\Nameset::lists('name', 'id');

      return View::make('layouts.admin.trial-config')
                  ->with('factoidsets', $factoidsets)
                  ->with('networks', $networks)
                  ->with('namesets', $namesets);
    }

    /**
     * Processes the New Trial form, saving all config
     * data to the appropriate tables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

      $trial = new Trial();
      $trial->storeTrialConfig($request);
      $trial->logConfig();

      return \Redirect::to('/admin/trial');
    }


    /**
     * Removes the specified trial from the database.
     * We are actually using soft-deletes with trials,
     * so the trial remains in the db, but will not
     * be included in any queries.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $trial = Trial::find($id);
        $trial->stopTrial();
        $trial->delete();
        return \Redirect::to('/admin/trial');
    }

    /**
     * Toggles a trial between active / inactive.
     * Only active trials can be filled, making it possible
     * for multiple trials to be set up in advance.
     */
    public function toggle($id)
    {
      $trial = Trial::find($id);
      $trial->is_active = !$trial->is_active;
      $trial->save();

      // Writes active status to the trial's log file
      $msg = 'Trial '.$id;
      $msg .= ($trial->is_active) ? ' is now active' : ' is not active';
      \oceler\Log::trialLog($id, $msg);
      Log::info($msg ." :: (type: ". $trial->trial_type ."; num_players: ". $trial->num_players .")");
      return \Redirect::to('/admin/trial');
    }

    /**
     * Stops an in-progress trial by removing
     * players from the trial_user table.
     */
    public function stopTrial($id)
    {
      $trial = Trial::find($id);
      $trial->stopTrial();

      // Writes active status to the trial's log file
      $msg = 'Trial '.$id;
      $msg .= ' was stopped by the administrator';
      \oceler\Log::trialLog($id, $msg);
      Log::info($msg);
      return \Redirect::to('/admin/trial');
    }

    /**
     * Displays the Trial Queue layout to the player
     * @return [type] [description]
     */
    public function enterQueue()
    {
      // First, check that they aren't already in a trial
      $trialPlayer = \oceler\User::with('trials')->find(Auth::user()->id);

      // If they are, redirect to the trial instructions page
      if(count($trialPlayer->trials) > 0) {
        return redirect('/player/trial/instructions');
      }

      $u_id = Auth::user()->id;
      $last_trial_type = Auth::user()->lastTrialType();
      $dt = \Carbon\Carbon::now();
      // Add the player to the queue and set updated_at to
      // current date/time
      $player = \oceler\Queue::firstOrNew(['user_id' => $u_id]);
      $player->trial_type = ($last_trial_type + 1);
      $player->updated_at = $dt->toDateTimeString();
      $player->save();
      Log::info("USER ID: ". $u_id ." entered the queue");
      return View::make('layouts.player.queue');
    }

    /**
     * Displays the admin page view of a trial, including all the players
     * that have been assigned to it.
     *
     */
    public function getTrial($id)
    {

      $trial = Trial::with('users')->find($id);
      $curr_round = $trial->curr_round;

      $server_time = time();
      if($curr_round > 0) {
        $start_time = strtotime(
                      $trial->rounds[$curr_round - 1]
                      ->updated_at);
      }
      else {
        $start_time = 'Trial has not begun yet';
      }



      return View::make('layouts.admin.trial-view')
                  ->with('trial', $trial)
                  ->with('curr_server_time', $server_time)
                  ->with('start_time', $start_time);
    }

    /**
     * Displays the edit config page of a trial. Checks if
     * trial is in progress.
     *
     */
    public function editTrial($id)
    {

      $trial = Trial::where('id', $id)
                    ->with('rounds')
                    ->with('groups')
                    ->first();

      $factoidsets = \oceler\Factoidset::lists('name', 'id');
      $networks = \oceler\Network::lists('name', 'id');
      $namesets = \oceler\Nameset::lists('name', 'id');


      $in_progress = DB::table('trial_user')
                         ->where('trial_id', $id)
                         ->first();

      return View::make('layouts.admin.trial-config')
                  ->with('trial', $trial)
                  ->with('factoidsets', $factoidsets)
                  ->with('networks', $networks)
                  ->with('namesets', $namesets)
                  ->with('in_progress', $in_progress);

    }

    /**
     * Processes the New Trial form, saving all config
     * data to the appropriate tables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateTrial($id, Request $request)
    {
      $trial = Trial::find($id);
      $trial->distribution_interval = $request->distribution_interval;
      $trial->num_players = $request->num_players;
      $trial->mult_factoid = $request->mult_factoid || 0;
      $trial->pay_correct = $request->pay_correct || 0;
      $trial->pay_time_factor = $request->pay_time_factor || 0;
      $trial->payment_per_solution = $request->payment_per_solution;
      $trial->payment_base = $request->payment_base;
      $trial->num_rounds = $request->num_rounds;
      $trial->num_groups = $request->num_groups;

      $trial->save(); // Saves the trial to the trial table


      /*
       * For each trial round (set in the config), the trial timeout,
       * factoidsets, countrysets, and namesets (selected in the config)
       * are stored in the rounds table.
       */
      for($i = 0; $i < $trial->num_rounds; $i++){

        DB::table('rounds')->insert([
            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'trial_id' => $trial->id,
            'round' => ($i + 1),
            'round_timeout' => $request->round_timeout[$i],
            'factoidset_id' => $request->factoidset_id[$i],
            'nameset_id' => $request->nameset_id[$i],
            ]);
      }

      /*
       *	For each group (set in the config) store the group's
       *	network and end-of-experiment survey URL
       */
      for($i = 0; $i < $trial->num_groups; $i++){

        DB::table('groups')->insert([
          'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
          'group' => $i + 1,
          'trial_id' => $trial->id,
          'network_id' => $request->network[$i],
          'survey_url' => $request->survey_url[$i],
        ]);

      }

      return \Redirect::to('/admin/trial');
    }

    public function getListenAllTrialPlayers()
    {
      $trials = Trial::with('users')
                      ->where('is_active', 1)
                      ->get();

        for($i = 0; $i < count($trials); $i++){

          for($k = 0; $k < count($trials[$i]['users']); $k++){

            $solutions = \oceler\Solution::getCurrentSolutions(
                                                $trials[$i]['users'][$k]['id'],
                                                $trials[$i]['id'],
                                                $trials[$i]['curr_round']
                                            );
            $trials[$i]['users'][$k]['solutions'] = $solutions;

            // Get the user's node
            $group = DB::table('groups')
                        ->where('id', $trials[$i]['users'][$k]['pivot']['group_id'])
                        ->first();

            $network = DB::table('networks')
                            ->where('id', $group->network_id)
                            ->value('id');

            $u_node_id = DB::table('user_nodes')
                            ->where('user_id', $trials[$i]['users'][$k]['id'])
                            ->where('group_id', $group->id)
                            ->value('node_id');

            $u_node = DB::table('network_nodes')
                          ->where('id', '=', $u_node_id)
                          ->value('node');
            $trials[$i]['users'][$k]['node'] = $u_node;
          }

      }

      return Response::json($trials);
    }

    public function getListenTrialPlayers($id)
    {
      $trial = Trial::with('users')
                      ->find($id);


      for($k = 0; $k < count($trial['users']); $k++){

        $solutions = \oceler\Solution::getCurrentSolutions(
                                            $trial['users'][$k]['id'],
                                            $trial['id'],
                                            $trial['curr_round']
                                        );
        $trial['users'][$k]['solutions'] = $solutions;

        // Get the user's node
        $group = DB::table('groups')
                    ->where('id', $trial['users'][$k]['pivot']['group_id'])
                    ->first();

        $network = DB::table('networks')
                        ->where('id', $group->network_id)
                        ->value('id');

        $u_node_id = DB::table('user_nodes')
                        ->where('user_id', $trial['users'][$k]['id'])
                        ->where('group_id', $group->id)
                        ->value('node_id');

        $u_node = DB::table('network_nodes')
                      ->where('id', '=', $u_node_id)
                      ->value('node');
        $trial['users'][$k]['node'] = $u_node;
      }

      return Response::json($trial);
    }

    /**
     * Manages the queue of players waiting to join an avaialable trial.
     * @return 0 when the required number of players for that trial is met.
     *         Otherwise, if there is an available trial it returns the
     *         remaining number of players needed before the trial can start.
     *         If no trial is available, returns -1.
     */
    public function queue()
    {

      $u_id = Auth::user()->id;
      $last_trial_type = Auth::user()->lastTrialType();
      $dt = \Carbon\Carbon::now();

      // If this user has been added to trial_user already, just return with 0
      if(DB::table('trial_user')->where('user_id', '=', $u_id)->get()) return 0;

      // Add the player to the queue and set updated_at to
      // current date/time
      $player = \oceler\Queue::firstOrNew(['user_id' => $u_id]);
      $player->trial_type = ($last_trial_type + 1);
      $player->updated_at = $dt->toDateTimeString();
      $player->save();

      // Then, delete all players who have been inactive for INACTIVE_TIME
      $INACTIVE_TIME = 6; // In seconds
      \oceler\Queue::where('updated_at', '<', $dt->subSeconds($INACTIVE_TIME))->delete();

      // Get all trials that are active but already have been filled
      // by querying the trial_user table
      $running_trials = DB::table('trial_user')
                         ->get();

      $filled_trials = [];
      foreach ($running_trials as $t) {
        $filled_trials[] = $t->trial_id;
      }

      // Get the oldest active, not-already-filled trial
      // the player qualifies for
      $trial = Trial::where('is_active', 1)
                    ->where('trial_type', '=', $player->trial_type)
                    ->whereNotIn('id', $filled_trials)
                    ->orderBy('created_at', 'asc')
                    ->first();

      // If such a trial exists, see if the # of players in the queue
      // is equal to the required # of players for the trial
      if(!$trial){
        return -1;
      }

      $queued_players = \oceler\Queue::where('trial_type', '=', $player->trial_type)
                                      ->count();

      // If there are enough players...
      if($queued_players >= $trial->num_players){

        // ... Take the required amount
        $selected = \oceler\Queue::where('trial_type', '=', $player->trial_type)
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
          \oceler\Queue::where('user_id', '=', $user->user_id)->delete();
        }

          return 0;
        }

        else return $trial->num_players - $queued_players;

    }

    public function trialStopped()
    {
      $trial_user = DB::table('trial_user')
                  ->where('user_id', '=', Auth::user()->id)
                  ->orderBy('updated_at', 'desc')
                  ->get();

      foreach ($trial_user as $key => $t_u) {
        $trial = Trial::find($t_u->trial_id);
        $trial->stopTrial();
      }

      Log::info('USER ID'. Auth::user()->id .' was taken to the trial stopped page');
      return View::make('layouts.player.trial-stopped');
    }

    /**
     * Returns true if number of players in trial who have
     * finished reading the instructions matches the total
     * number of players in that trial.
     * @return boolean
     */
    public function instructionsStatus($trial_id)
    {

      $trial = Trial::with('users')->find($trial_id);

      $num_read = 0;

      $INACTIVE_PING_TIME = 20;
      $dt = \Carbon\Carbon::now();

      $hasReadInstructions = false;

      foreach ($trial->users as $user) {
        if($user->id == \Auth::user()->id) {
          $hasReadInstructions = ($user->pivot->instructions_read == true) ? true : false;
          if($user->pivot->selected_for_removal == 1) {
            return Response::json(['status' => 'remove']);
          }
          $trial->users()->updateExistingPivot($user->id, ['last_ping' => date("Y-m-d H:i:s")]);
        }
        if(($user->pivot->instructions_read == true) &&
           (!$user->pivot->selected_for_removal) &&
           ($user->pivot->last_ping > $dt->subSeconds($INACTIVE_PING_TIME))) {
            $num_read++;
          }

      }


      if($num_read >= $trial->num_players) {
        if($hasReadInstructions) {
          return Response::json(['status' => 'ready']);
        }
        else {
          return Response::json(['status' => 'remove']);
        }
      }
      else {
        return Response::json(['status' => 'waiting', 'num_completed' => $num_read, 'num_needed' => $trial->num_players]);
      }
    }

    public function markInstructionsAsRead($user_id)
    {
      Log::info("USER ID ". $user_id ." marked instructions as read");
      DB::update('update trial_user set instructions_read = 1 where user_id = ?', [$user_id]);

    }

    public function notSelectedForTrial($trial_id) {
      $trial = Trial::with('users')->find($trial_id);
      $trial->removePlayerFromTrial(\Auth::user()->id, false, false);
      return redirect('/player/end-task/overrecruited');
    }


    public function manageQueue() {

      // Delete any inactive users from Queue
      $this->deleteInactiveQueueUsers();

      // Get all trials that are active but already have been filled
      // by querying the trial_user table
     $active_trials = Trial::has('users', '>', 0)
                           ->where('is_active', true)
                           ->with('users')
                           ->get();

      $filled_trials = [];
      foreach ($active_trials as $t) {
        $filled_trials[] = $t->id;

        // Process the instructions status of each running trial, if needed
        if($t->users()->sum('selected_for_removal') == 0 && count($t->users) > $t->num_players) {
            $this->selectPlayersForTrial($t);
        }
      }


      // Get all active, not-already-filled trials
      $trials = Trial::where('is_active', 1)
                      ->with('users')
                      ->whereNotIn('id', $filled_trials)
                      ->orderBy('created_at', 'asc')
                      ->get();



      // If no trials exist, return
      if(count($trials) == 0){
        echo 'There are no active trials with slots open.';
        return;
      }

      // For each active trial, see if the # of players in the queue
      // is equal to the required # of players for the trial
      foreach($trials as $trial) {
          // If no number to recruit is entered, use num_players
          $num_to_recruit = ($trial->num_to_recruit != '') ? $trial->num_to_recruit : $trial->num_players;
          echo 'Trial ' .$trial->name. ' (trial type ' .$trial->trial_type. ') needs ' .$num_to_recruit. ' players.<br><br>';

          $LAST_PING_TIME = 2; // How recent a ping must be for player to be chosen
          $dt = \Carbon\Carbon::now();
          $queued_players = Queue::where('trial_type', '=', $trial->trial_type)
                                 ->where('updated_at', '>=', $dt->subSeconds($LAST_PING_TIME))
                                 ->count();

          // If there aren't enough players for this trial type,
          // move on to the next one
          if($queued_players < $num_to_recruit){
            echo 'There are only ' .$queued_players. ' players with qualification type ' .$trial->trial_type. ' in the queue.<br><br>';
            continue;
          }
          $inTrialPreSelection = count($trial->users);
          Log::info('Selecting players for  '.$trial->id.' this trial currently has '.$inTrialPreSelection.' players in the trial_user table');

          // Otherwise, take the required amount
          $selected = Queue::where('trial_type', '=', $trial->trial_type)
                           ->where('updated_at', '>=', $dt->subSeconds($LAST_PING_TIME))
                           ->orderBy('created_at', 'asc')
                           ->take($num_to_recruit)
                           ->get();

          Log::info('Moving ' .$selected->count(). ' players into trial: ' .$trial->name);


          // Shuffle the collection of selected players so that
          // their network node positions will essentially
          // be randomized
          $selected = $selected->shuffle();

          // Insert each selected player into the trial_user table
          // along with the group they are part of

          $group = 1;
          $count = 0; // Counts the users added so far
          foreach ($selected as $user) {
              Log::info('Inserting into trial_user user: '.$user->user_id.' trial: '.$trial->id);
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

              Log::info("Moved ". $user->user_id ." into trial ". $trial->id);
              if($count >= $num_to_recruit / $trial->num_groups) $group++;
              // ... And delete that user from the queue
              Log::info("Moved USER ID ". $user->user_id ." into trial ". $trial->id);
              \oceler\Log::trialLog($trial->id, "Moved USER ID ". $user->user_id ." into trial");
              Queue::where('user_id', '=', $user->user_id)->delete();
              Log::info("Removed USER ID ". $user->user_id ." from queue");
          }
          $inTrialPostSelection = DB::table('trial_user')->where('trial_id', $trial->id)->count();
          Log::info("Moved ". $count ." players into trial ". $trial->id.'. Total now in trial: '.$inTrialPostSelection);
      }
    }

    /*
      Selects players (equal to trial->num_players) form an overrecruited trial.
     */
    private function selectPlayersForTrial($trial) {
      // Get all players who have read the instructions
      $activePlayers = $trial->users()->wherePivot('instructions_read', true)->get();
      $toRemove = count($activePlayers) - $trial->num_players;
      if($toRemove > 0) {
        $selectedPlayers = $activePlayers->random($toRemove);
        Log::info($toRemove." more players than needed for trial ".$trial->id);
        foreach($selectedPlayers as $player) {
          Log::info("Selected for removal: ".$player->id);
          \DB::table('trial_user')->where('user_id', $player->id)->update(['selected_for_removal' => true]);
        }
      }
    }

    private function deleteInactiveQueueUsers()
    {
        $INACTIVE_QUEUE_TIME = 6;
        $dt = \Carbon\Carbon::now();
        $toDelete = Queue::where('updated_at', '<', $dt->subSeconds($INACTIVE_QUEUE_TIME))->lists('user_id')->toArray();
        if(count($toDelete) > 0) {
          Log::info("Deleting from the Queue due to inactivity: ". implode(',', $toDelete));
          Queue::whereIn('user_id', $toDelete)->delete();
        }
    }

    public function testQueueManager()
    {
      $users = \oceler\User::where('id', '<', 6)->get();
      foreach($users as $user) {
        $player = \oceler\Queue::firstOrNew(['user_id' => $user->id]);
        $player->trial_type = (rand(1, 2));
        $player->save();
      }
      $queued_players = Queue::get()->count();
      dump("There are ".$queued_players." in the queue.");
      $trial_users = DB::table('trial_user')->get();
      dump("There are ". count($trial_users). " placed in trials.");

      for($i = 0; $i < 3; $i++) {
        $trial = new Trial;
        $trial->name = 'QUEUE MANAGER TEST '.$i;
        $trial->trial_type = rand(1, 2);
        $trial->num_players = rand(2, 4);
        $trial->is_active = 1;
        $trial->num_groups = 1;
        $trial->save();

        $group = new \oceler\Group;
        $group->trial_id = $trial->id;
        $group->network_id = 2;
        $group->group = 1;
        $group->save();
      }
    }

    public function testHitProcess()
    {
      $active_players = DB::table('trial_user')->lists('user_id');
      $hits = \oceler\MturkHit::whereNotIn('user_id', $active_players)
                               ->where('hit_processed', '=', 0)
                               ->where('trial_id', '>', 0)
                               ->orWhere('trial_id', '=', -1)
                               //=->whereRaw('(trial_id > 0 OR trial_id = -1)')
                               ->whereNotIn('user_id', $active_players)
                               ->where('hit_processed', '=', 0)
                               ->get();
      dump($hits);
    }
}
