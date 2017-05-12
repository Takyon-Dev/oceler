<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrialCompletedToTrialUserArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('trial_user_archive', function(Blueprint $table){
        $table->boolean('completed_trial')->after('group_id');
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

        $table->dropColumn('completed_trial');

      });
    }
}
