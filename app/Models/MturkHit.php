<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MturkHit extends Model
{
    use HasFactory;

    protected $fillable = ['worker_id', 'assignment_id', 'hit_id'];
}
