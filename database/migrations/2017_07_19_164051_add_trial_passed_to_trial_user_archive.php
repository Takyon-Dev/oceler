<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrialPassedToTrialUserArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('trial_user_archive', function(Blueprint $table){

           $table->integer('trial_passed')->after('completed_trial');

       });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
       Schema::table('trial_user_archive', function(Blueprint $table){

           $table->dropColumn('trial_passed');

       });
     }
}
