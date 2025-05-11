<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableToInitialSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     function up()
     {
         DB::statement('ALTER TABLE `initial_survey` MODIFY `understand` INTEGER NULL;');
         DB::statement('ALTER TABLE `initial_survey` MODIFY `confident` INTEGER NULL;');
     }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::statement('ALTER TABLE `initial_survey` MODIFY `understand` INTEGER NOT NULL;');
      DB::statement('ALTER TABLE `initial_survey` MODIFY `confident` INTEGER NOT NULL;');
    }
}
