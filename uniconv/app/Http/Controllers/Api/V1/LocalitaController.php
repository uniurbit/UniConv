<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Comune;
use App\Provincia;
use App\Http\Controllers\Controller;
use Exception;

class LocalitaController extends Controller
{
    //controller per l'accesso alle risorse comune e provicia

    //comuni/{id}
    public function getComuneById($codice)
    {
        $comune = new Comune;        

        $result = $comune->find($codice);

        return $result;
    }

   
    public function getComuniByCodiceProvincia($codice)
    {
        $prov = new Provincia;        

        $result = $prov->find($codice);

        return $result ? $result->comuni : [];
    }    

    //comuni?prov=pu
    public function getComuni(Request $request) {
        return $this->getComuniByCodiceProvincia( $request->prov);                
    }

    //provincie/{codice}
    public function getProvinciaById($codice)
    {
        try{
            $prov = new Provincia;        

            $result = $prov->find($codice);

            return $result;
        }catch(Exception $e){
            return response()->json(['error'=> $e->getMessage()], 404); 
        }
    }

    //provincie
    public function getProvincie()
    {
        try{
            return Provincia::all();
        }catch(Exception $e){
            //"ORA-01017: invalid username/password; logon denied"
            return response()->json(['error'=> $e->getMessage()], 404); 
        }

    }
}
