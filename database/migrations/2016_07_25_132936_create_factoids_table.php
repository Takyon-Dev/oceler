<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFactoidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factoids', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('factoidset_id')->unsigned();
            $table->text('factoid');
            $table->timestamps();

            // Add foreign key on id field in messages table
            $table->foreign('factoidset_id')->references('id')->on('factoidsets');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('factoids');
    }
}
