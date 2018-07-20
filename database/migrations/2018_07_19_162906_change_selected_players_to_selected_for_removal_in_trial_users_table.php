<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSelectedPlayersToSelectedForRemovalInTrialUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('trial_user', function (Blueprint $table) {

         $table->renameColumn('selected_for_trial', 'selected_for_removal');
       });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
       Schema::table('trial_user', function (Blueprint $table) {

         $table->renameColumn('selected_for_removal', 'selected_for_trial');
       });
     }
}
