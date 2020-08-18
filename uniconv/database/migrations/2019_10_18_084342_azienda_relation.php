<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AziendaRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('convenzione_azienda', function (Blueprint $table) {
            //rimuovi la relazione
            $table->dropForeign(['azienda_id']);
            //ricostruisci relazione
            $table->foreign('azienda_id')
                ->references('id')
                ->on('aziende');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
