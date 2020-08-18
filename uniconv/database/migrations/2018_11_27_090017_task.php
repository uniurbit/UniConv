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
class Task extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('tasktypes', function (Blueprint $table) {
            $table->increments('id');  
            $table->string('code',20)->unique();                                          
            $table->string('name');                                          
            $table->string('descrizione',255);
            $table->string('subject')->nullable(); 
            $table->string('content')->nullable();             
        });

        Schema::create('usertasks', function (Blueprint $table) {
            $table->increments('id');
            //modello di rifermento per la relazione 1:N
            //esempio usertasks riferito alla convenzione
            $table->morphs('model');            
            
            $table->unsignedInteger('tasktypes_id')->nullable();  
            //descrizione lunga del task
            $table->string('description')->nullable();  
            //inputabile dall'utente descrizione breve del task
            $table->string('subject')->nullable();  
            //stato del workflow del task
            $table->string('state')->nullable(); 

            //chi ha aperto owner del task  
            $table->unsignedInteger('owner_user_id');  

            //foreignkey responsabile unità organizzativa
            $table->unsignedInteger('respons_v_ie_ru_personale_id_ab');  
            $table->string('unitaorganizzativa_uo',10)->nullable();  
            //ci dice che un task è attivo fino a quando il modello collegato al task è nello stato contenuto nel campo "workflow_place" 
            //usato per elaborazione automatica stato task.            
            //alla transizione del workflow possono essere controllati tutti i task associati al documento e chiuderli...            
            $table->string('workflow_place',30)->nullable();     
            $table->string('workflow_transition',30)->nullable();     

            $table->text('data')->nullable();

            $table->timestamps();  

            $table->foreign('tasktypes_id')
                ->references('id')
                ->on('tasktypes');      
                
            $table->foreign('owner_user_id')
                ->references('id')
                ->on('users');          
        });

        Schema::create('logtransitions', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('model');
            $table->string('transition_leave');  
            $table->unsignedInteger('user_id');  
            $table->string('user_description')->nullable();  
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users');              
        });

        
        Schema::create('model_has_assignments', function (Blueprint $table) {
            //l'utente deve essere interno o manteniamo un riferimento esterno alla tabella del personale ... 

            $table->unsignedInteger('user_id')->nullable();   
            $table->unsignedInteger('v_ie_ru_personale_id_ab');   

            $table->string('cd_tipo_posizorg',10)->nullable();  

            $table->morphs('model');            
                    
            $table->foreign('user_id')
                ->references('id')
                ->on('users');  

            $table->primary(['v_ie_ru_personale_id_ab', 'model_id', 'model_type'],
                    'model_has_assignments_model_id_model_type_primary');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('logtransitions');
        Schema::drop('usertasks');
    }
}
