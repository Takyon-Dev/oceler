<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoundEarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('round_earnings', function(Blueprint $table){

          $table->increments('id');
          $table->integer('trial_id')->unsigned();
          $table->integer('user_id')->unsigned();
          $table->integer('round_id')->unsigned();
          $table->float('earnings');
          $table->timestamps();

          $table->unique(array('trial_id', 'user_id', 'round_id'));

          $table->foreign('trial_id')
                ->references('id')->on('trials')
                ->onDelete('cascade');

          $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

          $table->foreign('round_id')
                ->references('id')->on('rounds')
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
        Schema::drop('round_earnings');
    }
}
