<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('groups', function (Blueprint $table) {

        $table->increments('id');
        $table->integer('trial_id')->unsigned();
        $table->integer('network_id')->unsigned();
        $table->text('survey_url');
        $table->timestamps();

        $table->foreign('trial_id')
              ->references('id')->on('trials')
              ->onDelete('cascade');

        $table->foreign('network_id')
              ->references('id')->on('networks')
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
        Schema::drop('groups');
    }
}
