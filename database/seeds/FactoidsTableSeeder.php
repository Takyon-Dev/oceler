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
    }
}
