<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

	    DB::table('users')->insert([
	        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'name' => 'Phil McTestin',
	        'email' => 'phil@test.com',
	        'session_id' => 1,
	        'player_name' => 'Harley',
	        'password' => Hash::make('oceler'),
	    ]); 

	    DB::table('users')->insert([
	        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'name' => 'Barry Tester',
	        'email' => 'barry@test.com',
	        'session_id' => 1,
	        'player_name' => 'Casey',
	        'password' => Hash::make('oceler'),
	    ]);	   

	    DB::table('users')->insert([
	        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'name' => 'Steve Testerman',
	        'email' => 'steve@test.com',
	        'session_id' => 1,
	        'player_name' => 'Dakota',
	        'password' => Hash::make('oceler'),
	    ]);

	    DB::table('users')->insert([
	        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'name' => 'Alex Testopolis',
	        'email' => 'alex@test.com',
	        'session_id' => 1,
	        'player_name' => 'Jordan',
	        'password' => Hash::make('oceler'),
	    ]);

	    DB::table('users')->insert([
	        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'name' => 'Lou Testa',
	        'email' => 'lou@test.com',
	        'session_id' => 1,
	        'player_name' => 'Riley',
	        'password' => Hash::make('oceler'),
	    ]);	    	    	       

    }
}
