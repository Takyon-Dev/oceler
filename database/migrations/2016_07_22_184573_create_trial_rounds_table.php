<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrialRoundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('rounds', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('trial_id')->unsigned();
          $table->integer('round');
          $table->integer('round_timeout');
          $table->integer('factoidset_id')->unsigned();
          $table->integer('countryset_id')->unsigned();
          $table->integer('nameset_id')->unsigned();
          $table->timestamps();

          $table->foreign('trial_id')->references('id')->on('trials');
          $table->foreign('factoidset_id')->references('id')->on('factoidsets');
          $table->foreign('countryset_id')->references('id')->on('countrysets');
          $table->foreign('nameset_id')->references('id')->on('namesets');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('trial_rounds');
    }
}
