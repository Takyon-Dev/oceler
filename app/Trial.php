<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Trial extends Model
{
    public function rounds() {
      return $this->hasMany('oceler\Round');
    }

    public function users() {
      return $this->hasMany('\oceler\User');
    }
}
