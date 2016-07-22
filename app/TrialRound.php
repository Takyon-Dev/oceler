<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class TrialRound extends Model
{
  public function trial() {
    return $this->belongsTo('\oceler\Trial');
}
}
