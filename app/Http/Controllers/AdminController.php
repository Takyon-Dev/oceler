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
  public function showDashboard()
  {
    return View::make('layouts.admin.dashboard');
  }

  public function showTrials()
  {
    return View::make('layouts.admin.trials');
  }

  public function showTrialConfig()
  {
    return View::make('layouts.admin.trial-config');
  }

  public function postTrialConfig(Request $request)
  {
    $trial = new \oceler\Trial();
    $trial->distribution_interval = $request->distribution_interval;
    $trial->num_waves = $request->num_waves;
    $trial->num_players = $request->num_players;
    $trial->mult_factoid = $request->mult_factoid || 0;
    $trial->pay_correct = $request->pay_correct || 0;
    $trial->num_rounds = $request->num_rounds;

    $trial->save();

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
    //return View::make('layouts.admin.trial-config');
  }

  public function showConfigFiles()
  {
    return View::make('layouts.admin.config-files');
  }



}
