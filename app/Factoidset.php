<?php

namespace oceler;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factoidset extends Model
{
  use SoftDeletes;
  protected $dates = ['deleted_at'];

  public function factoid() {
    return $this->hasMany('\oceler\Factoid');
  }


  public static function addFactoidsetFromConfig($config)
  {

    // Save the new Factoidset
    $factoidset = new Factoidset();
    $factoidset->name = $config['name'];
    $factoidset->solutions_display_name = $config['solutions-display-name'];
    $factoidset->system_msg_name = $config['system-msg-name'];
    $factoidset->searchable_node = $config['searchable-node'];
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

      // Store the wave and node the factoid will initially get
      // distributed to
      if(is_array($factoid['nodes'])){
          foreach ($factoid['nodes'] as $node) {
            Factoidset::saveFactoidDistribution($factoidset->id, $fact->id, $node, $factoid['wave']);
          }
      }
      else Factoidset::saveFactoidDistribution($factoidset->id, $fact->id, $factoid['nodes'], $factoid['wave']);
    }

    // Then store the solutions
    foreach ($config['solutions'] as $key => $sol) {

      // Find the solution category ID
      $cat = \DB::table('solution_categories')
                ->where('name', $key)
                ->value('id');

      // If the category isn't found, add it
      if(!$cat){
        $cat = \DB::table('solution_categories')->insertGetId([
                            'name' => $key,
                          ]);
      }

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

  public static function saveFactoidDistribution($factoidset_id, $factoid_id, $node, $wave)
  {
      \DB::table('factoid_distributions')->insert([
        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
        'updated_at' => \Carbon\Carbon::now()->toDateTimeString(),
        'factoidset_id' => $factoidset_id,
        'factoid_id' => $factoid_id,
        'node' => $node,
        'wave' => $wave
      ]);
  }

}
