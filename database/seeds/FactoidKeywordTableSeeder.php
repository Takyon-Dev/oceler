<?php

use Illuminate\Database\Seeder;

class FactoidKeywordTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Store the test data in an array as $factoid=>[$keywords], using the
        // factoid IDs we set in the FactoidsTableSeeder
        $factoids =[
            1 => ['Amber', 'Brown', 'Coral', 'Violet', 'Charcoal', 'group',
                  'groups','plan', 'planning', 'attack', 'attacks', 'attacker',
                  'attackers'],

            2 => ['High', 'visibility', 'targets', 'target', 'include',
                  'monuments', 'monument', 'banks', 'bank', 'skyscrapers',
                  'skyscraper', 'embassies', 'embassy', 'visiting', 'visit',
                  'dignitaries', 'dignitary', 'own-country', 'own', 'country'],

            3 => ['Violet', 'Gold', 'group', 'groups', 'use', 'only',
                  'their', 'own', 'operatives', 'operative', 'never',
                  'employing', 'employ', 'locals', 'local'],

            4 => ['attackers', 'attack', 'focusing', 'focus', 'high',
                  'visibility','target']
        ];

        // For each factoid, create a new pivot entry for each keyword
        foreach($factoids as $factoid => $keyword) {

            // First get the factoid
            $fact = \App\Models\Factoid::find($factoid);

            // And for each keyword, connect it with the factoid
            foreach($keyword as $word) {
                $key = \App\Models\Keyword::where('keyword','=', $word)->first();
                $fact->keywords()->save($key);
            }

        }
    }
}
