<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('names', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('nameset_id')->unsigned();
            $table->text('name');
            $table->timestamps();

            // Add foreign key on id field in messages table
            $table->foreign('nameset_id')->references('id')->on('namesets');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('names');
    }
}
