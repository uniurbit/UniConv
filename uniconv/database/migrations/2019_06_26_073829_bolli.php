<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Bolli extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bolli', function (Blueprint $table) {
            //$table->increments('id');                
            $table->unsignedInteger('convenzioni_id');                
            $table->foreign('convenzioni_id')->references('id')->on('convenzioni')->onDelete('cascade');;   
            $table->string('tipobolli_codice',25);
            $table->foreign('tipobolli_codice')->references('codice')->on('tipobolli');   
            $table->unsignedInteger('num_bolli')->nullable(); 

            $table->primary(['convenzioni_id', 'tipobolli_codice']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bolli');
    }
}
