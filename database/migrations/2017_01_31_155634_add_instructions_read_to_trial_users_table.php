<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstructionsReadToTrialUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('trial_user', function (Blueprint $table) {

         $table->integer('instructions_read')->after('group_id')->default(0);
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

         $table->dropColumn('instructions_read');

       });
     }
}
