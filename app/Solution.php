<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Solution extends Model
{
    public function trials()
    {
      return $this->belongsTo('oceler\Trial');
    }

    public static function getCurrentSolutions($user_id, $trial_id, $round)
    {

      $solutions = \DB::select(\DB::raw('
                      SELECT m.id, m.user_id, m.category_id, solution_categories.name, m.solution, m.confidence, m.created_at
                      FROM solutions AS m
                      JOIN solution_categories
                      ON m.category_id = solution_categories.id
                      INNER JOIN (
                          SELECT user_id, category_id,
                            MAX(created_at) AS mTime
                          FROM solutions
                          WHERE user_id = '.$user_id.'
                          AND trial_id = '.$trial_id.'
                          AND round = '.$round.'
                          GROUP BY user_id, category_id
                          )
                      AS d
                      ON m.user_id = d.user_id
                      AND m.category_id = d.category_id
                      AND m.created_at = d.mTime
                      ORDER BY m.id ASC
                      '));
      return $solutions;


    }

    public static function checkSolutions($user, $trial, $curr_round)
    {


      $answers = \oceler\AnswerKey::where('factoidset_id',
                                              $trial->rounds[$curr_round - 1]
                                              ->factoidset_id)
                                      ->with('solutionCategories')
                                      ->get();



      $solutions = Solution::where('trial_id', $trial->id)
                            ->where('round', $curr_round)
                            ->where('user_id', $user->id)
                            ->orderBy('category_id', 'asc')
                            ->orderBy('id', 'asc')
                            ->get();


        /* Build an array of correct answers, with a flag to indicate if user's
         answer is correct, and a placeholder to store the length of time
         the answer was correct.
        */

        $solution_answers = array();

        foreach ($answers as $key => $answer) {
          $i = $answer->solution_category_id;
          $solution_answers[$i]['name'] = $answer->solutionCategories->name;
          $solution_answers[$i]['answer'][] = strtolower($answer->solution); // array - one solution can have multiple correct answers
          $solution_answers[$i]['is_correct'] = false;
          $solution_answers[$i]['time_correct'] = 0;
        }

        /* For each of the user's solutions compare them with the solution for
        that particular category. If they match, update the is_correct flag
        and add the time correct (diff between the solutions timestamps).
        */
        foreach ($solutions as $key => $solution) {

          if(in_array(strtolower($solution->solution), $solution_answers[$solution->category_id]['answer'])){

              $solution_answers[$solution->category_id]['is_correct'] = true;
              $time_correct = strtotime($solution->updated_at) - strtotime($solution->created_at);

              if($time_correct < 0) $time_correct = 0;

              // If there is no difference in time, use the start of round time
              // plus length of round to calculate the end of the round
              if($time_correct == 0){
                $round_end_time = strtotime('+'.$trial->rounds[$curr_round - 1]->round_timeout.' minutes',
                                            strtotime($trial->rounds[$curr_round - 1]->start_time));
                $time_correct = abs($round_end_time - (strtotime($solution->created_at)));
              }
              $solution_answers[$solution->category_id]['time_correct'] += $time_correct;
          }
        }

      return $solution_answers;

    }

    public static function getSolutionCategories($factoidset_id)
    {
      $answers = \oceler\AnswerKey::where('factoidset_id', $factoidset_id)
                                      ->with('solutionCategories')
                                      ->groupBy('solution_category_id')
                                      ->get();
      $categories = array();
      $i = 0;

      foreach ($answers as $key => $answer) {

        $categories[$i]['name'] = $answer->solutionCategories->name;
        $categories[$i]['id'] = $answer->solutionCategories->id;
        $i++;
      }

      return $categories;

    }

    public static function getLatestSolutions($trial_id, $round, $last_sol, $filter)
    {
      return Solution::whereIn('user_id', $filter)
                      ->where('id', '>', $last_sol)
                      ->where('trial_id', $trial_id)
                      ->where('round', $round)
                      ->get();
    }

    public static function dateTimeArray()
    {
      $datetime = [];

      $minutes = [];
      for($i = 0; $i<=60; $i++){
        $formatted_min = str_pad($i, 2, '0', STR_PAD_LEFT);
        $minutes[$formatted_min] = $formatted_min;
      }

      $datetime['months'] = [
                  "January" => "January",
                  "February" => "February",
                  "March" => "March",
                  "April" => "April",
                  "May" => "May",
                  "June" => "June",
                  "July" => "July",
                  "August" => "August",
                  "September" => "September",
                  "October" => "October",
                  "November" => "November",
                  "December" => "December"
                ];

      $datetime['minutes'] = $minutes;
      return $datetime;
    }

}
