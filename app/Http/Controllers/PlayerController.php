<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;
use View;
use Auth;
use DB;
use Response;
use Session;
use oceler\Solution;
use oceler\SolutionCategory;
use Log;

class PlayerController extends Controller
{
    public function home()
    {
      return View::make('layouts.player.home');
    }

    /**
     * Checks if the player is still waiting in the trial queue.
     * If so, updates the time, so the Queue Manager knows they are
     * still active.
     * If not, checks if they are in an active trial.
     * @return [type] [description]
     */
    public function queueStatus()
    {
      $queuedPlayer = \oceler\Queue::where('user_id', Auth::user()->id)->first();

      if($queuedPlayer) {
        $queuedPlayer->updated_at = date("Y-m-d H:i:s");
        $queuedPlayer->save();
        return 1;
      }

      $player = \oceler\User::with('trials')->find(Auth::user()->id);
      if(count($player->trials) > 0) {
        return 0;
      }
    }

    /**
     * Loads the trial game screen for the player, including the solution
     * categories and network.
     */
    public function playerTrial()
    {
    	/**
    	* Let me take a moment to explain networks. The connections between users
    	*   are stored within a network of nodes. Each user is assigned a node.
    	*   Each node can serve as a source or a target to other nodes. A source is
    	*   a player whose information can be seen by the target. Connections can
    	*   be bi-directional (player A can see player B's information, and player B
    	*   can see Player A's information) or uni-directional (player A can see
    	*   player B's information, but player B CAN'T see player A's information).
    	*
    	* Below, we find the network relationships to the current user, and store
    	*   the players the user can see in one array ($players_from) and the players
    	*   that can see the user in another array ($players_to).
    	*/

    	// Get the user's ID, , the trial ID, the ID of the network, and the
    	// user's network node
    	$u_id = Auth::id();

      $trial_user = DB::table('trial_user')->where('user_id', Auth::id())->first();

      Log::info("USER ID ". $u_id ." is loading main trial page");
      /* If the user is not currently assigned to a trial
      send them to their home screen */
      if(!$trial_user){
        Log::info("USER ID ". $u_id ." something went wrong! User not found in trial_user table!");
        return \Redirect::to('/player/trial/queue');
      }

      $curr_round = Session::get('curr_round');
      if(!$curr_round) $curr_round = 1;

      Log::info("USER ID ". $u_id ." is loading trial, group, and network info");
      $trial = \oceler\Trial::where('id', $trial_user->trial_id)
                            ->with('rounds')
                            ->first();

      // Used for the javascript trial timer
      $server_time = time();
      $start_time = strtotime(
                    $trial->rounds[($curr_round - 1)]
                    ->start_time);

      $group = DB::table('groups')
                  ->where('id', $trial_user->group_id)
                  ->first();

    	$network = DB::table('networks')
                      ->where('id', $group->network_id)
                      ->value('id');

    	$u_node_id = DB::table('user_nodes')
                      ->where('user_id', $u_id)
                      ->where('group_id', $group->id)
                      ->value('node_id');

      // If they haven't been assigned a node for some reason, send them to initialize the trial
      if(!$u_node_id) {
        Log::info("USER ID ". $u_id ." did not initialize trial yet!");
        return redirect('/player/trial/initialize');
      }

      $u_node = DB::table('network_nodes')
                    ->where('id', '=', $u_node_id)
                    ->value('node');

      $names = array();

      // Load the player names for each round
      foreach ($trial->rounds as $round) {
        $nameset = DB::table('names')
                      ->where('nameset_id', $round->nameset_id)
                      ->get();

        foreach($nameset as $name){
          $names[$round->round][] = $name->name;
        }
      }

      Log::info("USER ID ". $u_id ." is connecting to network");
    	// Get each player that is in the same session as the user
    	$session_players = DB::table('trial_user')
                            ->where('trial_id', $trial->id)
                            ->where('group_id', $group->id)
                            ->where('instructions_read', true)
                            ->where('selected_for_removal', false)
                            ->get();

    	// Create two arrays -- one to hold the players the user can see,
    	// and another to hold the players that can see the user
    	$players_from = array();
    	$players_to = array();

      $nodes = array();

    	// Then, loop through the players in the session
    	foreach ($session_players as $key => $session_player) {

        $player = \oceler\User::find($session_player->user_id);

    		// Get the network node for this player
    		$node_id = DB::table('user_nodes')
                    ->where('user_id', '=', $player->id)
                    ->where('group_id', $group->id)
                    ->value('node_id');

        $node = DB::table('network_nodes')
                    ->where('id', '=', $node_id)
                    ->value('node');

        $nodes[$player->id] = $node;

    		// See if their node is a source where the user's node is a target
    		$from = DB::table('network_edges')
                    ->where('network_id', '=', $network)
                    ->where('source', '=', $node)
                    ->where('target', '=', $u_node)
                    ->value('source');

    		// See if their node is a target where the user's node is a source
    		$to = DB::table('network_edges')
                  ->where('network_id', '=', $network)
                  ->where('target', '=', $node)
                  ->where('source', '=', $u_node)
                  ->value('target');

    		// If they are a source (e.g. the user can see this player),
    		// add them to the $players_from array
    		if($from && $from != $u_node) $players_from[] = $player;

    		// If they are a target (e.g., this player can see the user),
    		// add them to the $players_to array
    		if($to && $to != $u_node) $players_to[] = $player;

    	}

      // We build an array of user IDs for each player
      //  the user can see (including themselves)
      //  to use in queries for Solutions and Messages
      $players_from_ids[] = Auth::user()->id;

      foreach ($players_from as $player) {
        $players_from_ids[] = $player->id;
      }

      // Store the player id's in Session so we can access them later
      // for the message functions
      Session::put('players_from_ids', $players_from_ids);

      Log::info("USER ID ". $u_id ." is loading solution categories");
    	/*
    	* Solution categories are stored in the DB. This makes it
    	*  possible to support different sessions
    	*  having different solution categories.
    	*/
    	$solution_categories = \oceler\Solution::getSolutionCategories(
                              $trial->rounds[$curr_round - 1]->factoidset_id);

      $factoidset = \oceler\Factoidset::
                          where('id', $trial->rounds[$curr_round - 1]->factoidset_id)
                          ->first();

      // And for the datepicker in the solutions entry form,
      // we'll call a helper function to get an array of months and minutes
      $datetime = Solution::dateTimeArray();

      Log::info("USER ID ". $u_id ." is finished loading trial. Round ". $curr_round . " is now starting");
    	// Finally, we generate the page, passing the user's id,
    	// the players_from and players_to arrays and the
    	// solution categories array
    	return View::make('layouts.player.main')
                   ->with('user', Auth::user())
                   ->with('curr_round', $curr_round)
                   ->with('trial', $trial)
                   ->with('players_from', $players_from)
                   ->with('players_to', $players_to)
                   ->with('solutions_display_name', $factoidset->solutions_display_name)
                   ->with('system_msg_name', $factoidset->system_msg_name)
                   ->with('solution_categories', $solution_categories)
                   ->with('minutes', $datetime['minutes'])
                   ->with('months', $datetime['months'])
                   ->with('names', $names)
                   ->with('nodes', $nodes)
                   ->with('user_node', $u_node)
                   ->with('server_time', $server_time)
                   ->with('start_time', $start_time);
    }


    public function startTrialRound()
    {
      $curr_round = Session::get('curr_round') + 1;
      Session::put('curr_round', $curr_round);

      $trial = \oceler\Trial::where('id', Session::get('trial_id'))
                            ->with('rounds')
                            ->first();

      $trial->curr_round = $curr_round;
      $trial->save();
      Log::info("USER ID ". Auth::user()->id ." is loading new round: ". $curr_round ." in trial ". $trial->id);
      // Update the start time for this round
      $dt = \Carbon\Carbon::now()->toDateTimeString();
      $trial->rounds[$curr_round - 1]->updated_at = $dt;
      // If the start time hasn't been recorded yet, do so
      if(is_null($trial->rounds[$curr_round - 1]->start_time)) {
        $trial->rounds[$curr_round - 1]->start_time = $dt;
      }
      $trial->rounds[$curr_round - 1]->save();

      return redirect('/player/trial');
    }

    public function endTrialRound()
    {
      $user = Auth::user();
      $curr_round = Session::get('curr_round');

      $trial_user = DB::table('trial_user')->where('user_id', Auth::id())->first();
      Log::info("USER ID ". Auth::user()->id ." has ended round: ". $curr_round ." in trial ". $trial_user->trial_id);
      \oceler\Log::trialLog($trial_user->trial_id, "USER ID ". Auth::user()->id ." has ended round: ". $curr_round);
      $group = DB::table('groups')
                  ->where('id', $trial_user->group_id)
                  ->first();

      $trial = \oceler\Trial::where('id', '=', $trial_user->trial_id)
                            ->with('rounds')
                            ->with('users')
                            ->with('solutions')
                            ->first();

      $check_solutions = \oceler\Solution::checkSolutions($user, $trial, $curr_round);

      $num_correct = 0;
      $time_correct = 0;
      foreach ($check_solutions as $key => $check) {
        if($check['is_correct']) $num_correct++;
        $time_correct += $check['time_correct'];
      }

      $amt_earned = 0;

      if($trial->pay_correct){
        if($trial->pay_time_factor){
          $amt_earned = $time_correct / 60 * $trial->payment_per_solution;
          }

        else {
          $amt_earned = $num_correct * $trial->payment_per_solution;
        }
      }

      $MAX_PAYMENT = env('MAX_PAYMENT', '15.00');
      if($amt_earned > $MAX_PAYMENT) {
        $amt_earned = $MAX_PAYMENT;
      }

    // Add the earnings for this round to the round_earnings table

    $dt = \Carbon\Carbon::now()->toDateTimeString();
    $sql = DB::statement('
                          INSERT IGNORE INTO `round_earnings`
                            (`created_at`, `updated_at`, `trial_id`, `user_id`,
                            `round_id`, `earnings`, `num_correct`, `tot_categories`)
                            VALUES
                            ("'.$dt.'","'.$dt.'",'.$trial->id.', '.$user->id.',
                            '.$trial->rounds[$curr_round - 1]->id.',
                            '.$amt_earned.', '.$num_correct.',
                            '.count($check_solutions).');');

    return View::make('layouts.player.end-round')
                ->with('trial', $trial)
                ->with('curr_round', $curr_round)
                ->with('check_solutions', $check_solutions)
                ->with('num_correct', $num_correct)
                ->with('amt_earned', $amt_earned)
                ->with('group', $group);
    }

    /**
     * Redirects the player to a survey URL
     */

    public function getContinueSurvey()
    {
      $trial_user = DB::table('trial_user')->where('user_id', Auth::id())->first();
      $group = DB::table('groups')
                  ->where('id', $trial_user->group_id)
                  ->first();

      Log::info('USER ID '. Auth::user()->id ." is being directed to survey");
      return View::make('layouts.player.continue-survey')
                  ->with('group', $group)
                  ->with('mturk_id', Auth::user()->mturk_id);
    }

    public function showPostTrialSurvey()
    {
      $trial_user = DB::table('trial_user')->where('user_id', Auth::id())->first();

      $trial = \oceler\Trial::where('id', $trial_user->trial_id)
                            ->with('users')
                            ->first();

      Log::info('USER ID '. Auth::user()->id ." is viewing the survey");
      return View::make('layouts.player.post-trial-survey')
                  ->with('trial_type', $trial->trial_type)
                  ->with('trial_id', $trial->id);
    }


    public function postInitialSurvey(Request $request)
    {

      Log::info('USER ID '. Auth::user()->id ." has completed the survey");
      DB::table('initial_survey')->insert([
          'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
          'user_id' => Auth::id(),
          'trial_id' => $request->trial_id,
          'understand' => $request->understand,
          'confident' => $request->confident,
          'email' => $request->email,
          'comments' => $request->comments
        ]);
        // If we're testing, don't continue
        if($request->server('HTTP_REFERER') == 'http://oceler.loc/initial-post-trial-survey'
           || $request->server('HTTP_REFERER') == 'http://netlabexperiments.org/initial-post-trial-survey'){
          echo 'TESTING :: SUBMITTED. This message will not display during actual trial.';
          return;
        }
        return \Redirect::to('/player/trial/end');
    }

    public function postTrialSurvey(Request $request)
    {

      Log::info('USER ID '. Auth::user()->id ." has completed the survey");
      DB::table('post_trial_survey')->insert([
          'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
          'user_id' => Auth::id(),
          'trial_id' => $request->trial_id,
          'enjoy' => $request->enjoy,
          'confident' => $request->confident,
          'comments' => $request->comments
        ]);
        // If we're testing, don't continue
        if($request->server('HTTP_REFERER') == 'http://oceler.loc/post-trial-survey'
          || $request->server('HTTP_REFERER') == 'http://netlabexperiments.org/post-trial-survey'){
          echo 'TESTING :: SUBMITTED. This message will not display during actual trial.';
          return;
        }
        return \Redirect::to('/player/trial/end');
    }

    /**
     * Removes the player from the trial, marks them as
     * having completed the trial, and displays the
     * end-trial page.
     */
    public function endTrial()
    {
      $trial_user = DB::table('trial_user')->where('user_id', Auth::id())->first();

      $trial = \oceler\Trial::where('id', $trial_user->trial_id)
                            ->with('users')
                            ->first();

      $group = DB::table('groups')
                  ->where('id', $trial_user->group_id)
                  ->first();

      Log::info("USER ID ". Auth::id() ." has reached the end of trial ". $trial_user->trial_id);
      \oceler\Log::trialLog($trial_user->trial_id, "USER ID ". Auth::id() ." has reached the end of trial");
      // Calculate the player's earnings
      $round_data = DB::table('round_earnings')
                          ->where('trial_id', '=', $trial->id)
                          ->where('user_id', '=', Auth::id())
                          ->get();

      // Calculate their earnings and check for each round
      // that they have scored >= to the passing score
      $round_earnings = 0;
      $passed_trial = true;
      foreach ($round_data as $round) {
        $round_earnings += $round->earnings;
        if(($round->num_correct / $round->tot_categories) < ($trial->passing_score / 100)){
          $passed_trial = false;
        }
      }

      $MAX_PAYMENT = env('MAX_PAYMENT', '15.00');
      if($round_earnings > $MAX_PAYMENT) {
        $round_earnings = $MAX_PAYMENT;
      }


      $total_earnings = ["bonus" => $round_earnings,
                         "bonus_reason" => "Bonus payment based on your performance.",
                         "base_pay" => $trial->payment_base];

      Log::info("USER ID ". Auth::id() ." payment calculated for trial ". $trial_user->trial_id);
     // If the user hasn't already been unassigned from the
     // trial by some other method, remove them here
      if($trial->users->contains(Auth::id())){
        // first bool indicates that they have completed the trial
        Log::info("USER ID ". Auth::id() ." is being removed from trial ". $trial_user->trial_id ." passed: ". $passed_trial);
        $trial->removePlayerFromTrial(Auth::id(), true, $passed_trial);
      }

      $mturk_hit = \oceler\MturkHit::where('assignment_id', '=', \Session::get('assignment_id'))
                                   ->where('worker_id', '=', Auth::user()->mturk_id)
                                   ->first();


      if($mturk_hit){

        Log::info("USER ID ". Auth::id() ." updating MTurk Hit table for ". $trial_user->trial_id ." assignment_id: ". $mturk_hit->assignment_id);
        $mturk_hit->trial_id = $trial->id;
        $mturk_hit->trial_type = $trial->trial_type;
        $mturk_hit->trial_completed = true;
        $mturk_hit->trial_passed = $passed_trial;
        $mturk_hit->bonus = $total_earnings['bonus'];
        $mturk_hit->save();

        $assignment_id = $mturk_hit->assignment_id;
        $submit_to = $mturk_hit->submit_to;
        $mturk_id = $mturk_hit->worker_id;
      }

      else{
        $assignment_id = false;
        $submit_to = false;
        $mturk_id = false;
      }

      Log::info("USER ID ". Auth::id() ." is viewing the end of trial page");
      return View::make('layouts.player.end-trial')
                  ->with('total_earnings', $total_earnings)
                  ->with('group', $group)
                  ->with('passed_trial', $passed_trial)
                  ->with('completed_trial', true)
                  ->with('assignment_id', $assignment_id)
                  ->with('submit_to', $submit_to)
                  ->with('mturk_id', $mturk_id);

    }

    public function endTask($reason)
    {

      $total_earnings = ["bonus" => env('NO_AVAILABLE_TRIAL_COMPENSATION', ''),
                         "bonus_reason" => "Compensation for your time spent waiting for other players to join.",
                         "base_pay" => 0];

      if(\Session::get('assignment_id')){

        $hit_data = \oceler\MturkHit::where('assignment_id', '=', \Session::get('assignment_id'))
                                     ->where('worker_id', '=', Auth::user()->mturk_id)
                                     ->first();

        $hit_data->trial_id = -1;
        $hit_data->trial_type = 0;
        $hit_data->trial_completed = false;
        $hit_data->trial_passed = false;
        $hit_data->bonus = $total_earnings['bonus'];
        $hit_data->save();
        $assignment_id = \Session::get('assignment_id');
        $submit_to = $hit_data->submit_to;
      }

      else {
        $assignment_id = false;
        $submit_to = false;
      }

      // If they have been assigned to a trial, remove them
      $trial_user = DB::table('trial_user')->where('user_id', Auth::id())->first();

      if($trial_user) {
        \oceler\Trial::find($trial_user->trial_id)->removePlayerFromTrial(Auth::id(), false, false);
      }

      switch($reason) {
        case 'timeout':
          $msg = 'There are no trials available at this time.';
          break;

        case 'overrecruited':
          $msg = 'You were not selected to participate in the trial.';
          break;

        default:
          $msg = 'The task is now over. Thank you for your participation.';
      }

      $logReason = 'task ending';
      if($reason == 'timeout'){
        $logReason = 'queue or instructions timed out';
      }

      Log::info("USER ID ". Auth::user()->id ." ending task due to: ". $logReason);

      return View::make('layouts.player.end-task')
                  ->with('total_earnings', $total_earnings)
                  ->with('assignment_id', $assignment_id)
                  ->with('passed_trial', false)
                  ->with('completed_trial', false)
                  ->with('submit_to', $submit_to)
                  ->with('msg', $msg);
    }

    public function trialStopped()
    {
      Log::info('USER ID'. Auth::user()->id .' was taken to the trial stopped page');
      return View::make('layouts.player.trial-stopped');
    }

    /**
    * Stores a new solution to the solutions table in the DB
    *
    */
  	public function  postSolution(Request $request)
  	{

  		$user = Auth::user();

      /* First, update the updated_at timestamp of the
        most recent previous solution (if any) that is
        the same category as the incoming $request solution.
        This way, by comparing the created_at and updated_at
        timestamps, we can determine the amount of time a solution
        was 'active' for.
      */
      $last_solution = Solution::where('user_id', $user->id)
                                ->where('trial_id', Session::get('trial_id'))
                                ->where('round', Session::get('curr_round'))
                                ->where('category_id', $request->category_id)
                                ->orderBy('id', 'desc')
                                ->first();

      if($last_solution) $last_solution->touch();

      // Then, store the new solution

      // If the solution is for the 'when' category, format the
      // date using each component
      if($request->month){
        $datetime = $request->month.' ';
        $datetime .= $request->day.' ';
        $datetime .= $request->hour.':';
        $datetime .= $request->min.' ';
        $datetime .= $request->ampm;

        $request->solution = $datetime;
      }

  		$sol = new Solution;
  		$sol->category_id = $request->category_id;
  		$sol->solution = $request->solution;
  		$sol->confidence = $request->confidence;
  		$sol->user_id = $user->id;
      $sol->trial_id = Session::get('trial_id');
      $sol->round = Session::get('curr_round');

  		$sol->save();

      // And log it
      $log = "SOLUTION-- FROM: ".$user->id." (". $user->player_name .") ";
      $log .= "CATEGORY: ".$sol->category_id;
      $log .= " SOLUTION: ".$sol->solution." CONFIDENCE: ".$sol->confidence;
      \oceler\Log::trialLog($sol->trial_id, $log);

  	}

    /*
      Pings the server - Checks that trial hasn't been stopped by admin;
      records last ping time (to determine if player is active);
      retrieves latest solutions and messages
     */
    public function ping($last_solution, $last_msg)
    {
      // First, check that the trial is still in progress (that it hasn't
      // been stopped). Return -1 if stopped
      $id =  Auth::id();
      $player = \oceler\User::where('id', Auth::id())->with('trials')->first();

      if(count($player->trials) == 0) return -1;

      // Update the last ping time for this user
      $player->trials[0]->pivot->last_ping = \Carbon\Carbon::now()
                                                ->toDateTimeString();
      $player->trials[0]->pivot->save();

      // Get latest solutions and messages
      $solutions = Solution::getLatestSolutions(Session::get('trial_id'),
                                                Session::get('curr_round'),
                                                $last_solution,
                                                Session::get('players_from_ids'));

      $messages = \oceler\Message::getNewMessages($player->id, $last_msg);

      $response = array("solutions" => $solutions, "messages" => $messages);
      return Response::json($response);

    }

    /**
     * Displays the instructions specific to the trial
     * that the player has been assigned to.
     */
    public function showInstructions()
    {
      $trial_id = DB::table('trial_user')->where('user_id', Auth::id())->pluck('trial_id');

      if(!$trial_id) return redirect('/player/trial/queue');

      $trial = \oceler\Trial::where('id', $trial_id)->first();
      Log::info("USER ID ". Auth::id() ." is viewing the instructions for trial ". $trial_id);
      return View::make('layouts.player.instructions')
                 ->with('trial', $trial);
    }

    /**
     * Runs at the start of a trial. Stores the curr_round as 1,
     * and sets the user's player_name for the trial.
     * Stores current round and trial ID in Session for access later.
     * @return View: A countdown for the start of the trial
     */
    public function initializeTrial()
    {
      $curr_round = 1;
      Session::put('curr_round', $curr_round);

      // Get the trial ID and the trial
      $trial_user = DB::table('trial_user')->where('user_id', Auth::id())->first();

      Log::info("USER ID ". Auth::user()->id ." intitializing trial ". $trial_user->trial_id);
      if(!$trial_user) {
        Log::info("USER ID ". Auth::user()->id ." failed to initialize! User not found in trial_user table!");
        return redirect('/player/trial/stopped');
      }
      Session::put('trial_user', $trial_user);
      Session::put('trial_id', $trial_user->trial_id);

      $trial = \oceler\Trial::where('id', '=', $trial_user->trial_id)
                            ->with('rounds')
                            ->first();

      $trial->curr_round = $curr_round;
      $trial->save();

      // Update the start time of the first round (used for the timer)
      // We add 5 seconds to account for the countdown before the trial begins
      $dt = \Carbon\Carbon::now()->addSeconds(5)->toDateTimeString();
      $trial->rounds[0]->updated_at = $dt;
      $trial->rounds[0]->save();

      // If the start time hasn't been recorded yet, do so
      if(is_null($trial->rounds[0]->start_time)) {
        $trial->rounds[0]->start_time = $dt;
        $trial->rounds[0]->save();
      }


      $group = DB::table('groups')
                  ->where('id', $trial_user->group_id)
                  ->first();

      // Get each player in the trial
      $session_players = DB::table('trial_user')
                              ->where('trial_id', '=', $trial->id)
                              ->where('group_id', $trial_user->group_id)
                              ->where('selected_for_removal', false)
                              ->where('instructions_read', true)
                              ->get();



      // If the player in the trial array is equal to this player
      // insert this user into user_node
      foreach ($session_players as $key => $player) {
        if($player->user_id == Auth::user()->id){
          DB::table('user_nodes')->insert([
              'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
              'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
              'user_id' => $player->user_id,
              'group_id' => $trial_user->group_id,
              'node_id' => DB::table('network_nodes')
                              ->where('network_id', $group->network_id)
                              ->where('node', $key + 1)
                              ->value('id')
            ]);

          // Find the name from the nameset that corrosponds with the
          // user's position in the trial array and set their player_name
          // in the User table
          $user = \oceler\User::find(Auth::user()->id);
          $names = DB::table('names')
                    ->where('nameset_id', '=', $trial->rounds[$curr_round - 1]->nameset_id)
                    ->lists('name');

          $user->player_name = $names[$key + 1];
          $user->save();
          Log::info("USER ID ". Auth::user()->id ." was assigned to node id ". ($key + 1));
        }
      }

      return View::make('layouts.player.initialize');

    }

    /**
     * If a player is redirected to an external survey, capture their return
     * via this method.
     */
    public function getMTurkSubmit($worker_id)
    {

      if(!Auth::check()){
        $user = \oceler\User::where('mturk_id', $worker_id)->first();
        /* Then we log them in, record the MTurk HIT data,
           and send them to the trial queue */
        $credentials = array(
          'email' => $user->email,
          'password' => '0c3134-MtU4k'
        );

        if(!Auth::attempt($credentials)) {
          return; // Should be an error page
        }
      }
      else $user = Auth::user();

      $trial_user = DB::table('trial_user')
                  ->where('user_id', '=', Auth::user()->id)
                  ->orderBy('updated_at', 'desc')
                  ->first();

      if(!$trial_user){
        $trial_user = DB::table('trial_user_archive')
                    ->where('user_id', '=', $user->id)
                    ->orderBy('updated_at', 'desc')
                    ->first();
      }

      $assignment_id = DB::table('mturk_hits')
                         ->where('user_id', '=', Auth::id())
                         ->where('trial_id', '=', $trial_user->trial_id)
                         ->pluck('assignment_id');

      Session::put('assignment_id', $assignment_id);
      Session::put('trial_user', $trial_user);

      return \Redirect::to('player/trial/end');

    }

    public function getMTurkLogin(Request $request)
    {

      if(!$request->assignmentId)
      {
        return;
      }

      /* If the user is just previewing the MTurk HIT the assignment id
       will not be available. Show a default page. */
      if($request->assignmentId == "ASSIGNMENT_ID_NOT_AVAILABLE"){
        return View::make('layouts.player.default');
      }

      $worker_id = $request->workerId;

      /* If the user accepts the HIT, we need to see if they are already
      in our database. */
      $user_id = \oceler\User::where('mturk_id', $worker_id)->pluck('id');

      //$user = Auth::loginUsingId($user_id);

      /* If there isn't already an account for this person,
         we create one (if there is an MTurk worker ID) */
      if(!$user_id && $worker_id) {
        $user = new \oceler\User();
        $user->name = "Mturk Worker";
        $user->email = $worker_id;
        $user->mturk_id = $worker_id;
        $user->password = \Hash::make('0c3134-MtU4k');
        $user->role_id = 3;
        $user->save();
        $user_id = $user->id;
        Log::info('Creating new user via MTurk. USER ID '. $user->id);
      }

      $user = Auth::loginUsingId($user_id);

      /* Log their IP and user agent. This happens
      automatically when users log in, but here we're
      logging them in manually */
      $user->ip_address = $_SERVER['REMOTE_ADDR'];
      $user->user_agent = $_SERVER['HTTP_USER_AGENT'];
      $user->save();

      // Record the hit data
      $hit_data = \oceler\MturkHit::firstOrNew(['worker_id' => $worker_id,
                                                'assignment_id' => $request->assignmentId,
                                                'hit_id' => $request->hitId]);
      $hit_data->user_id = $user->id;
      $hit_data->submit_to = $request->turkSubmitTo;
      $hit_data->unique_token = uniqid();
      $hit_data->save();
      Log::info("Adding a new record to mturk_hits : USER ID ". $user->id ."; From (if known) ". request()->server('HTTP_REFERER'));
      Session::put('assignment_id', $request->assignmentId);

      return \Redirect::to('player/trial/queue');
      //workerId=1234ABCD5678&assignmentId=987654321&hitId=321ZYX654&turkSubmitTo=https://workersandbox.mturk.com
  }

    /*
      Testing functions...
     */

    public function timerTest()
    {

      $server_time = \Carbon\Carbon::now(); // Used for the javascript trial timer
      $round_timeout = 1;

      print_r($_SERVER);
      return;
      return View::make('layouts.tests.timer-test')
                  ->with('server_time', $server_time)
                  ->with('round_timeout', $round_timeout);
    }

    public function isTrialStoppedTest()
    {

      $player = \oceler\User::with('trials')->find(Auth::id());
      dump($player);

      if(count($player->trials) == 0) echo -1;

      else echo 9;

    }

    public function dateDebug()
    {

      $server_time = date("m/d/y H:i:s");
      $server_time = time();
      echo '<pre>';
      print_r($_SERVER);
      echo '</pre>';

      echo '$server_time: '.$server_time.'<br>';

      $round_time = DB::table('rounds')->orderBy('updated_at', 'desc')->first();

      //$start_time = date("m/d/y H:i:s",strtotime($round_time->updated_at));
      $start_time = strtotime($round_time->updated_at);

      return View::make('layouts.tests.date-debug')
                  ->with('server_time', $server_time)
                  ->with('start_time', $start_time);
    }

    public function testMTurk()
    {

      $active_players = DB::table('trial_user')->lists('user_id');
      $hits = \oceler\MturkHit::whereNotIn('user_id', $active_players)
                               ->where('hit_processed', '=', 0)
                               ->where('trial_id', '>', 0)
                               ->orWhere('trial_id', '=', -1)
                               ->whereNotIn('user_id', $active_players)
                               ->where('hit_processed', '=', 0)
                               ->get();

      dump($active_players);
      dump($hits);

      $mturks = [];
      foreach ($hits as $key => $hit) {
        $mturks[$key] = new \oceler\MTurk\MTurk();
        $mturks[$key]->hit = $hit;
        $mturks[$key]->testConnection();
      }
    }

    public function testInitialPostTrialSurvey()
    {
      // For testing purposes, find a trial of type 1
      $trial = DB::table('trials')
                  ->where('trial_type', '=', 1)
                  ->first();
      $trial_type = 1;
      return View::make('layouts.player.post-trial-survey')
      ->with('trial_type', $trial->trial_type)
      ->with('trial_id', $trial->id);
    }

    public function testPostTrialSurvey()
    {
      // For testing purposes, find a trial of type 1
      $trial = DB::table('trials')
                  ->where('trial_type', '>', 1)
                  ->first();

      return View::make('layouts.player.post-trial-survey')
                  ->with('trial_type', $trial->trial_type)
                  ->with('trial_id', $trial->id);
    }

}
