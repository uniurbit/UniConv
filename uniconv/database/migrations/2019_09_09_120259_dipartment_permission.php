<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;

class DipartmentPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {      

        $role = Role::where('name','super-admin')->first();
        if ($role!=null){
            Permission::create(['name' => 'all dipartimenti']);    
            $role->givePermissionTo('all dipartimenti');
        }

        $role = Role::where('name','op_approvazione')->first();
        if ($role!=null){
            $role->givePermissionTo('view attachments');
        }
    
        //004939	Dipartimento di Studi Umanistici (DISTUM) Dipartimento di Studi Umanistici (DISTUM) SI000089 PI000073
        DB::table('mappinguffici')->insert([                          
            'unitaorganizzativa_uo' => '004939',
            'descrizione_uo' => 'Dipartimento di Studi Umanistici (DISTUM)',                     
            'strutturainterna_cod_uff' => 'SI000089',
            'descrizione_uff' => 'Dipartimento di Studi Umanistici (DISTUM)',                     
        ]);

        //004424	Dipartimento di Economia, Società, Politica (DESP) - Dipartimento di Economia, Società, Politica (DESP) SI000058 PI000073
        DB::table('mappinguffici')->insert([                          
            'unitaorganizzativa_uo' => '004424',
            'descrizione_uo' => 'Dipartimento di Economia, Società, Politica (DESP)',                     
            'strutturainterna_cod_uff' => 'SI000058',
            'descrizione_uff' => 'Dipartimento di Economia, Società, Politica (DESP)',                     
        ]);

        //004419	Dipartimento di Giurisprudenza - Dipartimento di Giurisprudenza SI000062 PI000056
        DB::table('mappinguffici')->insert([                          
            'unitaorganizzativa_uo' => '004419',
            'descrizione_uo' => 'Dipartimento di Giurisprudenza',                     
            'strutturainterna_cod_uff' => 'SI000062',
            'descrizione_uff' => 'Dipartimento di Giurisprudenza',                     
        ]);

        //005579	Dipartimento di Scienze della Comunicazione, Studi Umanistici e Internazionali: Storia, Culture, Lingue, Letterature, Arti, Media (DISCUI) - Dipartimento di Scienze della Comunicazione, Studi Umanistici e Internazionali: Storia, Culture, Lingue, Letterature, Arti, Media - DISCUI SI000087 PI000056
        DB::table('mappinguffici')->insert([                          
            'unitaorganizzativa_uo' => '005579',
            'descrizione_uo' => 'Dipartimento di Scienze della Comunicazione, Studi Umanistici e Internazionali (DISCUI)',                     
            'strutturainterna_cod_uff' => 'SI000087',
            'descrizione_uff' => 'Dipartimento di Scienze della Comunicazione, Studi Umanistici e Internazionali: Storia, Culture, Lingue, Letterature, Arti, Media - DISCUI',                     
        ]);
    
        //005019	Dipartimento di Scienze Biomolecolari (DISB) - Dipartimento di Scienze Biomolecolari SI000060 PI000083
        DB::table('mappinguffici')->insert([                          
            'unitaorganizzativa_uo' => '005019',
            'descrizione_uo' => 'Dipartimento di Scienze Biomolecolari (DISB)',                     
            'strutturainterna_cod_uff' => 'SI000060',
            'descrizione_uff' => 'Dipartimento di Scienze Biomolecolari',                     
        ]);

       
        //004919	Dipartimento di Scienze Pure e Applicate (DiSPeA) - Dipartimento di Scienze Pure e Applicate - DISPeA SI000084 PI000083
        DB::table('mappinguffici')->insert([                          
            'unitaorganizzativa_uo' => '004919',
            'descrizione_uo' => 'Dipartimento di Scienze Pure e Applicate (DiSPeA)',                     
            'strutturainterna_cod_uff' => 'SI000084',
            'descrizione_uff' => 'Dipartimento di Scienze Pure e Applicate - DISPeA',                     
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
