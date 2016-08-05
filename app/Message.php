<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
  public function users()
  {
    return $this->belongsToMany('oceler\User')->withTimestamps();
  }

  public function sender() {
    return $this->belongsTo('oceler\User');
  }

  public function replies() {
    return $this->hasMany('oceler\Reply')->with('replier');
  }

}
