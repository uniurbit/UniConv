<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Attach extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('attachmenttypes')->insert([   
            'codice' => 'ALLEGATO_TECNICO',      
            'gruppo' => 'repertoriazione',
            'descrizione' => 'Allegato tecnico',   
            'descrizione_compl' => 'Allegato tecnico',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'ALLEGATO',      
            'gruppo' => 'repertoriazione',
            'descrizione' => 'Allegato',   
            'descrizione_compl' => 'Allegato',   
            'parent_type' => Convenzione::class,         
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
