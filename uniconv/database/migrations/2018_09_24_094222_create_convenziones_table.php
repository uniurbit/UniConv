<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//php artisan migrate --seed
//php artisan migrate:refresh --seed

//girano le migration con reset del db
//php artisan migrate:fresh --seed

//nome tabelle plurale
//nome campi snake_case
class CreateConvenzionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     
        Schema::create('tipopagamenti', function (Blueprint $table) {
            $table->increments('id');                
            $table->string('codice',6)->unique();                
            $table->string('descrizione',255);
        });

        Schema::create('convenzioni', function (Blueprint $table) {
            $table->increments('id');
            $table->string('schematipotipo',20)->default('schematipo');
            
            $table->string('descrizione_titolo',255);
            $table->unsignedInteger('dipartimemto_cd_dip')->nullable();

            $table->string('resp_scientifico',50);
            $table->boolean('conforme')->default(1); //schema tipo

            $table->enum('ambito',['istituzionale','commerciale'])->nullable();            
            $table->unsignedInteger('durata')->nullable();   //mesi

            $table->text('prestazioni')->nullable();        
            $table->decimal('corrispettivo',12,2)->nullable();  
                            
            $table->decimal('importo',12,2)->nullable();                                                           

            $table->string('tipopagamenti_codice',6)->nullable();  
            $table->foreign('tipopagamenti_codice')->references('codice')->on('tipopagamenti');            
            $table->string('convenzione_type',6)->nullable();                        

            $table->softDeletes();
            $table->date('data_inizio_conv')->nullable();
            $table->date('data_fine_conv')->nullable();
            $table->date('data_sottoscrizione')->nullable();
                        
            //utente ultima modifica            
            $table->unsignedInteger('user_id');  
            $table->string('user_email',50)->nullable();              
            $table->timestamps();

            //fascicolo
            $table->string('titolario_classificazione',50)->nullable();  
            $table->string('oggetto_fascicolo')->nullable(); 
            $table->string('nrecord')->nullable(); 
            //numero fascicolo
            $table->string('numero')->nullable(); 

            //sottoscrizione 
            $table->enum('stipula_type', array_keys(trans('globals.stipula_type')))->nullable();                
            $table->enum('stipula_format', array_keys(trans('globals.stipula_format')))->nullable(); 

            $table->boolean('bollo_virtuale')->default(false);

            //da Titulus numero di repertorio
            $table->string('num_rep')->nullable(); 

            $table->string('current_place')->nullable();            
            //dubbio se salavare o meno
            $table->string('unitaorganizzativa_uo',10)->nullable();  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('convenzioni');
        Schema::dropIfExists('tipopagamenti');
    }
}
