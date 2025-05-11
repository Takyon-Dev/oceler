<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    use HasFactory;

    protected $fillable = ['keyword'];

    public function factoid(): \Illuminate\Database\Eloquent\Relations$1 {
      return $this->belongsToMany('\App\Factoid')->withTimestamps();
    }
}
