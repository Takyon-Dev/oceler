<?php
namespace oceler\MTurk;
use DB;

class MTurk
{
  private $PATH_TO_PYTHON;
  private $PATH_TO_PYSCRIPTS;
  private $aws_access_key;
  private $aws_secret_key;
  public $hits;

  public function __construct()
  {
    $this->PATH_TO_PYTHON = env('PATH_TO_PYTHON', '');
    $this->PATH_TO_PYSCRIPTS = env('PATH_TO_PYSCRIPTS', '');
    $this->aws_access_key = env('AWS_ACCESS_KEY_ID', '');
    $this->aws_secret_key = env('AWS_SECRET_ACCESS_KEY', '');

  }

  public function testConnection()
  {
    $this->hits = DB::table('mturk_hits')
                  ->where('id', '=', 5)
                  ->update(['unique_token' => 'ABC123DEF456']);


    $PATH_TO_PYSCRIPTS = env('PATH_TO_PYSCRIPTS', '');

    $aws_access_key = env('AWS_ACCESS_KEY_ID', '');
    $aws_secret_key = env('AWS_SECRET_ACCESS_KEY', '');
    $host = 'sandbox';

    $args = ' -acc_key '.$aws_access_key;
    $args .= ' -sec_key '.$aws_secret_key;
    $args .= ' -host '.$host;
    $args .= ' -func test_connection';

    exec($this->PATH_TO_PYTHON. " "
          . $this->PATH_TO_PYSCRIPTS
          . "pyscripts/turkConnector3.py"
          .$args, $output, $return_val);

    return $output;
  }

  public function pythonConnect($hit, $operation)
  {
    $host = (strpos($hit->submit_to, 'sandbox') !== false) ? 'sandbox' : 'real';

    $args = ' -acc_key '.$aws_access_key;
    $args .= ' -sec_key '.$aws_secret_key;
    $args .= ' -host '.$host;
    $args .= ' -worker '.$hit->worker_id;
    $args .= ' -assignment '.$hit->assignment_id;
    $args .= ' -bonus '.$hit->bonus;
    $args .= ' -unique_token '.$hit->unique_token;
    $args .= ' -trial_completed '.$hit->trial_completed;
    $args .= ' -trial_passed '.$hit->trial_passed;
    $args .= ' -qual_id '.env('AWS_QUALIFICATION_ID', '');
    $args .= ' -qual_val '.$hit->trial_type;
    $args .= ' -func '.$operation;

    exec($this->PATH_TO_PYTHON. " "
          . $this->PATH_TO_PYSCRIPTS
          . "pyscripts/turkConnector3.py"
          .$args, $output, $return_val);
    // log output
    return $return_val;
  }

}
