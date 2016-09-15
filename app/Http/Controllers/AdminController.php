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

class AdminController extends Controller
{
  public function showPlayers()
  {
    $queued_players = \oceler\Queue::with('users')
                        ->get();
    $trials = \oceler\Trial::with('users')
                        ->with('solutions')
                        ->get();

    return View::make('layouts.admin.players')
                  ->with('queued_players', $queued_players)
                  ->with('trials', $trials);
  }

  public function getListenQueue()
	{
    $queued_players = \oceler\Queue::with('users')
                        ->get();

		return Response::json($queued_players);

	}


  public function showTrialConfig()
  {
    return View::make('layouts.admin.trial-config');
  }

  /**
   * Creates a new Trial based on the trial config form.
   */
  public function postTrialConfig(Request $request)
  {
    $trial = new \oceler\Trial();
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

      DB::table('trial_rounds')->insert([
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
    return Redirect::to('admin/trials');
  }

  public function showConfigFiles()
  {
    $factoidsets = \oceler\Factoidset::all();
    $networks = \oceler\Network::all();
    $namesets = \oceler\Nameset::all();

    return View::make('layouts.admin.config-files')
                ->with('factoidsets', $factoidsets)
                ->with('networks', $networks)
                ->with('namesets', $namesets);
  }

  public function showLogs()
  {

    $logs = array();
    $files = scandir(storage_path()."/logs/trial-logs/");

    $i = 0;
    foreach ($files as $f) {
      if($f != '.' && $f != '..'){

        $id = trim($f, "trial_.txt");

        $logs[$i]['log'] = $f;
        $logs[$i]['id'] = $id;
        $logs[$i]['name'] = \oceler\Trial::where('id', $id)
                                          ->value('name');
        $i++;
      }
    }

    return View::make('layouts.admin.logs')
                ->with('logs', $logs);

  }

  public function readLog($id)
  {
    $log = \File::get(storage_path()."/logs/trial-logs/trial_".$id.".txt");
    $response = Response::make($log, 200);
    return $response;
  }



}
