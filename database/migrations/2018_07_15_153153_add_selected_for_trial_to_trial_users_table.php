<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSelectedForTrialToTrialUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('trial_user', function(Blueprint $table){

           $table->boolean('selected_for_trial')->after('instructions_read');

       });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
       Schema::table('trial_user', function(Blueprint $table){

           $table->dropColumn('selected_for_trial');

       });
     }
}
