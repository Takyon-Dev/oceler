<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrialTypeToQueue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('queues', function(Blueprint $table){

          $table->integer('trial_type')->after('user_id');

      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('queues', function(Blueprint $table){

          $table->dropColumn('trial_type');

      });
    }
}
