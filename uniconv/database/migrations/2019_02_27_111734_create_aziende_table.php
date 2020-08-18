<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//php artisan migrate:fresh --seed

//php artisan make:migration create_aziende_table
class CreateAziendeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aziende', function (Blueprint $table) {
            $table->increments('id');

            //Titolare o legale rappresentante
            $table->string('nome');
            $table->string('cognome');    

            $table->string('denominazione');                 

            $table->string('contatto_email')->nullable();
            $table->string('pec_email')->nullable();

            $table->string('azienda_id_esterno')->nullable();   

            $table->string('cod_fisc')->nullable();     
            $table->string('indirizzo1')->nullable();
            $table->string('stato')->nullable();
            $table->string('comune')->nullable();
            $table->string('cap')->nullable();            
            
            $table->string('phone_number')->nullable();
            $table->string('cell_phone')->nullable();

            $table->enum('status', array_keys(trans('globals.company_status')))->default('active');                      
        
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aziende');
    }
}
