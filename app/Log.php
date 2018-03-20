<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{

    protected $path;
    protected $name;

    public function __construct($id = null, array $attributes = array())
    {
      $this->path = storage_path()."/logs/trial-logs/trial_".$id.".txt";
      $this->name = \DB::table('trials')->where('id', $id)->pluck('name') ."_log.txt";

      parent::__construct($attributes);
    }

    public static function trialLog($trial_id, $data)
    {
      $path = storage_path()."/logs/trial-logs/trial_".$trial_id.".txt";
      $fh = fopen($path, 'a');

      $dt = \Carbon\Carbon::now()->toDateTimeString();
      fwrite($fh, $dt." :: ".$data."\n");
      fclose($fh);
    }

    public static function errorLog($e)
    {

    }

    public static function listAll()
    {

      $logs = array();
      $files = scandir(storage_path()."/logs/trial-logs/", SCANDIR_SORT_DESCENDING);
      natsort($files);
      $files = array_reverse($files, true);
      $i = 0;
      foreach ($files as $f) {
        if($f != '.' && $f != '..'){

          $id = trim($f, "trial_.txt");

          $trial = \oceler\Trial::where('id', $id)
                                ->withTrashed()
                                ->first();

          $logs[$i]['log'] = $f;
          $logs[$i]['id'] = $id;
          $logs[$i]['name'] = $trial->name;
          $logs[$i]['date'] = $trial->created_at->toDateTimeString();
          $i++;
        }
      }

      return $logs;
    }


}
