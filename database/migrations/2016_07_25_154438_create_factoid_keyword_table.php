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

          # `book_id` and `tag_id` will be foreign keys, so they have to be unsigned
          #  Note how the field names here correspond to the tables they will connect...
          # `book_id` will reference the `books table` and `tag_id` will reference the `tags` table.
          $table->integer('factoid_id')->unsigned();
          $table->integer('keyword_id')->unsigned();

          # Make foreign keys
          $table->foreign('factoid_id')->references('id')->on('factoids');
          $table->foreign('keyword_id')->references('id')->on('keywords');
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
