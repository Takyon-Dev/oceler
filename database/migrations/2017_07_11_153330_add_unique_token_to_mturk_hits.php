<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueTokenToMturkHits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('mturk_hits', function(Blueprint $table){

          $table->string('unique_token')->after('submit_to');

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

          $table->dropColumn('unique_token');

      });
    }
}
