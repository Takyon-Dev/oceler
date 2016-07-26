<?php

use Illuminate\Database\Seeder;

class FactoidsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('factoids')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'id' => 1,
          'factoidset_id' => 1,
          'factoid' => 'The Amber, Brown, Coral, Violet, or Charcoal groups may
                        be planning an attack'
      ]);

      DB::table('factoids')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'id' => 2,
          'factoidset_id' => 1,
          'factoid' => 'High visibility targets include monuments, banks,
                        skyscrapers, embassies, visiting dignitaries, and
                        own-country dignitaries'
      ]);

      DB::table('factoids')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'id' => 3,
          'factoidset_id' => 2,
          'factoid' => 'The Violet and Gold groups use only their own
                        operatives, never employing locals'
      ]);

      DB::table('factoids')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'id' => 4,
          'factoidset_id' => 2,
          'factoid' => 'The attackers are focusing on a high visibility target'
      ]);
    }
}
