<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
  public function trial(){
    return $this->belongsTo('\oceler\Trial')
                ->with('network');
  }

  public function network(){
    return $this->hasOne('\oceler\Network', 'id', 'network_id');
  }
}
