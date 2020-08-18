<?php


use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;


class OpcontabiltaPerm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $role = Role::where('name','op_contabilita')->first();
        if ($role!=null){
            $role->givePermissionTo('view attachments');
            $role->givePermissionTo('ordineincasso scadenze');            
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
}
