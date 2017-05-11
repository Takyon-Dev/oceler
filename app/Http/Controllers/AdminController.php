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


}
