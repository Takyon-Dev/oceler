<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SoftDeletingTrait;

class Trial extends Model
{
    use SoftDeletes;
    protected $table = 'trials';
    public $timestamps = true;
    protected $dates = ['deleted_at'];

    public function rounds() {
      return $this->hasMany('oceler\Round');
    }

    public function users() {
      return $this->belongsToMany('oceler\User')->withTimestamps();
    }

    public function solutions() {
      return $this->hasMany('oceler\Solution');
    }

}
