<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;
//php artisan db:seed --class=RolesTableSeeder 
//php artisan migrate:fresh --seed
class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'create convenzioni']);
        Permission::create(['name' => 'update convenzioni']);
        Permission::create(['name' => 'delete convenzioni']);
        Permission::create(['name' => 'view convenzioni']);        

        //permessi per passaggio di stato
        Permission::create(['name' => 'store_to_approvato convenzioni']);
        Permission::create(['name' => 'store_to_inapprovazione convenzioni']);
        Permission::create(['name' => 'store_proposta convenzioni']);
        Permission::create(['name' => 'store_validazione convenzioni']);
        Permission::create(['name' => 'firma_da_controparte1 convenzioni']);
        Permission::create(['name' => 'firma_da_controparte2 convenzioni']);
        Permission::create(['name' => 'firma_da_direttore1 convenzioni']);
        Permission::create(['name' => 'firma_da_direttore2 convenzioni']);                
        Permission::create(['name' => 'repertorio convenzioni']); 

        Permission::create(['name' => 'cancella_sottoscrizione_contr convenzioni']);                
        Permission::create(['name' => 'cancella_sottoscrizione_uniurb convenzioni']);                

        //permessi transizioni scadenze
        Permission::create(['name' => 'richiestaemissione scadenze']);        
        Permission::create(['name' => 'emissione scadenze']);        
        Permission::create(['name' => 'registrazionepagamento scadenze']);        
        Permission::create(['name' => 'ordineincasso scadenze']); 
        Permission::create(['name' => 'richiestapagamento scadenze']); 
                
                
        Permission::create(['name' => 'create usertasks']);
        Permission::create(['name' => 'update usertasks']);
        Permission::create(['name' => 'delete usertasks']);
        Permission::create(['name' => 'view usertasks']);        
        
        //permessi per passaggio di stato
        Permission::create(['name' => 'store_aperto usertasks']);
        Permission::create(['name' => 'store_presaincarico usertasks']);
        Permission::create(['name' => 'store_conerrori usertasks']);
        Permission::create(['name' => 'store_eseguito usertasks']);
        
        Permission::create(['name' => 'create attachments']);
        Permission::create(['name' => 'update attachments']);
        Permission::create(['name' => 'delete attachments']);
        Permission::create(['name' => 'view attachments']);        

        //permessi per gestione
        Permission::create(['name' => 'create tasktypes']);
        Permission::create(['name' => 'update tasktypes']);
        Permission::create(['name' => 'delete tasktypes']);
        Permission::create(['name' => 'view tasktypes']);  
        
        //permessi per scadenze
        Permission::create(['name' => 'create scadenze']);
        Permission::create(['name' => 'update scadenze']);
        Permission::create(['name' => 'delete scadenze']);
        Permission::create(['name' => 'view scadenze']);  

        //permessi mappingruoli
        Permission::create(['name' => 'create mappingruoli']);
        Permission::create(['name' => 'update mappingruoli']);
        Permission::create(['name' => 'delete mappingruoli']);
        Permission::create(['name' => 'view mappingruoli']);  

        //permessi ricerche
        Permission::create(['name' => 'search all convenzioni']);  
        Permission::create(['name' => 'search orgunit convenzioni']);  

        Permission::create(['name' => 'search all scadenze']);  
        Permission::create(['name' => 'search orgunit scadenze']);  
        Permission::create(['name' => 'all dipartimenti']);   

        //convenzioni
        Permission::create(['name' => 'ui convenzioni amm']);                
        Permission::create(['name' => 'ui convenzioni dip']);    

        // create roles and assign created permissions

        $role = Role::create(['name' => 'op_docente']) 
            ->givePermissionTo(['view convenzioni', 'view scadenze', 'view attachments', 'search orgunit convenzioni', 'search orgunit scadenze']);

        // this can be done as separate statements
        $role = Role::create(['name' => 'viewer']);
        $role->givePermissionTo(['view convenzioni', 'view scadenze', 'view attachments', 'search orgunit convenzioni', 'search orgunit scadenze']);

        // or may be done by chaining
        $role = Role::create(['name' => 'op_contabilita'])
            ->givePermissionTo(['store_eseguito usertasks', 'emissione scadenze', 'ordineincasso scadenze', 'update scadenze', 'view scadenze', 'view convenzioni','search orgunit convenzioni', 'search orgunit scadenze', 'view attachments' ]);

        // or may be done by chaining
        $role = Role::create(['name' => 'op_approvazione'])
            ->givePermissionTo(['store_eseguito usertasks', 'store_validazione convenzioni', 'view convenzioni', 'search orgunit convenzioni', 'search orgunit scadenze', 'view attachments']);

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());        
       

        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());
        $role->revokePermissionTo('search all convenzioni');
        $role->revokePermissionTo('search all scadenze');
        $role->revokePermissionTo('ui convenzioni amm');
        $role->revokePermissionTo('all dipartimenti');
        
        $role = Role::create(['name' => 'admin_amm']);
        $role->givePermissionTo(Permission::all());
        $role->revokePermissionTo('search all convenzioni');
        $role->revokePermissionTo('search all scadenze');
        $role->revokePermissionTo('ui convenzioni dip');
        $role->revokePermissionTo('all dipartimenti');
        
        $role = Role::create(['name' => 'limited']); 
        $role->givePermissionTo(['search orgunit convenzioni', 'search orgunit scadenze']);     
       
        $role = Role::create(['name' => 'op_uff_bilancio']); 
        $role->givePermissionTo(['view convenzioni', 'view scadenze', 'view attachments', 'search all convenzioni', 'search all scadenze', 'all dipartimenti']);     

        $this->command->info('created roles');

    }
}
