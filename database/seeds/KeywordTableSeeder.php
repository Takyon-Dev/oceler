<?php

use Illuminate\Database\Seeder;

class KeywordTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $keywords = array('Amber', 'Brown', 'Coral', 'Violet', 'Charcoal',
                          'group', 'groups','plan', 'planning', 'attack',
                          'attacks', 'attacker', 'attackers',

                          'High', 'visibility', 'targets', 'target', 'include',
                          'monuments', 'monument', 'banks', 'bank',
                          'skyscrapers', 'skyscraper', 'embassies', 'embassy',
                          'visiting', 'visit', 'dignitaries', 'dignitary',
                          'own-country', 'own', 'country');

        foreach ($keywords as $key => $word) {
          $k = new \oceler\Keyword();
          $k->keyword = $word;
          $k->save();
        }
    }
}
