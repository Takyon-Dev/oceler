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

class PlayerController extends Controller
{
    public function home()
    {
      return View::make('layouts.player.home');
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

      $trial_user = DB::table('trial_user')
                  ->where('user_id', Auth::user()->id)
                  ->orderBy('updated_at', 'desc')
                  ->first();

      /* If the user is not currently assigned to a trial,
      or if for some reason the trial has not yet been inititalized
      (curr_round has not been set)
      send them to their home screen */
      if(!$trial_user || !\Session::get('curr_round')) return \Redirect::to('/player/');

      $curr_round = \Session::get('curr_round');

      $trial = \oceler\Trial::where('id', $trial_user->trial_id)
                            ->with('rounds')
                            ->first();

      $server_time = date("m/d/y H:i:s"); // Used for the javascript trial timer

      $start_time = date("m/d/y H:i:s",strtotime(
                    $trial->rounds[(Session::get('curr_round') - 1)]->updated_at));

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

    	// Get each player that is in the same session as the user
    	$session_players = DB::table('trial_user')
                              ->where('trial_id', $trial->id)
                              ->where('group_id', $group->id)
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
                  ->where('target', '=', $u_node)
                  ->where('source', '=', $node)
                  ->value('target');

    		// If they are a source (e.g. the user can see this player),
    		// add them to the $players_from array
    		if($from) $players_from[] = $player;

    		// If they are a target (e.g., this player can see the user),
    		// add them to the $players_to array
    		if($to) $players_to[] = $player;

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

    	/*
    	* Solution categories are stored in the DB. This makes it
    	*  possible to support different sessions
    	*  having different solution categories.
    	*/
    	$solution_categories = \oceler\Solution::getSolutionCategories(
                              $trial->rounds[$curr_round - 1]->factoidset_id);

      $factoidset = \oceler\Factoidset::
                          find($trial->rounds[$curr_round - 1]->factoidset_id)
                          ->first();

      // And for the datepicker in the solutions entry form,
      // we'll call a helper function to get an array of months and minutes
      $datetime = Solution::dateTimeArray();

    	// Finally, we generate the page, passing the user's id,
    	// the players_from and players_to arrays and the
    	// solution categories array
    	return View::make('layouts.player.main')
                   ->with('user', Auth::user())
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

      // Update the start time for this round
      $dt = \Carbon\Carbon::now()->toDateTimeString();
      $trial->rounds[$curr_round - 1]->updated_at = $dt;
      $trial->rounds[$curr_round - 1]->save();

      return redirect('/player/trial');
    }

    public function endTrialRound()
    {
      $user = Auth::user();
      $curr_round = Session::get('curr_round');

      $trial_user = DB::table('trial_user')
                  ->where('user_id', '=', Auth::user()->id)
                  ->orderBy('updated_at', 'desc')
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

    // Add the earnings for this round to the round_earnings table

    $dt = \Carbon\Carbon::now()->toDateTimeString();
    $sql = DB::statement('
                          INSERT IGNORE INTO `round_earnings`
                            (`created_at`, `updated_at`, `trial_id`, `user_id`,
                            `round_id`, `earnings`)
                            VALUES
                            ("'.$dt.'","'.$dt.'",'.$trial->id.', '.$user->id.',
                            '.$trial->rounds[$curr_round - 1]->id.',
                            '.$amt_earned.');');

    return View::make('layouts.player.end-round')
                ->with('trial', $trial)
                ->with('curr_round', $curr_round)
                ->with('check_solutions', $check_solutions)
                ->with('num_correct', $num_correct)
                ->with('amt_earned', $amt_earned);
    }

    /**
     * Removes the player from the trial, marks them as
     * having completed the trial, and displays the
     * end-trial page.
     */
    public function endTrial()
    {
      $trial_user = DB::table('trial_user')
                  ->where('user_id', '=', Auth::user()->id)
                  ->orderBy('updated_at', 'desc')
                  ->first();

      $group = DB::table('groups')
                  ->where('id', $trial_user->group_id)
                  ->first();

      // If the user hasn't already been unassigned from the
      // trial by some other method, remove them here
      $trial = \oceler\Trial::find(Session::get('trial_id'));
      if($trial->users->contains(Auth::id())){
        \oceler\Trial::removePlayerFromTrial(Auth::id(), true);
      }

      // If all users have left the trial, deactivate it
      if(count($trial->users) == 0){
        $trial->is_active = 0;
        $trial->save();
      }

      // Calculate the player's earnings
      $round_earnings = DB::table('round_earnings')
                          ->where('trial_id', '=', $trial->id)
                          ->where('user_id', '=', Auth::id())
                          ->sum('earnings');
      $total_earnings = $round_earnings + $trial->payment_base;

      return View::make('layouts.player.end-trial')
                  ->with('total_earnings', $total_earnings)
                  ->with('group', $group);
    }

    public function endTask()
    {

      /*
        Called when a trial ends early or a trial is not availble (after
        a timeout).
        Look up base payment, apply to user's MTurk account(?)
        and display it.
       */
      return View::make('layouts.player.end-task');
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
      $player = \oceler\User::with('trials')->find(Auth::id())->first();
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
     * Displays the insgtructions specific to the trial
     * that the player has been assigned to.
     */
    public function showInstructions()
    {



      $trial_id = DB::table('trial_user')->where('user_id', Auth::id())->pluck('trial_id');
      $trial = \oceler\Trial::where('id', $trial_id)->first();

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
      $trial_user = DB::table('trial_user')
                  ->where('user_id', '=', Auth::user()->id)
                  ->orderBy('updated_at', 'desc')
                  ->first();

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

      $group = DB::table('groups')
                  ->where('id', $trial_user->group_id)
                  ->first();

      // Get each player in the trial
      $session_players = DB::table('trial_user')
                              ->where('trial_id', '=', $trial->id)
                              ->where('group_id', $trial_user->group_id)
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
        }
      }

      return View::make('layouts.player.initialize');

    }

    public function getMTurkLogin(Request $request)
    {
      $worker_id = $request->workerId;

      /* If the user is just previewing the MTurk HIT the assignment id
       will not be available. Show a default page. */
      if($request->assignmentId == "ASSIGNMENT_ID_NOT_AVAILABLE"){
        return View::make('layouts.player.default');
      }

      /* If the user accepts the HIT, we need to see if they are already
      in our database. */
      $user = \oceler\User::where('mturk_id', $worker_id)->first();

      /* If there isn't already an account for this person,
         we create one (if there is an MTurk worker ID) */
      if(!$user && $worker_id) {
        $user = new \oceler\User();
        $user->name = "Mturk Worker";
        $user->email = $worker_id;
        $user->mturk_id = $worker_id;
        $user->password = \Hash::make('0c3134-MtU4k');
        $user->role_id = 3;
        $user->save();
      }

      /* Then we log them in, record the MTurk HIT data,
         and send them to the trial queue */
      $credentials = array(
        'email' => $user->email,
        'password' => '0c3134-MtU4k'
      );

      if(Auth::attempt($credentials)) {

        /* Log their IP and user agent. This happens
        automatically when users log in, but here we're
        logging them in manually */
        $user->ip_address = $_SERVER['REMOTE_ADDR'];
        $user->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $user->save();

        DB::table('mturk_hits')->insert([
            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'user_id' => $user->id,
            'hit_id' => $request->hitId,
            'assignment_id' => $request->assignmentId,
            'worker_id' => $worker_id,
            ]);

        return \Redirect::to('player/trial/queue');
      }

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


}
