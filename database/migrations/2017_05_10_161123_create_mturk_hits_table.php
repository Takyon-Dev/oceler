<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMturkHitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mturk_hits', function(Blueprint $table){
          $table->increments('id');
          $table->integer('user_id')->unsigned();
          $table->string('hit_id');
          $table->string('assignment_id');
          $table->string('worker_id');
          $table->timestamps();

          $table->foreign('user_id')
                ->references('id')->on('users')
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
      Schema::table('mturk_hits', function(Blueprint $table){
        Schema::drop('mturk_hits');
      });
    }
}
