<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSolutionsDisplayNameToFactoidsets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('factoidsets', function(Blueprint $table){

          $table->string('solutions_display_name')->after('name');

      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('factoidsets', function(Blueprint $table){

          $table->dropColumn('solutions_display_name');

      });
    }
}
