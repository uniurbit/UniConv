<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserTasksClosingUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usertasks', function($table) {
            $table->unsignedInteger('closing_user_id')->after('owner_user_id')->nullable();              
            $table->foreign('closing_user_id')
                ->references('id')
                ->on('users');                 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usertasks', function($table) {
           $table->dropForeign(['closing_user_id']);
           $table->dropColumn('closing_user_id');   
        });
    }
}
