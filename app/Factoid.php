<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Factoid extends Model
{
  public function factoidset()
  {
    return $this->belongsTo('oceler\Factoidset')->withTimestamps();
  }

  public function keywords()
  {
    return $this->belongsToMany('\oceler\Keyword')->withTimestamps();
  }
}
