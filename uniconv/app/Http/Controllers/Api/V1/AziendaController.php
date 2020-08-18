<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Azienda;
use App\Http\Controllers\Controller;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;

class AziendaController extends Controller
{

    public function __construct() {
       // $this->middleware(['isAdmin']); //isAdmin middleware lets only users with a //specific permission permission to access these resources
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        
	        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return  Azienda::find($id);
    }
    
    public function getIndirizzoResidenza($id)
    {
        //Azienda::find($id)->indirizzo_residenza senza () ferma la chaining query costrain 
        $res = Azienda::find($id)->indirizzo_residenza()->first();
        return [
            'indirizzo' => ucwords(strtolower($res->indirizzo)),
            'cd_cap' => $res->cd_cap,
            'comune' => $res->comune
        ];
    }

    public function query(Request $request){

        $app = $request->json();
        $parameters = $request->json()->all();
        $parameters['order_by'] = 'id_esterno,desc';
        $findparam =new \App\FindParameter($parameters);      

        $queryBuilder = new QueryBuilderForceInsensitive(new Azienda, $request, $findparam);
                
        return $queryBuilder->build()->paginate();               
    }        

}
