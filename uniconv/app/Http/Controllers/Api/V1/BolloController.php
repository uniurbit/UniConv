<?php

namespace App\Http\Controllers\Api\V1;

use App\Convenzione;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Exports\BolliExport;


class BolloController extends Controller
{
   
    public function queryparameter(Request $request){
        //NB lettura parametri con json() per test exportCsv
        $parameters = $request->json()->all();

        $parameters['includes'] = 'aziende,tipopagamento,bolli';
        $parameters['columns'] =   implode (",", ['id',
            'descrizione_titolo',
            'user_id',
            'schematipotipo',
            'dipartimemto_cd_dip',
            'resp_scientifico',
            'ambito',
            'durata',        
            'prestazioni',
            'corrispettivo',        
            'importo',
            'tipopagamenti_codice',
            'current_place',
            'unitaorganizzativa_uo',
            'convenzione_type',
            'stipula_type',
            'titolario_classificazione',
            'oggetto_fascicolo',
            'nrecord',
            'numero',
            'num_rep',
            'data_sottoscrizione',
            'data_inizio_conv',
            'data_fine_conv',
            'data_stipula',
            'aziende.id',
            'aziende.denominazione',
            'tipopagamento.codice',
            'tipopagamento.descrizione',
            'rinnovo_type',   
            'bollo_virtuale',       
            'bolli.convenzioni_id',
            'bolli.tipobolli_codice',
            'bolli.num_bolli',
            'bolli.num_righe'
        ]);

        //se l'utente non ha il peremesso 'search all convenzioni' va filtrato 
        if (!Auth::user()->hasPermissionTo('search all convenzioni')){
            //aggiungere filtro per unitaorganizzativa_uo
            $uo = Auth::user()->unitaorganizzativa();
            if ($uo->isDipartimento()){
                //ad un afferente al dipartimento filtro per dipartimento
                $dip = $uo->dipartimento()->first();
                array_push($parameters['rules'],[
                    "operator" => "=",
                    "field" => "dipartimemto_cd_dip",                
                    "value" => $dip->cd_dip
                ]);
            }else if ($uo->isPlesso()){
                //filtro per unitaorganizzativa dell'utente di inserimento (plesso)
                array_push($parameters['rules'],[
                    "operator" => "In",
                    "field" => "dipartimemto_cd_dip",                
                    "value" => $uo->dipartimenti_cd_dip()
                ]);
            }else{
                //filtro per unitaorganizzativa dell'utente di inserimento (servizio o un plesso)
                array_push($parameters['rules'],[
                    "operator" => "=",
                    "field" => "unitaorganizzativa_uo",                
                    "value" => $uo->uo
                ]);
            }                                            
        }        

    
        return new \App\FindParameter($parameters);
    }

    public function query(Request $request){
        
        //permesso search all convenzioni 
        if (!Auth::user()->hasAnyPermission(['search all convenzioni', 'search orgunit convenzioni'])) {
            abort(403, trans('global.utente_non_autorizzato'));
        }

        $findparam = $this->queryparameter($request);
        $queryBuilder = new QueryBuilder(new Convenzione, $request, $findparam);

        return $queryBuilder->build()->paginate();        
    }

    public function export(Request $request){
        //prendi i parametri 
        $findparam = $this->queryparameter($request);          
        return (new BolliExport($request,$findparam))->download('bolli.csv', \Maatwebsite\Excel\Excel::CSV,  [
            'Content-Type' => 'text/csv',
        ]);        
    }

    public function exportxls(Request $request){
        //prendi i parametri 
        $findparam = $this->queryparameter($request);                              
        return (new BolliExport($request,$findparam))->download('bolli.xlsx');     
    }
}