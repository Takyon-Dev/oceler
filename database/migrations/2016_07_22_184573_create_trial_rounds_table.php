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

          $table->foreign('trial_id')
                ->references('id')->on('trials')
                ->onDelete('cascade');

          $table->foreign('factoidset_id')
                ->references('id')->on('factoidsets')
                ->onDelete('cascade');

          $table->foreign('countryset_id')
                ->references('id')->on('countrysets')
                ->onDelete('cascade');

          $table->foreign('nameset_id')
                ->references('id')->on('namesets')
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
        Schema::drop('rounds');
    }
}
