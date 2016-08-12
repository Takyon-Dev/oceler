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

    public static function checkSolutions($solutions, $factoidset_id)
    {

      $key = array();
      $key['Who'] = array(' ', false);
      $key['What'] = array(' ', false);
      $key['Where'] = array(' ', false);
      $key['When'] = array(' ', false);
      $key['How'] = array(' ', false);

      if($factoidset_id == 1){

        foreach ($solutions as $sol) {

          $solution = $sol->solution;
          if($sol->category_id == 1){
            if(strtolower($solution) == 'violet'){
              $key['Who'] = array($solution, true);
            }
            else {
              $key['Who'] = array($solution, false);
            }
          }

          if($sol->category_id == 2){
            if(strtolower($solution) == 'bank' || strtolower($solution) == 'banks'){
              $key['What'] = array($solution, true);
            }
            else {
              $key['What'] = array($solution, false);
            }
          }

          if($sol->category_id == 3){
            if(strtolower($solution) == 'psiland'){
              $key['Where'] = array($solution, true);
            }
            else {
              $key['Where'] = array($solution, false);
            }
          }

          if($sol->category_id == 4){
            if(strtolower($solution) == 'april'){
              $key['When'] = array($solution, true);
            }
            else {
              $key['When'] = array($solution, false);
            }
          }

          if($sol->category_id == 5){
            if(strtolower($solution) == 'TEST'){
              $key['How'] = array($solution, true);
            }
            else {
              $key['How'] = array($solution, false);
            }
          }
        }

      }

      if($factoidset_id == 2){

        foreach ($solutions as $key => $sol) {
          $solution = $solution;
          if($sol->category_id == 1){
            if(strtolower($solution) == 'gold'){
              $key['Who'] = array($solution, true);
            }
            else {
              $key['Who'] = array($solution, false);
            }
          }

          if($sol->category_id == 2){
            if(strtolower($solution) == 'skyscraper' || strtolower($solution) == 'skyscrapers'){
              $key['What'] = array($solution, true);
            }
            else {
              $key['What'] = array($solution, false);
            }
          }

          if($sol->category_id == 3){
            if(strtolower($solution) == 'tauland'){
              $key['Where'] = array($solution, true);
            }
            else {
              $key['Where'] = array($solution, false);
            }
          }

          if($sol->category_id == 4){
            if(strtolower($solution) == 'may'){
              $key['When'] = array($solution, true);
            }
            else {
              $key['When'] = array($solution, false);
            }
          }

          if($sol->category_id == 5){
            if(strtolower($solution) == 'TEST'){
              $key['How'] = array($solution, true);
            }
            else {
              $key['How'] = array($solution, false);
            }
          }
        }

      }

      return $key;

    }

}
