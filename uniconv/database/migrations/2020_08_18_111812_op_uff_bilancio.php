<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;

class OpUffBilancio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $role = Role::where('name','op_uff_bilancio')->first();                 
        if ($role == null){
            $perm = Permission::where('name', 'view convenzioni')->first();
            if($perm !=null){
                $role = Role::create(['name' => 'op_uff_bilancio']);                     
                $role->givePermissionTo(['view convenzioni', 'view scadenze', 'view attachments', 'search all convenzioni', 'search all scadenze', 'all dipartimenti']);
            }
        }                
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $role = Role::where('name','op_uff_bilancio')->first();          
        $role->delete();
    }
}
