<?php

namespace App\Service;
use Carbon\Carbon;
use App\Convenzione;
use App\UserTask;
use App\Permission;
use App\Repositories\ConvenzioneRepository;
use DateTime;
use Auth;
use App\Http\Resources\WorkflowConvenzione;
use App\Http\Resources\WorkflowConvenzioneSchemaTipoResource;
use Workflow;
use App\User;
use App\Notifications\ConvenzioneApprovata;
use App\Tasks\SottoscrizioneTask;
use App\AttachmentType;
use Storage;

use App\Http\Controllers\SoapControllerTitulus;
use Artisaninweb\SoapWrapper\SoapWrapper;
use App\Soap\Request\SaveDocument;
use App\Soap\Request\SaveParams;
use App\Soap\Request\AttachmentBean;
use App\Models\Titulus\Fascicolo;
use App\Models\Titulus\Documento;
use App\Models\Titulus\Rif;
use App\Models\Titulus\Element;
use Illuminate\Support\Facades\Log;

class RegistrazioneRepertoriazione 
{
    
    public static function exec($request){
           
        $data = $request->all();        
        $objdata = (object) $data;

        //registrare allegato già repertoriato
        if ($objdata->attachment1['attachmenttype_codice'] == 'DOC_BOLLATO_FIRMATO'){
                
            $attch['attachmenttype_codice'] = 'DOC_BOLLATO_FIRMATO';

            $doc = $objdata->attachment1['doc'];

            if (array_key_exists('nrecord',$doc))
                $attch['nrecord'] = $doc['nrecord'];
            
            if (array_key_exists('num_prot',$doc))
                $attch['num_prot'] = $doc['num_prot'];

            if (array_key_exists('numero',$doc))
                $attch['num_rep'] = $doc['numero'];

            $attch['emission_date'] =  $doc['data_prot']; 

            $data['attachments'] = array($attch);

        }        
        
        return $data;
        
    }
}
