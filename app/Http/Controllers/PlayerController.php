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

    	// Get the user's ID, , the trial ID, the ID of the network, and the user's network node
    	$u_id = Auth::id();

      $curr_round = Session::get('curr_round');

      $trial_id = DB::table('trial_user')
                  ->where('user_id', '=', Auth::user()->id)
                  ->orderBy('updated_at', 'desc')
                  ->value('trial_id');

      $trial = \oceler\Trial::where('id', '=', $trial_id)
                            ->with('rounds')
                            ->first();

    	$network = DB::table('networks')
                      ->where('trial_id', '=', 1)
                      ->value('id');

    	$u_node_id = DB::table('user_nodes')
                      ->where('user_id', '=', $u_id)
                      ->value('node_id');

      $u_node = DB::table('network_nodes')
                    ->where('id', '=', $u_node_id)
                    ->value('node');

      $names = array();

      foreach ($trial->rounds as $round) {
        $nameset = DB::table('names')
                                  ->where('nameset_id', '=', $round->nameset_id)
                                  ->get();

        foreach($nameset as $name){
          $names[$round->round][] = $name->name;
        }
      }
    	// Get each player that is in the same session as the user
    	$session_players = DB::table('trial_user')
                              ->where('trial_id', '=', $trial_id)
                              ->get();

    	// Create two arrays -- one to hold the players the user can see, and another to hold the players that can see the user
    	$players_from = array();
    	$players_to = array();

      $nodes = array();

    	// Then, loop through the players in the session
    	foreach ($session_players as $key => $session_player) {

        $player = \oceler\User::find($session_player->user_id);

    		// Get the network node for this player
    		$node_id = DB::table('user_nodes')
                    ->where('user_id', '=', $player->id)
                    ->value('node_id');

        $node = DB::table('network_nodes')
                    ->where('id', '=', $node_id)
                    ->value('node');

        $nodes[$player->user_id] = $node;

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

    	/**
    	* Solution categories are stored in the DB. This makes it
    	*  possible to support different sessions
    	*  having different solution categories. At the moment,
    	*  all sessions use the same categories, so
    	*  we simply get them all in an array.
    	*/
    	$solution_categories = SolutionCategory::all();

    	Session::put('players_from', $players_from);
    	Session::put('players_to', $players_to);

    	// Finally, we generate the page, passing the user's id,
    	// the players_from and players_to arrays and the
    	// solution categories array
    	return View::make('layouts.player.main')
                   ->with('user', Auth::user())
                   ->with('trial', $trial)
                   ->with('players_from', $players_from)
                   ->with('players_to', $players_to)
                   ->with('solution_categories', $solution_categories)
                   ->with('names', $names)
                   ->with('nodes', $nodes)
                   ->with('curr_round', $curr_round);
    }

    public function startTrialRound()
    {
      $curr_round = Session::get('curr_round');
      Session::put('curr_round', $curr_round + 1);
      return redirect('/player/trial');
    }

    public function endTrialRound()
    {
      $user = Auth::user();
      $curr_round = Session::get('curr_round');

      $trial_id = DB::table('trial_user')
                  ->where('user_id', '=', Auth::user()->id)
                  ->orderBy('updated_at', 'desc')
                  ->value('trial_id');

      $trial = \oceler\Trial::where('id', '=', $trial_id)
                            ->with('rounds')
                            ->with('users')
                            ->with('solutions')
                            ->first();


      $solutions = \oceler\Solution::getCurrentSolutions($user->id, $trial_id);

      $check_solutions = \oceler\Solution::checkSolutions($solutions, $trial->rounds[$curr_round]->factoidset_id);

      $num_correct = 0;
      foreach ($check_solutions as $key => $check) {

        if($check[1]) $num_correct++;
      }
      $amt_earned = 0;
      if($trial->pay_correct) $amt_earned = $num_correct * .05;


      return View::make('layouts.player.end-round')
                  ->with('trial', $trial)
                  ->with('curr_round', $curr_round)
                  ->with('check_solutions', $check_solutions)
                  ->with('num_correct', $num_correct)
                  ->with('amt_earned', $amt_earned);
    }

    /**
    * Stores a new solution to the solutions table in the DB
    *
    */
  	public function  postSolution(Request $request)
  	{

  		$user = Auth::user();

  		$sol = new Solution;
  		$sol->category_id = $request->category_id;
  		$sol->solution = $request->solution;
  		$sol->confidence = $request->confidence;
  		$sol->user_id = $user->id;
      $sol->trial_id = Session::get('trial_id');

  		$sol->save();
  	}

      /**
      * Gets the most recent solutions for every player
      *  that is visible to the user
      *
      * @param solution_id	The id of the latest solution that was retrieved
      */
  	public function getListenSolution($solution_id)
  	{

  		// We build an array of user IDs for each player
  		//  the user can see (including themselves)
  		//  to use in our query

  		$ids[] = Auth::user()->id;

  		foreach (Session::get('players_from') as $player) {
  			$ids[] = $player->id;
  		}

  		// Get all solutions more recent than the last solution ID we have
  		$solutions = DB::table('solutions')
                      ->whereIn('user_id', $ids)
                      ->where('id', '>', $solution_id)
                      ->get();

  		return Response::json($solutions);

  	}

    /**
     * Runs at the start of a trial. Stores the curr_round as 1,
     * and sets the user's player_name for the trial.
     * @return View: A countdown for the start of the trial
     */
    public function initializeTrial()
    {
      $curr_round = 1;
      Session::put('curr_round', $curr_round);

      // Get the trial ID and the trial
      $trial_id = DB::table('trial_user')
                  ->where('user_id', '=', Auth::user()->id)
                  ->orderBy('updated_at', 'desc')
                  ->value('trial_id');

      Session::put('trial_id', $trial_id);

      $trial = \oceler\Trial::where('id', '=', $trial_id)
                            ->with('rounds')
                            ->get();

      // Get each player in the trial
      $session_players = DB::table('trial_user')
                              ->where('trial_id', '=', $trial_id)
                              ->get();

      // If the player in the trial array is equal to this player
      // insert this user into user_node
      foreach ($session_players as $key => $player) {
        if($player->user_id == Auth::user()->id){
          DB::table('user_nodes')->insert([
              'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
              'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
              'user_id' => $player->user_id,
              'node_id' => ($key + 1),
            ]);

          // Find the name form the nameset that corrosponds with the
          // user's position in the trial array and set their player_name
          $user = \oceler\User::find(Auth::user()->id);
          $names = DB::table('names')
                    ->where('nameset_id', '=', $trial[0]->rounds[$curr_round]->nameset_id)
                    ->lists('name');

          $user->player_name = $names[$key + 1];
          $user->save();
        }
      }

      return View::make('layouts.player.initialize');

    }

}
