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
	        'trial_id' => 1,
	        'player_name' => 'Harley',
	        'password' => Hash::make('oceler'),
          'role_id' => 3,
	    ]);

	    DB::table('users')->insert([
	        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'name' => 'Barry Tester',
	        'email' => 'barry@test.com',
	        'trial_id' => 1,
	        'player_name' => 'Casey',
	        'password' => Hash::make('oceler'),
          'role_id' => 3,
	    ]);

	    DB::table('users')->insert([
	        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'name' => 'Steve Testerman',
	        'email' => 'steve@test.com',
	        'trial_id' => 1,
	        'player_name' => 'Dakota',
	        'password' => Hash::make('oceler'),
          'role_id' => 3,
	    ]);

	    DB::table('users')->insert([
	        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'name' => 'Alex Testopolis',
	        'email' => 'alex@test.com',
	        'trial_id' => 1,
	        'player_name' => 'Jordan',
	        'password' => Hash::make('oceler'),
          'role_id' => 3,
	    ]);

	    DB::table('users')->insert([
	        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'name' => 'Lou Testa',
	        'email' => 'lou@test.com',
	        'trial_id' => 1,
	        'player_name' => 'Riley',
	        'password' => Hash::make('oceler'),
          'role_id' => 3,
	    ]);

      DB::table('users')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'name' => 'Adam Admintest',
          'email' => 'admin@test.com',
          'trial_id' => 1,
          'player_name' => 'Administrator',
          'password' => Hash::make('OcelerAdmin'),
          'role_id' => 2,
      ]);

      DB::table('users')->insert([
          'created_at' => Carbon\Carbon::now()->toDateTimeString(),
          'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
          'name' => 'System',
          'email' => 'system@oceler',
          'trial_id' => 1,
          'player_name' => 'System',
          'password' => Hash::make('OcelerAdmin'),
          'role_id' => 3,
      ]);

    }
}
