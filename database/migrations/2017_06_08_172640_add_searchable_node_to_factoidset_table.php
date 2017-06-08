<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSearchableNodeToFactoidsetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('factoidsets', function(Blueprint $table){

          $table->integer('searchable_node')->after('system_msg_name');

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

          $table->dropColumn('searchable_node');

      });
    }
}
