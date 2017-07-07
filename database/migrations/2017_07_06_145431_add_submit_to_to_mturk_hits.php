<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubmitToToMturkHits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('mturk_hits', function(Blueprint $table){

          $table->string('submit_to')->after('assignment_id');

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

          $table->dropColumn('submit_to');

      });
    }
}
