<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUfficio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('mappinguffici')->insert([                          
            'unitaorganizzativa_uo' => '005340',
            'descrizione_uo' => 'Ufficio Contratti e Convenzioni - Settore Acquisti - Area Econ. Finanziaria',                     
            'strutturainterna_cod_uff' => 'SI000093',
            'descrizione_uff' => 'Ufficio Contratti e Convenzioni',                     
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
