<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrialIdToMturkHits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('mturk_hits', function(Blueprint $table){

          $table->integer('trial_id')->after('unique_token');

      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('mturk_hits', function(Blueprint $table){

          $table->dropColumn('trial_id');

      });
    }
}
