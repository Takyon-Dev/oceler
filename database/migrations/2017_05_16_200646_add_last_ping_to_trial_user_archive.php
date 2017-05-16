<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastPingToTrialUserArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('trial_user_archive', function(Blueprint $table){
         $table->timestamp('last_ping')->after('group_id');
       });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
       Schema::table('trial_user_archive', function (Blueprint $table) {

         $table->dropColumn('last_ping');

       });
     }
}
