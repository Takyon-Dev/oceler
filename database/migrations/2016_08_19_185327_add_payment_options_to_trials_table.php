<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentOptionsToTrialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trials', function(Blueprint $table){

          $table->boolean('pay_time_factor')->after('pay_correct');
          $table->float('payment_per_solution')->after('pay_time_factor');
          $table->float('payment_base')->after('payment_per_solution');

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

        $table->dropColumn('pay_time_factor');
        $table->dropColumn('payment_per_solution');
        $table->dropColumn('payment_base');

      });
    }
}
