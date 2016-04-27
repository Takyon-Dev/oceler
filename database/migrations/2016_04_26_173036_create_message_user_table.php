<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('message_user', function (Blueprint $table) {

        $table->increments('id');
        $table->timestamps();

        $table->integer('message_id')->unsigned();
        $table->integer('user_id')->unsigned();

        # Make foreign keys
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
        Schema::drop('message_user');
    }
}
