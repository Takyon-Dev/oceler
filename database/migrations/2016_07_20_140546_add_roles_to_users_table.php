<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRolesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('users', function (Blueprint $table) {

          $table->integer('role_id')->after('password')->unsigned();

          # This field `author_id` is a foreign key that connects to the `id` field in the `authors` table
          $table->foreign('role_id')->references('id')->on('roles');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('users', function (Blueprint $table) {

          # ref: http://laravel.com/docs/5.1/migrations#dropping-indexes
          # combine tablename + fk field name + the word "foreign"
          $table->dropForeign('users_role_id_foreign');

          $table->dropColumn('role_id');
      });
    }
}
