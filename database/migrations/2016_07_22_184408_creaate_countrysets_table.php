<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreaateCountrysetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::create('countrysets', function (Blueprint $table) {
             $table->increments('id');
             $table->text('name');
             $table->text('location');
             $table->timestamps();

             $table->softDeletes();

         });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         Schema::drop('countrysets');
     }
}
