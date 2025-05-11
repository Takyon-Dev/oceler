<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastPingToTrialUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('trial_user', function(Blueprint $table){
        $table->timestamp('last_ping')->after('instructions_read');
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

        $table->dropColumn('last_ping');

      });
    }
}
