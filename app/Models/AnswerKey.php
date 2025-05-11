<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerKey extends Model
{
    use HasFactory;

    public function solutionCategories(): \Illuminate\Database\Eloquent\Relations$1 {
      return $this->belongsTo('\App\SolutionCategory', 'solution_category_id', 'id');
    }
}
