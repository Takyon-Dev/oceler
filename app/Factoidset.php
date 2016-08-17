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

      // Then store each keyword, first checking to see if it
      // already exists
      foreach ($factoid['keywords'] as $keyword) {

        $k = \oceler\Keyword::firstOrNew(['keyword' => $keyword]);
        $k->keyword = $keyword;
        $k->save();

        // Finally, connect the keyword and the factoid
        $fact->keywords()->save($k);
      }
    }


  }

}
