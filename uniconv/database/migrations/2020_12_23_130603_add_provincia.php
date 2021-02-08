<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProvincia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 
        Schema::table('aziende', function($table) {
            $table->string('provincia', 2)->nullable();
            $table->string('part_iva', 13)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('aziende', function($table) {
            $table->dropColumn('provincia');
            $table->dropColumn('part_iva');
        });
    }
}
