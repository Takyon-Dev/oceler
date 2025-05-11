<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('searches', function (Blueprint $table) {

          $table->increments('id');
          $table->integer('user_id')->unsigned();
          $table->integer('trial_id')->unsigned();
          $table->text('search_term');
          $table->integer('factoid_id')->unsigned()->nullable();
          $table->timestamps();

          $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

          $table->foreign('trial_id')
                ->references('id')->on('trials')
                ->onDelete('cascade');

          $table->foreign('factoid_id')
                ->references('id')->on('factoids')
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
        Schema::drop('searches');
    }
}
