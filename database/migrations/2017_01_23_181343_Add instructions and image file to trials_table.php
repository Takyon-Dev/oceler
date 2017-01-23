<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstructionsAndImageFileToTrialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('trials', function (Blueprint $table) {

        $table->String('instr_path')->after('name');
        $table->String('instr_img_path')->after('instr_path');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('trials', function(Blueprint $table){

        $table->dropColumn('instr_path');
        $table->dropColumn('instr_img_path');

      });
    }
}
