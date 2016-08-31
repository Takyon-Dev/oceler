<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
  public function trial() {
    return $this->belongsTo('\oceler\Trial')
                ->with('factoidset')
                ->with('nameset');
  }

  public function factoidset(){
    return $this->hasOne('\oceler\Factoidset');
  }

  public function nameset(){
    return $this->hasOne('\oceler\Nameset');
  }
}
