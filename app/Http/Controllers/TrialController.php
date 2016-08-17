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
      $trial->distribution_interval = $request->distribution_interval;
      $trial->num_players = $request->num_players;
      $trial->mult_factoid = $request->mult_factoid || 0;
      $trial->pay_correct = $request->pay_correct || 0;
      $trial->num_rounds = $request->num_rounds;
      $trial->num_groups = $request->num_groups;
      $trial->is_active = false;

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

      $trial = Trial::find($id);
      $players = array();

      $players_in_trial = DB::table('trial_user')
                            ->where('trial_id', '=', $trial->id)
                            ->get();

      foreach ($players_in_trial as $key => $value) {
        $players[] = \oceler\User::find($value->user_id);
      }

      return View::make('layouts.admin.trial-view')
                  ->with('players', $players)
                  ->with('trial', $trial);
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
          }

      }
      return Response::json($trials);
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

      // If this user has been added to trial_user already, return with true
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
      $filled_trials = DB::table('trial_user')->select('trial_id')->get();

      // Get the oldest active, not-already-filled trial
      $trial = Trial::where('is_active', 1)
                    ->whereNotIn('id', $filled_trials)
                    ->orderBy('created_at', 'asc')
                    ->first();

      // If such a trial exists, see if the # of players in the queue
      // is equal to the required # of players for the trial
      if($trial){
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
      return -1;
    }
}
