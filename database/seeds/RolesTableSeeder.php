<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      oceler\Role::create([
          'id'            => 1,
          'name'          => 'Root',
          'description'   => 'This account has full access and control to the entire app.'
      ]);
      oceler\Role::create([
          'id'            => 2,
          'name'          => 'Administrator',
          'description'   => 'Full access to the administrator dashboard.'
      ]);
      oceler\Role::create([
          'id'            => 3,
          'name'          => 'Player',
          'description'   => 'Standard user - only allows access to player section.'
      ]);
    }
}
