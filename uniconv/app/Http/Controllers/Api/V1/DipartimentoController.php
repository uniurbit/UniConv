<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Dipartimento;
use App\Http\Controllers\Controller;
use App\UnitaOrganizzativa;
use Auth;
use Illuminate\Support\Facades\Cache;

class DipartimentoController extends Controller
{

    public function cacheKey()
    {
        return 'uniconv';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {                
        return Cache::rememberForever($this->cacheKey() . ':dipartimenti', function () {
            return Dipartimento::Dipartimenti()->get();
        });

       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //return Cache::rememberForever($this->cacheKey().'_'.$id.':dipartimento', function () {
            return Dipartimento::find($id);
        //});
    }

    public function query(Request $request){       

        $queryBuilder = new QueryBuilder(new Dipartimento, $request);
                
        return $queryBuilder->build()->paginate();       

    }

//sezione di metodi che non rispondono direttamente alle api
    public function decodeDescrizione($id){

        $conv = Cache::rememberForever($this->cacheKey().'_'.$id.':dipartimento', function () use($id){
            return Dipartimento::findOrFail($id);
        }); 
        return $conv->nome_breve;
    }

    public function getDocentiByDipartimento($codice)
    {
        $res = Dipartimento::find($codice)->docenti;
        return $res->map(function ($person) {
            return [
                'nome' => $person->nome,
                'cognome' => $person->cognome,
                'user_email' => $person->user_email,
            ];
        });
    }

    public function getDirettoreByDipartimento($codice){
        $res = Dipartimento::find($codice)->direttoreDipartimento()->first();        
        return [
            'nome' => ucwords(strtolower($res->nome)),
            'cognome' => ucwords(strtolower($res->cognome)),     
            'nome_esteso' => ucwords(strtolower($res->nome_esteso))            
        ];        
    }

    public function getDipartimentiByUser(){
        
        //se utente corrente afferisce ad un dipartimento restituire quello
        //se l'utente corrente afferisca al plesso restituire tutti i dipartimenti sottostanti
        //se l'utente corrente è super-admin oppure controllare permessi                 
        if (Auth::user()->hasPermissionTo('all dipartimenti')){
            return $this->index();
        }

        return $this->getUserDepartments();
    }

    public function getUserDepartments(){
           //se non ha il permesso viene filtrato per utente
           $pers = Auth::user()->personale()->first();
           $uo = $pers->unita()->first();
           if ($uo->isDipartimento()){
               return $uo->dipartimento();
           }
           if ($uo->isPlesso()){
               return $uo->dipartimenti();
           }
    }

}
