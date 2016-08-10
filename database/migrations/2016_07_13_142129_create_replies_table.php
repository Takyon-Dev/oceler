<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replies', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('message_id')->unsigned();
            $table->text('message');
            $table->timestamps();

            // Add foreign key on id field in messages table
            $table->foreign('message_id')->references('id')->on('messages');
            $table->foreign('user_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

      Schema::drop('replies');

    }
}
