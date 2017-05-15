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


    $logs = \oceler\Log::listAll();

    return View::make('layouts.admin.logs')
                ->with('logs', $logs);

  }

  public function readLog($id)
  {
    $log = new \oceler\Log($id);

    $fh = fopen($log['path'], 'r');
    $display = nl2br(fread($fh, 25000));

    return Response::make($display, 200);

  }

  public function downloadLog($id)
  {

    $log = new \oceler\Log($id);


    $headers = ['Content-type'=>'text/plain',
                'Content-Disposition'=>sprintf('attachment; filename="%s"', $log['name']),
                'Content-Length'=>sizeof($log['path'])];


    return Response::download($log['path'], $log['name'], $headers);
  }

  /* Retrieves player performance data, grouped by trial */
  public function getData()
  {
    $stats = [];
    $trials = \oceler\Trial::with('rounds')->get();

    foreach ($trials as $trial) {

      if(count($trial->rounds) <= 0) continue;
      $total_time = \Carbon\Carbon::parse($trial->updated_at)
                                  ->diffInMinutes(\Carbon\Carbon::parse(
                                  $trial->rounds[0]->updated_at));

      $factoidsets = DB::table('factoidsets')
                       ->whereIn('id', ($trial->rounds->pluck('factoidset_id')))
                       ->lists('name');

      $stats[$trial->id]['trial'] =
        array('name' => $trial->name,
        'num_players' => $trial->num_players,
        'factoidset' => $factoidsets,
        'start_time' => $trial->rounds[0]->updated_at,
        'total_time' => $total_time);
      $trial_users = DB::table('trial_user_archive')
                 ->where('trial_id', '=', $trial->id)
                 ->get();

      foreach ($trial_users as $trial_user) {


        $user = DB::table('users')
                       ->where('id', '=', $trial_user->user_id)
                       ->first();

        $round_earnings = DB::table('round_earnings')
                            ->where('trial_id', '=', $trial->id)
                            ->where('user_id', '=', $user->id)
                            ->sum('earnings');
        $total_earnings = $round_earnings + $trial->payment_base;



        $stats[$trial->id]['users'][$user->id] =
          array('worker_id' => $user->mturk_id,
                'last_ping' => $user->updated_at,
                'user_agent' => $user->user_agent,
                'ip_address' => $user->ip_address,
                'earnings' => $total_earnings);
      }
    }
    dump($stats);
    return View::make('layouts.admin.data')
                ->with('stats', $stats);

  }


}
