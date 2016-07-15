<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forwards', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('sender_id');
            $table->integer('message_id')->unsigned();
            $table->timestamps();

            // Add foreign key on id field in messages table
            $table->foreign('message_id')->references('id')->on('messages');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::drop('forwards');
    }
}
