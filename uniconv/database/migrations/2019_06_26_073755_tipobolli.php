<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Tipobolli extends Migration
{
    //php artisan migrate --seed
    //php artisan migrate:refresh --seed
    //php artisan migrate:fresh --seed
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipobolli', function (Blueprint $table) {
            $table->increments('id');                
            $table->string('codice',25)->unique();                
            $table->string('descrizione',255);
            $table->decimal('importo',12,2)->nullable();     
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
        Schema::dropIfExists('tipobolli');
    }
}
