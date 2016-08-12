<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoundToSolutionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('solutions', function (Blueprint $table) {

        $table->integer('round')->after('trial_id');

      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('solutions', function (Blueprint $table) {

        $table->dropColumn('round');

      });
    }
}
