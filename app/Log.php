<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public static function trialLog($trial_id, $data)
    {
      $log = storage_path()."/logs/trial-logs/trial_".$trial_id.".txt";
      $fh = fopen($log, 'a');

      $dt = \Carbon\Carbon::now()->toDateTimeString();
      fwrite($fh, $dt." :: ".$data."\n");
      fclose($fh);
    }
}
