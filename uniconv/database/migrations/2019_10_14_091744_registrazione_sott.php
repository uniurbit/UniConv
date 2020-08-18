<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Role;
use App\Permission;

class RegistrazioneSott extends Migration
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
            Permission::create(['name' => 'cancella_sottoscrizione_contr convenzioni']);                
            Permission::create(['name' => 'cancella_sottoscrizione_uniurb convenzioni']);     
            $role->givePermissionTo('cancella_sottoscrizione_contr convenzioni');
            $role->givePermissionTo('cancella_sottoscrizione_uniurb convenzioni');
        }

        $role = Role::where('name','admin')->first();
        if ($role!=null){                         
            $role->givePermissionTo('cancella_sottoscrizione_contr convenzioni');
            $role->givePermissionTo('cancella_sottoscrizione_uniurb convenzioni');
        }

        DB::table('attachmenttypes')->insert([   
            'codice' => 'LTU_FIRM_UNIURB_PROT',      
            'gruppo' => 'sottoscrizione',
            'descrizione' => 'Lettera di trasmissione sottoscrizione',   
            'descrizione_compl' => 'Lettera di trasmissione sottoscrizione già protocollata',   
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
        DB::table('attachmenttypes')->where('codice','=', 'LTU_FIRM_UNIURB_PROT')->delete();  
    }
}
