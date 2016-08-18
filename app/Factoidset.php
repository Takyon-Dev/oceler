<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;

class Factoidset extends Model
{
  public function factoid() {
    return $this->hasMany('\oceler\Factoid');
  }

  public static function addFactoidset($config)
  {

    // Save the new Factoidset
    $factoidset = new Factoidset();
    $factoidset->name = $config['name'];
    $factoidset->save();

    // Then store each factoid, along with the factoidset ID
    foreach ($config['factoids'] as $factoid) {
      $fact = new \oceler\Factoid();
      $fact->factoidset_id = $factoidset->id;
      $fact->factoid = $factoid['factoid'];
      $fact->save();

      // Store each keyword, with firstOrNew checking to see if it
      // already exists
      foreach ($factoid['keywords'] as $keyword) {

        $k = \oceler\Keyword::firstOrNew(['keyword' => $keyword]);
        $k->keyword = $keyword;
        $k->save();

        // Connect the keyword and the factoid by ID
        $fact->keywords()->save($k);
      }
    }

    // Then store the solutions
    foreach ($config['solutions'] as $key => $sol) {

      // Find the solution category ID
      $cat = \DB::table('solution_categories')
                ->where('name', $key)
                ->value('id');

      // If there is an array of solutions for a category,
      // i.e. a category has more than one accepted solution
      // process each of them
      if(is_array($sol)){
          foreach ($sol as $s) {
            Factoidset::saveAnswerKey($cat, $s, $factoidset->id);
          }
      }
      else Factoidset::saveAnswerKey($cat, $sol, $factoidset->id);


    }
  }

  public static function saveAnswerKey($category, $solution, $factoidset_id)
  {
      \DB::table('answer_keys')->insert([
        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
        'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
        'solution_category_id' => $category,
        'factoidset_id' => $factoidset_id,
        'solution' => $solution
      ]);
  }

}
