<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrialAndRoundColumnsToSearches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('searches', function (Blueprint $table) {
          $table->integer('round_id')->after('trial_id')->unsigned();

          $table->foreign('round_id')
                ->references('id')->on('rounds')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('searches', function (Blueprint $table) {
          $table->dropForeign('searches_round_id_foreign');
          $table->dropColumn('round_id');
        });
    }
}
