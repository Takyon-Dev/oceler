<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class MTurk extends Model
{

  public static function testAwsSdk()
  {
    echo "-- PHP -- ".getcwd()." -- PHP -- ";
    $python = exec("/usr/bin/python ../resources/pyscripts/testMturkConnection.py -acc_key AKIAJTMGGTPGKJJS2SGA -sec_key ZPIdUj0nmu/N9vk8mv2S7y3FaLcNolS+G1lkAZda -host sandbox");
    echo $python;
    return;

    $pyscript = ' resources/pyscripts/testMturkConnection.py ';
    $python = 'usr/bin/python2.7';
    $args = "-acc_key AKIAJTMGGTPGKJJS2SGA -sec_key ZPIdUj0nmu/N9vk8mv2S7y3FaLcNolS+G1lkAZda -host sandbox";

    $command = escapeshellcmd($python . $pyscript);
    $output = shell_exec($command);
    dump($output);

    //resources/pyscripts/testMturkConnection.py -hit_id 3WUVMVA7OA27PNXEMET7KWWORD3AZW -acc_key AKIAJTMGGTPGKJJS2SGA -sec_key ZPIdUj0nmu/N9vk8mv2S7y3FaLcNolS+G1lkAZda -host sandbox -delay 30.0
    return;

    //'3BQU611VFPJH1X3PGCQ9ZXFSIR099G', 'A2LOZXVWUBY8MO', array('bonus' => '3.45', 'bonus_reason' => 'Bonus payment based on your performance.', 'base_pay' => '5'), false, true
    $assignment_id = '3BQU611VFPJH1X3PGCQ9ZXFSIR099G';
    $mturk_id = 'A2LOZXVWUBY8MO';
    $completed_trial = true;



    $mturk_hit = \DB::table('mturk_hits')
                    ->where('assignment_id', '=', $assignment_id)
                    ->where('worker_id', '=', $mturk_id)
                    ->first();

    dump($mturk_hit);

    /*

        TEST HIT SUBMISSION
    $client = new \GuzzleHttp\Client();
    dump($client);
    $request = $client->CreateRequest('POST',
                                 $mturk_hit->submit_to.'/mturk/externalSubmit',
                                 [
                                   'body' => [
                                                      'assignmentId' => $assignment_id,
                                                      'foo' => 'bar'
                                                    ]
                                                  ]);
    $response = $client->send($request);
    dump($response);
    return;
    */

    $client = new \Aws\MTurk\MTurkClient([
      'version' => 'latest',
      'region'  => env('AWS_REGION', ''),
      'endpoint' => $mturk_hit->submit_to
    ]);

    if($completed_trial){
      $result = $client->approveAssignment([
        'AssignmentId' => '3483FV8BEEIJJUGSXW8I50GCMKI266',
        'RequesterFeedback' => 'nice work',
      ]);
    }

    dump($result);
    return;

    /*
    $client = new \Aws\MTurk\MTurkClient([
      'version' => 'latest',
      'region'  => env('AWS_REGION', ''),
      'endpoint' => env('AWS_ENDPOINT', '')
    ]);

    //$result = $client->listHITs([
        //'MaxResults' => 100
    //]);

    $result = $client->createHIT([
    'AssignmentDurationInSeconds' => 5000, // REQUIRED
    'Description' => 'TESTING THE AWS SDK API', // REQUIRED
    'Keywords' => 'AWSSDKTEST, netlabexperiments',
    'LifetimeInSeconds' => 10000, // REQUIRED
    'MaxAssignments' => 5,
    'Question' => '
      <ExternalQuestion xmlns="http://mechanicalturk.amazonaws.com/AWSMechanicalTurkDataSchemas/2006-07-14/ExternalQuestion.xsd">
        <ExternalURL>https://netlabexperiments.org/MTurk-login</ExternalURL>
        <FrameHeight>600</FrameHeight>
      </ExternalQuestion>'
    ,
    'Reward' => '0.01', // REQUIRED
    'Title' => 'TEST QUESTION', // REQUIRED
    'UniqueRequestToken' => '1234567890',
    ]);

    dump($result);
    */
//https://requestersandbox.mturk.com/mturk/manageHIT?viewableEditPane=manageHIT_expire&HITId=341H3G5YFZDBOAZWUCWEVQKBIPOZ0N
/*
https://tictactoe.amazon.com/gamesurvey.cgi?gameid=01523
&assignmentId=123RVWYBAZW00EXAMPLE456RVWYBAZW00EXAMPLE
&hitId=123RVWYBAZW00EXAMPLE
&turkSubmitTo=https://www.mturk.com/
&workerId=AZ3456EXAMPLE
*/
  }

  public static function postHitData($assignment_id, $mturk_id, $total_earnings,
                                     $passed_trial, $completed_trial)
  {
    $mturk_hit = \DB::table('mturk_hits')
                    ->where('assignment_id', '=', $assignment_id)
                    ->where('worker_id', '=', $mturk_id)
                    ->first();

    $aws_access_key = env('AWS_ACCESS_KEY_ID', '');
    $aws_secret_key = env('AWS_SECRET_ACCESS_KEY', '');

    $host = (strpos($mturk_hit->submit_to, 'sandbox') !== false) ? 'sandbox' : 'real';
    $args = ' -acc_key '.$aws_access_key;
    $args .= ' -sec_key '.$aws_secret_key;
    $args .= ' -sec_key '.$aws_secret_key;



  }
}
