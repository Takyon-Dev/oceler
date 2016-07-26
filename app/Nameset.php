<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Nameset extends Model
{
    public function names()
    {
      return $this->hasMany('\oceler\Name');
    }
}
