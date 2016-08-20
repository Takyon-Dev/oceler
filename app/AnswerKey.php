<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class AnswerKey extends Model
{
    public function solution_categories()
    {
      return $this->belongsTo('\oceler\SolutionCategory');
    }
}
