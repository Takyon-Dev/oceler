<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    public function users()
    {
      return $this->hasMany('\User', 'role_id', 'id');
    }
}
