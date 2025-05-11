<?php

use Illuminate\Database\Seeder;

class NamesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // An array of namesets IDs (set in the Nameset Seeder) that each
        // contain an array of names
        $namesets = [
                  1 => ['Alex', 'Chris', 'Dale', 'Francis', 'Harlan', 'Jesse',
                        'Kim', 'Leslie', 'Morgan', 'Pat', 'Quinn', 'Robin', 'Sam',
                        'Sidney', 'Taylor', 'Val', 'Whitley'],

                  2 => ['Alex', 'Chris', 'Dale', 'Francis', 'Harlan', 'Jesse',
                        'Kim', 'Leslie', 'Morgan', 'Pat', 'Quinn', 'Robin', 'Sam',
                        'Sidney', 'Taylor', 'Val', 'Whitley', 'Gary', 'Arnold',
                        'Tina']
        ];

        // For each nameset, create a new entry for each name
        foreach($namesets as $set => $names) {

            foreach($names as $name) {

              DB::table('names')->insert([
                  'created_at' => Carbon\Carbon::now()->toDateTimeString(),
                  'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
                  'nameset_id' => $set,
                  'name' => $name
              ]);
            }

        }
    }
}    
