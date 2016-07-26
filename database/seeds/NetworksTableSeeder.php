<?php

use Illuminate\Database\Seeder;

class NetworksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    DB::table('networks')->insert([
	        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
	        'trial_id' => 1
	    ]);

	    for($i=1; $i<=5; $i++){

		    DB::table('network_nodes')->insert([
		        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
		        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
		        'network_id' => 1,
		        'node' => $i
		    ]);

		    DB::table('user_nodes')->insert([
		        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
		        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
		        'user_id' => $i,
		        'node_id' => $i
		    ]);

	    }

	    for($i=1; $i<=5; $i++){


	    	for($j=1; $j<=5; $j++){
	    		if($j != $i){

				    DB::table('network_edges')->insert([
				        'created_at' => Carbon\Carbon::now()->toDateTimeString(),
				        'updated_at' => Carbon\Carbon::now()->toDateTimeString(),
				        'network_id' => 1,
				        'source' => $i,
				        'target' => $j
				    ]);


	    		}


	    	}

	    }

    }
}
