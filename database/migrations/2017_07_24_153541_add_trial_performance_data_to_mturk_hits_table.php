<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrialPerformanceDataToMturkHitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('mturk_hits', function(Blueprint $table){

          $table->integer('trial_type')->after('trial_id');
          $table->integer('trial_completed')->after('trial_type');
          $table->integer('trial_passed')->after('trial_completed');
          $table->float('bonus')->after('trial_passed');
          $table->integer('hit_processed')->after('bonus');
          $table->integer('bonus_processed')->after('hit_processed');
          $table->integer('qualification_processed')->after('bonus_processed');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      $table->dropColumn('trial_type');
      $table->dropColumn('trial_completed');
      $table->dropColumn('trial_passed');
      $table->dropColumn('bonus');
      $table->dropColumn('hit_processed');
      $table->dropColumn('bonus_processed');
      $table->dropColumn('qualification_processed');
    }
}
