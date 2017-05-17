<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSystemMsgNameToFactoidset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('factoidsets', function(Blueprint $table){

          $table->string('system_msg_name')->after('solutions_display_name');

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

          $table->dropColumn('system_msg_name');

      });
    }
}
