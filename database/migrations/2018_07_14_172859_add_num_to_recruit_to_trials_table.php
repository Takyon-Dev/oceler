<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumToRecruitToTrialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('trials', function(Blueprint $table){

           $table->integer('num_to_recruit')->after('num_players');

       });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
       Schema::table('trials', function(Blueprint $table){

           $table->dropColumn('num_to_recruit');

       });
     }
}
