<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class AnswerKey extends Model
{
    public function solutionCategories()
    {
      return $this->belongsTo('\oceler\SolutionCategory', 'solution_category_id', 'id');
    }
}
