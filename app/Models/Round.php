<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

  public function trial(): \Illuminate\Database\Eloquent\Relations$1 {
    return $this->belongsTo('\App\Trial')
                ->with('factoidset')
                ->with('nameset');
  }

}
