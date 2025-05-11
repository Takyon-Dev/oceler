<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupIdToUserNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_nodes', function(Blueprint $table){

            $table->integer('group_id')->after('user_id')->unsigned();

            $table->foreign('group_id')
                  ->references('id')->on('groups')
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
        Schema::table('user_nodes', function (Blueprint $table) {

          $table->dropForeign('user_nodes_group_id_foreign');
          $table->dropColumn('group_id');

        });
    }
}
