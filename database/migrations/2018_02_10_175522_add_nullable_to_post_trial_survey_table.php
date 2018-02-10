<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableToPostTrialSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     function up()
     {
         DB::statement('ALTER TABLE `post_trial_survey` MODIFY `enjoy` INTEGER NULL;');
         DB::statement('ALTER TABLE `post_trial_survey` MODIFY `confident` INTEGER NULL;');
     }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::statement('ALTER TABLE `post_trial_survey` MODIFY `enjoy` INTEGER NOT NULL;');
      DB::statement('ALTER TABLE `post_trial_survey` MODIFY `confident` INTEGER NOT NULL;');
    }
}
