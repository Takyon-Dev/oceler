<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnswerKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answer_keys', function(Blueprint $table){

            $table->increments('id');
            $table->integer('factoidset_id')->unsigned();
            $table->integer('solution_category_id')->unsigned();
            $table->text('solution');
            $table->timestamps();

            $table->foreign('factoidset_id')
                  ->references('id')->on('factoids')
                  ->onDelete('cascade');

            $table->foreign('solution_category_id')
                  ->references('id')->on('solution_categories')
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
        Schema::drop('answer_keys');
    }
}
