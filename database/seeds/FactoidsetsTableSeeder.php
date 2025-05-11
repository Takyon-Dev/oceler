<?php

use Illuminate\Database\Seeder;

class FactoidsetsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('factoidsets')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'id' => 1,
          'name' => 'factoidset1ha1-17.txt',
          'location' => '/config/factoidsets/'
      ]);

      DB::table('factoidsets')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'id' => 2,
          'name' => 'factoidset2ha1-17.txt',
          'location' => '/config/factoidsets/'
      ]);
    }
}
