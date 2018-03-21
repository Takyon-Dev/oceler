<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class MturkHit extends Model
{
    protected $fillable = ['worker_id', 'assignment_id', 'hit_id'];
}
