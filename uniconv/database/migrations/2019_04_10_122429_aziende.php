<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
    //php artisan migrate --seed
    //php artisan migrate:refresh --seed
    //php artisan migrate:fresh --seed
class Aziende extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('convenzione_azienda', function (Blueprint $table) {
            $table->unsignedInteger('convenzione_id');            
            $table->unsignedInteger('azienda_id');            
            $table->timestamps();
            
            $table->foreign('convenzione_id')
                ->references('id')
                ->on('convenzioni')
                ->onDelete('cascade');
            
            $table->foreign('azienda_id')
                ->references('id')
                ->on('aziende')
                ->onDelete('cascade');

            $table->primary(['convenzione_id', 'azienda_id'], 'convenzione_azienda_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('convenzione_azienda');
    }
}
