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

    $trial_id = \DB::table('trial_user')
                ->where('user_id', '=', $user->id)
                ->orderBy('updated_at', 'desc')
                ->value('trial_id');

    $curr_round = \Session::get('curr_round');

    $round_id = \DB::table('rounds')
                    ->where('trial_id', $trial_id)
                    ->where('round', $curr_round)
                    ->value('id');

    $factoidset = \DB::table('rounds')
                ->where('round', $curr_round)
                ->where('trial_id', $trial_id)
                ->value('factoidset_id');

    // Split the search input by space chars
    $search_terms = explode(' ', $request->search_term);

    $i = 0;
    $keyword = array();

    do{

      // Remove any non-alphanumeric chars from the search term
      $search_term = preg_replace("/[^A-Za-z0-9 ]/", '', $search_terms[$i]);

      $factoids = \DB::select(\DB::raw("
                            SELECT keywords.id, keywords.keyword,
                            factoid_keyword.factoid_id,
                            factoids.id, factoids.factoidset_id,
                            factoids.factoid
                            FROM keywords
                            JOIN factoid_keyword ON
                              factoid_keyword.keyword_id = keywords.id
                            JOIN factoids ON
                              factoid_keyword.factoid_id = factoids.id
                            WHERE keywords.keyword LIKE :search_term
                            AND factoids.factoidset_id = :factoidset_id
                            AND factoids.id NOT IN
                                (SELECT factoid_id
                                 FROM searches
                                 WHERE trial_id = :trial_id
                                 AND round_id = :round_id
                                 AND user_id = :user_id)
                    "), array('search_term' => $search_term,
                              'factoidset_id' => $factoidset,
                              'trial_id' => $trial_id,
                              'round_id' => $round_id,
                              'user_id' => $user->id));
      $i++;
    } while(count($factoids) == 0 && $i < count($search_terms));

    $result = array();

    if(count($factoids) == 0)
    {
      $result['success'] = false;
      $result['search_term'] = $request->search_term;
      $result['result'] = 'No results were found for your search \'' . $request->search_term . '\'';
      $result['factoid_id'] = null;
    }

    else {

      // Select one random factoid from the array of query results
      $factoid = $factoids[array_rand($factoids)];

      $result['success'] = true;
      $result['search_term'] = $request->search_term;
      $result['result'] = $factoid->factoid;
      $result['factoid_id'] = $factoid->factoid_id;
    }

    $search = new \oceler\Search();
    $search->user_id = $user->id;
    $search->trial_id = $trial_id;
    $search->round_id = $round_id;
    $search->search_term = $request->search_term;
    $search->factoid_id = $result['factoid_id'];
    $search->save();


    return \Response::json($result);

  }
}
