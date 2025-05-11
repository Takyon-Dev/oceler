<?php

use Illuminate\Database\Seeder;

class CountrysetsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('countrysets')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'id' => 1,
          'name' => 'countries1.txt',
          'location' => '/config/countrysets/'
      ]);

      DB::table('countrysets')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'id' => 2,
          'name' => 'countries2.txt',
          'location' => '/config/countrysets/'
      ]);
    }
}
