<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFactoidsetIdToFactoidDistributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::table('factoid_distributions', function(Blueprint $table){

             $table->integer('factoidset_id')->after('updated_at')->unsigned();

             $table->foreign('factoidset_id')
                   ->references('id')->on('factoidsets')
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
         Schema::table('factoid_distributions', function (Blueprint $table) {

           $table->dropForeign('factoid_distributions_factoidset_id_foreign');
           $table->dropColumn('factoidset_id');

         });
     }
}
