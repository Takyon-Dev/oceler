<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFactoidKeywordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('factoid_keyword', function (Blueprint $table) {

          $table->increments('id');
          $table->timestamps();

          $table->integer('factoid_id')->unsigned();
          $table->integer('keyword_id')->unsigned();

          $table->foreign('factoid_id')
                ->references('id')->on('factoids')
                ->onDelete('cascade');

          $table->foreign('keyword_id')
                ->references('id')->on('keywords')
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
        Schema::drop('factoid_keyword');
    }
}
