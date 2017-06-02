<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;
use \oceler\Trial;
use View;
use Auth;
use DB;
use Response;
use Session;
use Input;


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

      return \Redirect::to('/admin/trial');
    }

    /**
     * Displays the Trial Queue layout to the player
     * @return [type] [description]
     */
    public function enterQueue()
    {
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

      $curr_server_time = \Carbon\Carbon::now()->toDateTimeString();
      $curr_round = $trial->curr_round;
      $start_time = $trial->rounds[$curr_round - 1]->updated_at;


      return View::make('layouts.admin.trial-view')
                  ->with('trial', $trial)
                  ->with('curr_server_time', $curr_server_time)
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
      $dt = \Carbon\Carbon::now();

      // If this user has been added to trial_user already, just return with 0
      if(DB::table('trial_user')->where('user_id', '=', $u_id)->get()) return 0;

      // Add the player to the queue and set updated_at to
      // current date/time
      $player = \oceler\Queue::firstOrNew(['user_id' => $u_id]);
      $player->updated_at = $dt->toDateTimeString();
      $player->save();

      // Then, delete all players who have been inactive for INACTIVE_TIME
      $INACTIVE_TIME = 6; // In seconds
      \oceler\Queue::where('updated_at', '<', $dt->subSeconds($INACTIVE_TIME))->delete();

      // Get all trials that are active but already have been filled
      // by querying the trial_user table
      $filled_trials = DB::table('trial_user')
                         ->pluck('trial_id');
      dump($filled_trials);

      // Get the oldest active, not-already-filled trial
      /*
        THIS IS BROKEN _ NEEDS TO SUPPORT MULTIPLE ACTIVE TRIALS
       */
      $trial = Trial::where('is_active', 1)
                    ->whereNotIn('id', $filled_trials)
                    ->orderBy('created_at', 'asc')
                    ->first();


      // If such a trial exists, see if the # of players in the queue
      // is equal to the required # of players for the trial
      if(!$trial){
        return -1;
      }

      $queued_players = \oceler\Queue::count();

      // If there are enough players...
      if($queued_players >= $trial->num_players){

        // ... Take the required amount
        $selected = \oceler\Queue::orderBy('created_at', 'asc')
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

    public function queueTimeout()
    {
      return View::make('layouts.player.timeout');
    }

    public function trialStopped()
    {
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

      foreach ($trial->users as $user) {
        if($user->pivot->instructions_read == 1) $num_read++;
      }

      return Response::json($num_read == count($trial->users));
    }

    public function markInstructionsAsRead($user_id)
    {

      DB::update('update trial_user set instructions_read = 1 where user_id = ?', [$user_id]);

    }
}
