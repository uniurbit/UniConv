<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReferenteCCsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referenti_c_c', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('nome',50);
            $table->string('cognome',50);
            $table->date('data_nascita');
            $table->string('luogo_nascita',50);
            $table->string('comune',10);
            $table->string('provincia',10);
            $table->string('via',50);
            $table->string('num_civico',10);
            $table->string('codice_fiscale',16);
            $table->string('qualifica',50);            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referenti_c_c');
    }
}
