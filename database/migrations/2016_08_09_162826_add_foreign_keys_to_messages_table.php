<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('messages', function (Blueprint $table) {
        $table->integer('share_id')->after('factoid_id')->unsigned()->nullable();
        $table->foreign('share_id')->references('id')->on('messages');

        $table->foreign('factoid_id')->references('id')->on('factoids');
        $table->foreign('user_id')->references('id')->on('users');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('messages', function (Blueprint $table) {
        $table->dropForeign('messages_factoid_id_foreign');
        $table->dropForeign('messages_user_id_foreign');
        $table->dropForeign('messages_share_id_foreign');
        $table->dropColumn('share_id');
      });
    }
}
