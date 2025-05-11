<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

  public function trial(): \Illuminate\Database\Eloquent\Relations$1 {
    return $this->belongsTo('\App\Trial')
                ->with('network');
  }

  public function network(): \Illuminate\Database\Eloquent\Relations$1 {
    return $this->hasOne('\App\Network', 'id', 'network_id');
  }
}
