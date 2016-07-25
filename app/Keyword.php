<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    public function factoid()
    {
      return $this->belongsToMany('\oceler\Factoid')->withTimestamps();
    }
}
