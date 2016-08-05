<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Solution extends Model
{
    public function trials()
    {
      return $this->belongsTo('oceler\Trial');
    }

}
