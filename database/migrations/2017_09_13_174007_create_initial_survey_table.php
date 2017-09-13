<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitialSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('initial_survey', function (Blueprint $table) {
          $table->string('id')->unique();
          $table->integer('user_id')->unsigned();
          $table->integer('understand');
          $table->integer('confident');
          $table->string('email');
          $table->text('comments');
          $table->timestamps();

          $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('initial_survey');
    }
}
