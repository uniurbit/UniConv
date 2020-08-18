<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;

class ConvAmministrativa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       

        $role = Role::where('name','admin')->first();
        if ($role!=null){
            Permission::create(['name' => 'ui convenzioni dip']);    
            $role->givePermissionTo('ui convenzioni dip');         
            //significa che i dati di seed hanno già girato
            
             //creazione ruolo amministratore convenzione amministrative
            $role = Role::where('name','admin_amm')->first();
            if ($role == null){
                $role = Role::create(['name' => 'admin_amm']); 
                //creazione del ruolo            
                Permission::create(['name' => 'ui convenzioni amm']);                
                $role->givePermissionTo(Permission::all());
                $role->revokePermissionTo('search all convenzioni');
                $role->revokePermissionTo('search all scadenze');   
                $role->revokePermissionTo('ui convenzioni dip');       
            }        
        }

        $role = Role::where('name','super-admin')->first();
        if ($role!=null){
            $role->givePermissionTo('ui convenzioni amm');                      
            $role->givePermissionTo('ui convenzioni dip');                      
        }

        Schema::table('convenzioni', function (Blueprint $table) {
            //aggiungere tipo convenzione dip o amm          
            $table->enum('convenzione_from', array_keys(trans('globals.convenzione_from')))->nullable()->default('dip');  

            //aggiungere tipo rinnovo  
            $table->enum('rinnovo_type', array_keys(trans('globals.rinnovo_type')))->default('non_rinnovabile')->nullable();         
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Permission::delete(['name' => 'ui convenzioni dip']);  
        Permission::delete(['name' => 'ui convenzioni amm']);         
        $table->dropColumn('convenzione_from');   
        $table->dropColumn('rinnovo_type');   
        
    }
}
