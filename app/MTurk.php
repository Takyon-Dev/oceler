<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class MTurk extends Model
{

  public static function testAwsSdk()
  {
    // python resources/pyscripts/turkConnector.py -acc_key AKIAJTMGGTPGKJJS2SGA -sec_key ZPIdUj0nmu/N9vk8mv2S7y3FaLcNolS+G1lkAZda -host sandbox -hit QAWSED4321RFTG -worker A2LOZXVWUBY8MO -assignment 1234TESTAZSXDC -token 596e0a5f797d3 -delay 0 -trial_completed true -trial_passed false -bonus 0 -qual_id 3DDNYIPUQNTSBR52F1XBRX6XW33RZA -qual_val 1
    $aws_access_key = env('AWS_ACCESS_KEY_ID', '');
    $aws_secret_key = env('AWS_SECRET_ACCESS_KEY', '');

    $args = ' -acc_key '.$aws_access_key;
    $args .= ' -sec_key '.$aws_secret_key;
    $args .= ' -host sandbox';
    $args .= ' -hit QWEASD12345ZXCV';
    $args .= ' -worker A123Q456ZXCC';
    $args .= ' -assignment POO09876QWE';
    $args .= ' -token 125345';
    $args .= ' -delay 0';
    $args .= ' -trial_completed true';
    $args .= ' -trial_passed true';
    $args .= ' -bonus 5.70';
    $args .= ' -qual_id WER123DFGCVB';
    $args .= ' -qual_val 2';

    echo "python resources/pyscripts/turkConnector.py ".$args;

    $python = exec("/usr/bin/python ../resources/pyscripts/turkConnector.py".$args);
    echo $python;

  }

  public static function postHitData($assignment_id, $mturk_id, $submit_to, $total_earnings,
                                     $passed_trial, $completed_trial, $trial_type)
  {

    // Need to convert to strings to pass as args
    $passed_trial = ($passed_trial) ? 'true' : 'false';
    $completed_trial = ($completed_trial) ? 'true' : 'false';

    $mturk_hit = \DB::table('mturk_hits')
                    ->where('assignment_id', '=', $assignment_id)
                    ->where('worker_id', '=', $mturk_id)
                    ->first();

    $aws_access_key = env('AWS_ACCESS_KEY_ID', '');
    $aws_secret_key = env('AWS_SECRET_ACCESS_KEY', '');

    $host = (strpos($submit_to, 'sandbox') !== false) ? 'sandbox' : 'real';
    $args = ' -acc_key '.$aws_access_key;
    $args .= ' -sec_key '.$aws_secret_key;
    $args .= ' -host '.$host;
    $args .= ' -hit '.$mturk_hit->hit_id;
    $args .= ' -worker '.$mturk_id;
    $args .= ' -assignment '.$assignment_id;
    $args .= ' -token '.$mturk_hit->unique_token;
    $args .= ' -delay 20';
    $args .= ' -trial_completed '.$completed_trial;
    $args .= ' -trial_passed '.$passed_trial;
    $args .= ' -bonus '.$total_earnings['bonus'];
    $args .= ' -qual_id '.env('AWS_QUALIFICATION_ID', '');
    $args .= ' -qual_val '.$trial_type;

    $python = exec("/usr/bin/python ../resources/pyscripts/turkConnector.py".$args);

  }
}
