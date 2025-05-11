<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrialTypeToTrialUserArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('trial_user_archive', function(Blueprint $table){

           $table->integer('trial_type')->after('trial_id');

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

           $table->dropColumn('trial_type');

       });
     }
}
