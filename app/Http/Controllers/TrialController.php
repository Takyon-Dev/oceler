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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $trials = Trial::all();

      return View::make('layouts.admin.trials')
                  ->with('trials', $trials);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      return View::make('layouts.admin.trial-config');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $trial = new Trial();
      $trial->distribution_interval = $request->distribution_interval;
      $trial->num_waves = $request->num_waves;
      $trial->num_players = $request->num_players;
      $trial->mult_factoid = $request->mult_factoid || 0;
      $trial->pay_correct = $request->pay_correct || 0;
      $trial->num_rounds = $request->num_rounds;
      $trial->is_active = false;

      $trial->save();

      /*
       * For each forund, the timeout factoidset, countryset, and
       * nameset are stored.
       */
      for($i = 0; $i < $trial->num_rounds; $i++){

        DB::table('rounds')->insert([
            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'trial_id' => $trial->id,
            'round' => ($i + 1),
            'round_timeout' => $request->round_timeout[$i],
            'factoidset_id' => $request->factoidset_id[$i],
            'countryset_id' => $request->countryset_id[$i],
            'nameset_id' => $request->nameset_id[$i],
            ]);
      }
      return \Redirect::to('/admin/trial');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
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

    public function toggle($id)
    {
      $trial = Trial::find($id);
      $trial->is_active = !$trial->is_active;
      $trial->save();
      return \Redirect::to('/admin/trial');
    }

    public function enterQueue()
    {
      return View::make('layouts.player.queue');
    }

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

            $solutions = \oceler\Solution::getCurrentSolutions($trials[$i]['users'][$k]['id'], $trials[$i]['id']);
            $trials[$i]['users'][$k]['solutions'] = $solutions;
          }

      }
      return Response::json($trials);
    }

    /**
     * Manages the queue of players waiting to join an avaialable trial.
     * @return True when the required number of players for that trial is met
     */
    public function queue()
    {
      $u_id = Auth::user()->id;
      $dt = \Carbon\Carbon::now();

      // If this user has been added to trial_user already, return with true
      if(DB::table('trial_user')->where('user_id', '=', $u_id)->get()) return 1;

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

        if($queued_players >= $trial->num_players){

          $selected = \oceler\Queue::orderBy('created_at', 'asc')
                                    ->take($trial->num_players)
                                    ->get();

          // Shuffle the collection of selected players so that
          // their network node positions will essentially
          // be randomized
          $selected = $selected->shuffle();

          foreach ($selected as $user) {
            DB::table('trial_user')->insert([
              'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
              'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
              'user_id' => $user->user_id,
              'trial_id' => $trial->id,
            ]);
            // Delete the user from queue
            \oceler\Queue::where('user_id', '=', $user->user_id)->delete();
          }

        }
      }
      return 0;
    }
}
