<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;
use App\MappingRuolo;

class UfficiRuoliAmm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $role = Role::where('name','admin_amm')->first();
        if ($role!=null){            
            //aggiunti i vari mapping 
            //Ufficio Contratti e Convenzioni - Settore Acquisti - Area Econ. Finanziaria
            //Ufficio Gare - Settore Acquisti - Area Econ. Finanziaria
            $this->insertOffice(['005339','005340'], 'admin_amm');
        }
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

    private function insertOffice(Array $offices, $rolename){
        $role = Role::where('name', $rolename)->first();
        foreach ($offices as $office) {
            $mp = new MappingRuolo();
            $mp->unitaorganizzativa_uo = $office;
            $uo = $mp->unitaorganizzativa()->get()->first();
            //se esiste l'unità organizzativa
            if ($uo){
                $mp->descrizione_uo = $uo->descr;
                $mp->role_id = $role->id;
                $mp->save();
            }
        }       
    }
}
