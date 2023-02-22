<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\TipoBollo;

class ChangeBolli extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //$table->foreign('tipobolli_codice')->references('codice')->on('tipobolli');   
        if (!Schema::hasColumn('bolli', 'num_righe'))
        {
            Schema::table('bolli', function (Blueprint $table) {            
                $table->unsignedInteger('num_righe')->nullable();
                $table->unsignedInteger('num_pagine')->nullable();            
            });
        }

        $tipobollo = TipoBollo::where('codice','BOLLO_ALLEGATI')->first();
        if ($tipobollo==null){
            DB::table('tipobolli')->insert([                          
                'codice' => 'BOLLO_ALLEGATI',
                'descrizione' => 'Bollo allegato',                     
                'importo' => 16.00
            ]);
        }

        if (!Schema::hasColumn('convenzioni', 'data_stipula'))
        {
            Schema::table('convenzioni', function (Blueprint $table) {
                $table->date('data_stipula')->nullable();   //rappresenta la data di stipula della convenzione (la data di firma della convenzione)
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('bolli', 'num_righe'))
        {
            Schema::table('bolli', function (Blueprint $table){
                $table->dropColumn('num_righe');
                $table->dropColumn('num_pagine');            
            });
        }
    }
}
