<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Name extends Model
{
    public function nameset()
    {
      return $this->belongsTo('\oceler\Nameset');
    }
}
