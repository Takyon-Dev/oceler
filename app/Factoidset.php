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
    foreach ($config['solutions'] as $key => $sol) {
      if(is_array($sol)){
          foreach ($sol as $s) {
            echo $key .' :::: '.$s.'<br>';
          }
      }
      else echo  $key .' :::: '.$sol.'<br>';


    }
    return;
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



  }

}
