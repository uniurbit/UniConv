<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Scadenze extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    //php artisan migrate --seed
    //php artisan migrate:refresh --seed
    //php artisan migrate:fresh --seed
    public function up()
    {
        Schema::create('scadenze', function (Blueprint $table) {
            $table->increments('id');                        
            $table->unsignedInteger('convenzione_id');

            $table->date('data_tranche')->nullable();
            $table->decimal('dovuto_tranche',12,2)->nullable();

            $table->enum('tipo_emissione', array_keys(trans('globals.tipo_emissione')))->nullable();                        

            $table->date('data_emisrichiesta')->nullable();
            $table->string('protnum_emisrichiesta')->nullable();

            $table->date('data_fattura')->nullable();
            $table->string('num_fattura')->nullable();

            $table->date('data_ordincasso')->nullable();
            $table->string('num_ordincasso')->nullable();

            $table->string('prelievo')->nullable();

            $table->string('note')->nullable();

            $table->foreign('convenzione_id')->references('id')->on('convenzioni');

            //stato del workflow della scandenza
            $table->string('state')->default('attivo')->nullable(); 

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
        Schema::dropIfExists('scadenze');
    }
}
