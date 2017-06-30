<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPercentCorrectToRoundEarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('round_earnings', function(Blueprint $table){

          $table->integer('num_correct')->after('earnings');
          $table->integer('tot_categories')->after('num_correct');

      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('round_earnings', function(Blueprint $table){

          $table->dropColumn('num_correct');
          $table->dropColumn('tot_categories');

      });
    }
}
