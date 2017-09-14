<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostTrialSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::create('post_trial_survey', function (Blueprint $table) {
           $table->string('id')->unique();
           $table->integer('user_id')->unsigned();
           $table->integer('trial_id')->unsigned();
           $table->integer('enjoy');
           $table->integer('confident');
           $table->text('comments');
           $table->timestamps();

           $table->foreign('user_id')
                 ->references('id')->on('users')
                 ->onDelete('cascade');

           $table->foreign('trial_id')
                 ->references('id')->on('trials')
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
         Schema::drop('post_trial_survey');
     }
}
