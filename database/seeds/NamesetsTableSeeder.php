<?php

use Illuminate\Database\Seeder;

class NamesetsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('namesets')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'id' => 1,
          'name' => 'names17.txt',
          'location' => '/config/namesets/'
      ]);

      DB::table('namesets')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'id' => 2,
          'name' => 'names20.txt',
          'location' => '/config/namesets/'
      ]);
    }
}
