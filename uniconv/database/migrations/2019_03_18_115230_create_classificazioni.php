<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//php artisan migrate:fresh --seed
//php artisan make:migration create_classificazioni
class CreateClassificazioni extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classificazioni', function (Blueprint $table) {
            $table->increments('id');                
            $table->string('codice', 20)->unique();                
            $table->string('descrizione',255);            

            $table->softDeletes();
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
        Schema::dropIfExists('classificazioni');
    }
}
