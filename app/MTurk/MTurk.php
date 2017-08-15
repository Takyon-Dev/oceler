<?php
namespace oceler\MTurk;
use DB;

class MTurk
{
  private $PATH_TO_PYTHON;
  private $PATH_TO_PYSCRIPTS;
  private $aws_access_key;
  private $aws_secret_key;
  private $aws_qualification_id;
  public $hit;

  public function __construct()
  {
    $this->PATH_TO_PYTHON = env('PATH_TO_PYTHON', '');
    $this->PATH_TO_PYSCRIPTS = env('PATH_TO_PYSCRIPTS', '');
    $this->aws_access_key = env('AWS_ACCESS_KEY_ID', '');
    $this->aws_secret_key = env('AWS_SECRET_ACCESS_KEY', '');
    $this->aws_qualification_id = env('AWS_QUALIFICATION_ID', '');

  }

  private function pythonConnect($operation)
  {

    $args = ' -acc_key '.$this->aws_access_key;
    $args .= ' -sec_key '.$this->aws_secret_key;
    $args .= ' -qual_id '.$this->aws_qualification_id;
    $args .= ' -func '.$operation;

    if($this->hit) {
      $host = (strpos($this->hit->submit_to, 'sandbox') !== false) ? 'sandbox' : 'real';
      $args .= ' -host '.$host;
      $args .= ' -worker '.$this->hit->worker_id;
      $args .= ' -assignment '.$this->hit->assignment_id;
      $args .= ' -bonus '.$this->hit->bonus;
      $args .= ' -unique_token '.$this->hit->unique_token;
      $args .= ' -trial_completed '.$this->hit->trial_completed;
      $args .= ' -trial_passed '.$this->hit->trial_passed;
      $args .= ' -qual_val '.$this->hit->trial_type;
    }

    exec($this->PATH_TO_PYTHON. " "
          . $this->PATH_TO_PYSCRIPTS
          . "pyscripts/turkConnector3.py"
          .$args, $output, $return_val);

    return $return_val;
  }

  public function process_assignment()
  {
    if($this->hit->trial_completed == 1){
      $result = $this->pythonConnect('approve_assignment');
    }
    else {
      $result = $this->pythonConnect('reject_assignment');
    }
    if($result == 0){
      $this->hit->hit_processed = 1;
      $this->hit->save();
    }
  }

  public function process_bonus()
  {
    if($this->hit->bonus > 0){
      $result = $this->pythonConnect('process_bonus');
    }
    if($result == 0){
      $this->hit->bonus_processed = 1;
      $this->hit->save();
    }
  }

  public function process_qualification()
  {
    if($this->hit->trial_completed == 1 && $this->hit->trial_passed == 1){
      $result = $this->pythonConnect('process_qualification');
    }
    if($result == 0){
      $this->hit->qualification_processed = 1;
      $this->hit->save();
    }
  }

  public function testConnection()
  {
      $this->pythonConnect('test_connection');
  }

}
