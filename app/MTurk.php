<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class MTurk extends Model
{

  public static function testAwsSdk()
  {
    $client = new \Aws\MTurk\MTurkClient([
      'version' => 'latest',
      'region'  => 'us-east-1'
    ]);

    $result = $client->listHITs([
        'MaxResults' => 100
    ]);

    dump($result);
//https://requestersandbox.mturk.com/mturk/manageHIT?viewableEditPane=manageHIT_expire&HITId=341H3G5YFZDBOAZWUCWEVQKBIPOZ0N

  }

  public static function postHitData($assignment_id, $mturk_id, $total_earnings,
                                     $passed_trial, $completed_trial)
  {
    $submit_to_url = \DB::table('mturk_hits')
                    ->where('assignment_id', '=', $assignment_id)
                    ->where('worker_id', '=', $mturk_id)
                    ->pluck('submit_to');

    $client = new Client(); //GuzzleHttp\Client
    $result = $client->post($submit_to_url, [
      'form_params' => [
          'basePay' => $total_earnings['base_pay'],
          'bonusPay' => $total_earnings['bonus'],
          'passedTrial' => $passed_trial,

      ]
    ]);
  }
}
