<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
  public function users()
  {
    return $this->belongsToMany('oceler\User')->withTimestamps();
  }

}
