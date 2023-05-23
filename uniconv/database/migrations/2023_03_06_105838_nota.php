<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Nota extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
           
        DB::table('attachmenttypes')->insert([   
            'codice' => 'NOTA_INTEGRATA',      
            'gruppo' => 'emissione',
            'descrizione' => 'Nota integrata',   
            'descrizione_compl' => 'Nota integrata',   
            'parent_type' => Scadenza::class,         
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
