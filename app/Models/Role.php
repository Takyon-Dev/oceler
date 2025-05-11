<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    public function users(): \Illuminate\Database\Eloquent\Relations$1 {
      return $this->hasMany('\User', 'role_id', 'id');
    }
}
