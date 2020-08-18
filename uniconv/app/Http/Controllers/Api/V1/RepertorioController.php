<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Documento;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\SoapControllerTitulus;
use App\Service\QueryTitulusBuilder;
use Artisaninweb\SoapWrapper\SoapWrapper;

class RepertorioController extends Controller
{

    protected $sc;

    public function __construct() {
        $this->sc = new SoapControllerTitulus(new SoapWrapper);
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


    public function getminimal($id){
        $this->sc = new SoapControllerTitulus(new SoapWrapper);        

        $findparam = new \App\FindParameter([
            'rules' => [
                [
                    'field' => '/doc/repertorio/@numero',
                    'operator' => '=',
                    'value' => $id
                ],
            ],
            'limit' => 1,
        ]);          

        $queryBuilder = new QueryTitulusBuilder(new Documento, null, $this->sc, $findparam);

        return $queryBuilder->build()->get()->first();

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {        
        //la load utilizza il physdoc o nrecord
        //va tradotta la risposta 
        $response = $this->sc->load($id);

        $objResult = simplexml_load_string($result);
        $model = new Documento;
    
        $arr= QueryTitulusBuilder::xmlToArray($objResult->document->doc, []);
        $model->fill($arr);

        return $model;
    }
    
    public function query(Request $request){
        
        $this->sc = new SoapControllerTitulus(new SoapWrapper);

        //chiamata a titulus che impersona il chiamante 

        // $pers =  Auth::user()->personaleRespons()->first(); 
        // $ctrPers = new PersonaInternaController();
        // $persint = $ctrPers->getminimalByName($pers->utenteNomepersona);        
        // $result = $this->sc->setWSUser($persint->loginName,$persint->matricola);

        //Coac
        //doc_repertoriocod = "COAC"
        $parameters = $request->json()->all();
        array_push($parameters['rules'],[
            "operator" => "=",
            "field" => "doc_repertoriocod",                
            "value" => "Coac"
        ]);

        $fp = new \App\FindParameter($parameters);

        $queryBuilder = new QueryTitulusBuilder(new Documento, $request, $this->sc, $fp);
        
        return $queryBuilder->build()->paginate();               
    }        

}
