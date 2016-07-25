<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Factoidset extends Model
{
  public function factoid() {
    return $this->hasMany('\oceler\Factoid');
  }
}
