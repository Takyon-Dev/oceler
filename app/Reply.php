<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    public function replier() {
      return $this->belongsTo('\oceler\User', 'user_id', 'id');
    }

}
