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

    $cutoff_date = \Carbon\Carbon::now()->subDays(env('TRIALS_WITHIN_DAYS', ''))->toDateString();

    $trials = \oceler\Trial::with('users')
                            ->where('created_at', '>', $cutoff_date)
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
    $cutoff_date = \Carbon\Carbon::now()->subDays(env('TRIALS_WITHIN_DAYS', ''))->toDateString();
    $trials = \oceler\Trial::with('rounds')
                           ->where('created_at', '>', $cutoff_date)
                           ->orderBy('id', 'DESC')->get();

    foreach ($trials as $trial) {

      if(count($trial->rounds) <= 0) continue;

      $total_time = 0;

      foreach ($trial->rounds as $round) {
        $total_time += $round->round_timeout;
      }

      $factoidsets = DB::table('factoidsets')
                       ->whereIn('id', ($trial->rounds->pluck('factoidset_id')))
                       ->lists('name');

      $stats[$trial->id]['trial'] =
        array('name' => $trial->name,
        'num_players' => $trial->num_players,
        'base_pay'   => $trial->payment_base,
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

        $performance = DB::select("SELECT SUM(earnings) AS bonus,
                                  SUM(num_correct) AS correct,
                                  SUM(tot_categories) AS categories
                                  FROM round_earnings
                                  WHERE trial_id = ?
                                  AND user_id = ?", [$trial->id, $user->id]);


        $player_time = \Carbon\Carbon::parse($trial_user->last_ping)
                                    ->diffInMinutes(\Carbon\Carbon::parse(
                                    $trial->rounds[0]->updated_at));



        $stats[$trial->id]['users'][$user->id] =
          array('worker_id' => $user->mturk_id,
                'last_ping' => $trial_user->last_ping,
                'user_agent' => $user->user_agent,
                'ip_address' => $user->ip_address,
                'player_time' => $player_time,
                'completed_trial' => $trial_user->completed_trial,
                'passed_trial' => $trial_user->trial_passed,
                'num_correct' => $performance[0]->correct,
                'tot_categories' => $performance[0]->categories,
                'bonus' => $performance[0]->bonus
                );
      }
    }

    return View::make('layouts.admin.data')
                ->with('stats', $stats);

  }

  public function viewMturkLog()
  {
    $log = env('PATH_TO_PYSCRIPTS', '').'pyscripts/turk-connector.log';
    $handle = @fopen($log, "r");
    if ($handle) {
      while (($buffer = fgets($handle, 4096)) !== false) {
        echo nl2br($buffer);
      }
      fclose($handle);
    }


    //$fh = fopen($log, 'r');
    //$display = nl2br(fread($fh, 25000));

    /*
    return View::make('layouts.admin.mturk-log')
                ->with('log', $display);
    */
    //return \Response::make($display, 200);

  }


}
