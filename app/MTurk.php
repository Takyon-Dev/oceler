<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class MTurk extends Model
{

  public static function testAwsSdk()
  {
    echo 'Current working directory: ';
    echo getcwd();
    return;

    // * * * * * php /var/www/netlabexperiments.org/public_html/oceler/artisan schedule:run >> /dev/null 2>&1

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

  public static function storeHitData($assignment_id, $mturk_id, $submit_to, $total_earnings,
                                     $passed_trial, $completed_trial, $trial_type)
  {
    $mturk_hit = \DB::table('mturk_hits')
                    ->where('assignment_id', '=', $assignment_id)
                    ->where('worker_id', '=', $mturk_id)
                    ->update(['trial_type' => $trial_type,
                              'trial_completed' => $completed_trial,
                              'bonus' => $total_earnings['bonus']]);


  }

  public static function processAssignments()
  {

    $aws_access_key = env('AWS_ACCESS_KEY_ID', '');
    $aws_secret_key = env('AWS_SECRET_ACCESS_KEY', '');
    $PATH_TO_PYSCRIPTS = env('PATH_TO_PYSCRIPTS', '')

    $mturk_hits = \DB::table('mturk_hits')
                     ->where('hit_processed', '=', 0)
                     ->get();

    foreach ($mturk_hits as $hit) {
      $host = (strpos($hit->submit_to, 'sandbox') !== false) ? 'sandbox' : 'real';
      $args = ' -func process_assignment';
      $args .= ' -acc_key '.$aws_access_key;
      $args .= ' -sec_key '.$aws_secret_key;
      $args .= ' -host '.$host;
      $args .= ' -worker '.$hit->worker_id;
      $args .= ' -assignment '.$hit->assignment_id;
      $args .= ' -trial_completed '.$hit->trial_completed;

      exec("/usr/bin/python " . $PATH_TO_PYSCRIPTS . "pyscripts/turkConnector.py".$args, $output, $return_val);

      if($return_val == 0){
        $mturk_hit = \DB::table('mturk_hits')
                        ->where('id', '=', $hit->id)
                        ->update(['hit_processed' => 1]);
      }
    }
  }


  public static function processBonus()
  {
    $aws_access_key = env('AWS_ACCESS_KEY_ID', '');
    $aws_secret_key = env('AWS_SECRET_ACCESS_KEY', '');
    $PATH_TO_PYSCRIPTS = env('PATH_TO_PYSCRIPTS', '')

    $mturk_hits = \DB::table('mturk_hits')
                     ->where('bonus_processed', '=', 0)
                     ->where('bonus', '>', 0)
                     ->get();

    foreach ($mturk_hits as $hit) {
      $host = (strpos($hit->submit_to, 'sandbox') !== false) ? 'sandbox' : 'real';
      $args = ' -func process_bonus';
      $args .= ' -acc_key '.$aws_access_key;
      $args .= ' -sec_key '.$aws_secret_key;
      $args .= ' -host '.$host;
      $args .= ' -worker '.$hit->worker_id;
      $args .= ' -assignment '.$hit->assignment_id;
      $args .= ' -bonus '.$hit->bonus;
      $args .= ' -unique_token '.$hit->unique_token;
      exec("/usr/bin/python " . $PATH_TO_PYSCRIPTS . "pyscripts/turkConnector.py".$args, $output, $return_val);

      if($return_val == 0){
        $mturk_hit = \DB::table('mturk_hits')
                        ->where('id', '=', $hit->id)
                        ->update(['bonus_processed' => 1]);
      }
    }
  }

  public static function processQualification()
  {
    $aws_access_key = env('AWS_ACCESS_KEY_ID', '');
    $aws_secret_key = env('AWS_SECRET_ACCESS_KEY', '');
    $PATH_TO_PYSCRIPTS = env('PATH_TO_PYSCRIPTS', '')

    $mturk_hits = \DB::table('mturk_hits')
                     ->where('qualification_processed', '=', 0)
                     ->where('trial_passed', '=', 1)
                     ->get();

    foreach ($mturk_hits as $hit) {
      $host = (strpos($hit->submit_to, 'sandbox') !== false) ? 'sandbox' : 'real';
      $args = ' -func process_qualification';
      $args .= ' -acc_key '.$aws_access_key;
      $args .= ' -sec_key '.$aws_secret_key;
      $args .= ' -host '.$host;
      $args .= ' -worker '.$hit->worker_id;
      $args .= ' -assignment '.$hit->assignment_id;
      $args .= ' -trial_passed '.$hit->trial_passed;
      $args .= ' -qual_id '.env('AWS_QUALIFICATION_ID', '');
      $args .= ' -qual_val '.$hit->trial_type;

      exec("/usr/bin/python " . $PATH_TO_PYSCRIPTS . "pyscripts/turkConnector.py".$args, $output, $return_val);
      print_r($output);
      echo $return_val;
      if($return_val == 0){
        $mturk_hit = \DB::table('mturk_hits')
                        ->where('id', '=', $hit->id)
                        ->update(['qualification_processed' => 1]);
      }
    }
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
    $PATH_TO_PYSCRIPTS = env('PATH_TO_PYSCRIPTS', '')

    $host = (strpos($submit_to, 'sandbox') !== false) ? 'sandbox' : 'real';
    $args = ' -acc_key '.$aws_access_key;
    $args .= ' -sec_key '.$aws_secret_key;
    $args .= ' -host '.$host;
    $args .= ' -hit '.$mturk_hit->hit_id;
    $args .= ' -worker '.$mturk_id;
    $args .= ' -assignment '.$assignment_id;
    $args .= ' -token '.$mturk_hit->unique_token;
    $args .= ' -delay 300';
    $args .= ' -trial_completed '.$completed_trial;
    $args .= ' -trial_passed '.$passed_trial;
    $args .= ' -bonus '.$total_earnings['bonus'];
    $args .= ' -qual_id '.env('AWS_QUALIFICATION_ID', '');
    $args .= ' -qual_val '.$trial_type;

    exec("/usr/bin/python " . $PATH_TO_PYSCRIPTS . "pyscripts/turkConnector.py".$args, $output, $return_val);

  }
}
