<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrialUserArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::create('trial_user_archive', function (Blueprint $table) {

             $table->increments('id');
             $table->timestamps();

             $table->integer('trial_id')->unsigned();
             $table->integer('user_id')->unsigned();

             $table->foreign('trial_id')
                   ->references('id')->on('trials')
                   ->onDelete('cascade');

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
         Schema::drop('trial_user_archive');
     }
}
