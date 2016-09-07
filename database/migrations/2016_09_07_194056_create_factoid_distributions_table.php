<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFactoidDistributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::create('factoid_distributions', function (Blueprint $table) {

             $table->increments('id');
             $table->timestamps();

             $table->integer('factoid_id')->unsigned();
             $table->integer('node');
             $table->integer('wave');

             $table->foreign('factoid_id')
                   ->references('id')->on('factoids')
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
         Schema::drop('factoid_distributions');
     }
}
