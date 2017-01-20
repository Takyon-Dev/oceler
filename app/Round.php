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

}
