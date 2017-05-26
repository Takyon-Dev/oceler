<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrialTypeToTrialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('trials', function(Blueprint $table){

          $table->int('trial_type')->after('name');

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

          $table->dropColumn('trial_type');

      });
    }
}
