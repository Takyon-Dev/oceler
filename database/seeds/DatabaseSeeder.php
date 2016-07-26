<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(RolesTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(NetworksTableSeeder::class);
        $this->call(SessionTableSeeder::class);
        $this->call(SolutionCategoryTableSeeder::class);
        $this->call(CountrysetsTableSeeder::class);
        $this->call(FactoidsetsTableSeeder::class);
        $this->call(NamesetsTableSeeder::class);
        $this->call(NamesTableSeeder::class);
        $this->call(FactoidsTableSeeder::class);
        $this->call(KeywordTableSeeder::class);
        $this->call(FactoidKeywordTableSeeder::class);

        Model::reguard();
    }
}
