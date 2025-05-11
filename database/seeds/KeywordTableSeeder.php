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
                          'own-country', 'own', 'country',

                          'Violet', 'Gold', 'group', 'groups', 'use', 'only',
                          'their', 'own', 'operatives', 'operative', 'never',
                          'employing', 'employ', 'locals', 'local',

                          'attackers', 'attack', 'focusing', 'focus', 'high',
                          'visibility','target');

        foreach ($keywords as $key => $word) {
          $k = \App\Models\Keyword::firstOrNew(['keyword' => $word]);
          $k->keyword = $word;
          $k->save();
        }
    }
}
