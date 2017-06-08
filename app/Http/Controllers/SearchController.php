<?php

namespace oceler\Http\Controllers;

use Illuminate\Http\Request;
use oceler\Http\Requests;
use oceler\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
  public function postSearch(Request $request)
  {

    $user = \Auth::user();

    $trial_id = \DB::table('trial_user')
                ->where('user_id', '=', $user->id)
                ->orderBy('updated_at', 'desc')
                ->value('trial_id');

    $trial = \oceler\Trial::find($trial_id);

    $curr_round = \Session::get('curr_round');

    $round = \DB::table('rounds')
                    ->where('trial_id', $trial_id)
                    ->where('round', $curr_round)
                    ->first();

    $factoidset = \DB::table('factoidsets')
                ->where('id', $round->factoidset_id)
                ->first();

    // Split the search input by space chars
    $search_terms = explode(' ', $request->search_term);

    $i = 0;
    $keyword = array();

    do{

      // Remove any non-alphanumeric chars from the search term
      $search_term = preg_replace("/[^A-Za-z0-9 ]/", '', $search_terms[$i]);

      $query = "
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
      AND factoids.factoidset_id = :factoidset_id_1
      AND factoids.id IN
          (SELECT factoid_distributions.factoid_id
           FROM factoid_distributions
           WHERE factoid_distributions.factoidset_id = :factoidset_id_2
           AND factoid_distributions.node = :searchable_node
           AND factoid_distributions.wave <= :wave)";

      $parameters = array('search_term' => $search_term,
                              'factoidset_id_1' => $factoidset->id,
                              'factoidset_id_2' => $factoidset->id,
                              'searchable_node' => $factoidset->searchable_node,
                              'wave' => $request->wave);

      if($trial->unique_factoids){

        // Also add factoids.id NOT IN previous searches

        $query .= "
        AND factoids.id NOT IN
            (SELECT factoid_id
             FROM searches
             WHERE trial_id = :trial_id
             AND round_id = :round_id
             AND user_id = :user_id
             AND factoid_id IS NOT NULL)";

             $parameters = array('search_term' => $search_term,
                                     'factoidset_id_1' => $factoidset->id,
                                     'factoidset_id_2' => $factoidset->id,
                                     'trial_id' => $trial_id,
                                     'round_id' => $round->id,
                                     'user_id' => $user->id,
                                     'searchable_node' => $factoidset->searchable_node,
                                     'wave' => $request->wave);
      }

      $factoids = \DB::select(\DB::raw($query), $parameters);
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
    $search->round_id = $round->id;
    $search->search_term = $request->search_term;
    $search->factoid_id = $result['factoid_id'];
    $search->save();

    $log = "SEARCH BY: ".$user->id." (".$user->player_name.") ";
    $log .= "SEARCH TERM: ".$search->search_term." RESULT: ".$result['result'];
    \oceler\Log::trialLog($trial_id, $log);

    return \Response::json($result);

  }

  function getSearchReload()
  {
    $user = \Auth::user();

    $trial_id = \DB::table('trial_user')
                ->where('user_id', '=', $user->id)
                ->orderBy('updated_at', 'desc')
                ->value('trial_id');

    $trial = \oceler\Trial::find($trial_id);

    $curr_round = \Session::get('curr_round');

    $round_id = \DB::table('rounds')
                    ->where('trial_id', $trial_id)
                    ->where('round', $curr_round)
                    ->value('id');

    $searches = \DB::table('searches')
                 ->where('user_id', '=', $user->id)
                 ->where('trial_id', '=', $trial_id)
                 ->where('round_id', '=', $round_id)
                 ->orderBy('id', 'asc')
                 ->get();

    $results = array();

    foreach ($searches as $search) {
      $result = array();
      $result['search_term'] = $search->search_term;
      if($search->factoid_id)
      {
        $result['success'] = true;
        $result['result'] = \DB::table('factoids')
                               ->where('id', $search->factoid_id)
                               ->pluck('factoid');
        $result['factoid_id'] = $search->factoid_id;
      }
      else {
        $result['success'] = false;
        $result['result'] = 'No results were found for your search \'' . $search->search_term . '\'';
      }
      $results[] = $result;
    }
    return $results;
  }

}
