<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factoid extends Model
{
    use HasFactory;

  public function factoidset(): \Illuminate\Database\Eloquent\Relations$1 {
    return $this->belongsTo('App\Models\Factoidset')->withTimestamps();
  }

  public function keywords(): \Illuminate\Database\Eloquent\Relations$1 {
    return $this->belongsToMany('App\Models\Keyword')->withTimestamps();
  }

  public function message(): \Illuminate\Database\Eloquent\Relations$1 {
    return $this->hasMany('App\Models\Message');
  }
}
