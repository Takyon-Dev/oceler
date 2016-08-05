<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;

class SearchController extends Controller
{
  public function postSearch(Request $request)
  {
    $user = \Auth::user();

    $curr_round = \Session::get('curr_round');

    $trial_id = \DB::table('trial_user')
                ->where('user_id', '=', $user->id)
                ->value('trial_id');

    $trial = \oceler\Trial::where('id', '=', $trial_id)
                          ->with('rounds')
                          ->first();


    return \Response::json($trial->rounds[$curr_round]->factoid_set);

    //return \Response::json(dd($trial_id));

  }
}
