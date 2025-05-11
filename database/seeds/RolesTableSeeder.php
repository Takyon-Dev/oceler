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
      App\Models\Role::create([
          'name' => 'Root',
          'description' => 'Use this account with caution. It has access to everything.',
      ]);
      App\Models\Role::create([
          'name' => 'Admin',
          'description' => 'This is the admin user that has access to the admin section.',
      ]);
      App\Models\Role::create([
          'name' => 'Player',
          'description' => 'This is the default user role.',
      ]);
    }
}
