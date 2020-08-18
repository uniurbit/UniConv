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

class Repertoriazione 
{
    
    public static function exec($request){
        //DETERMINARE SE 
        //stipula digitale o analogica
        //stipula uniurb o ditta         
        $data = $request->all();
        $conv = Convenzione::findOrFail($data['convenzione_id']);    
        $aziende = $conv->aziende()->get();

        //repertoriare 
        //che tipo di stipula?
        $allegati = null;
        //cartacea 
        //repertoria il documento in attachment1
        if ($conv->stipula_format=='cartaceo'){        
            
            $allegati = array($data['attachment1']);
            // AGGIUNGERE GLI ALLEGATI 
            if ($data['optional_attachments'] && count($data['optional_attachments']) > 0){
                $allegati = array_merge($allegati, $data['optional_attachments']);  
            }

        } else if ($conv->stipula_format=='digitale'){

            $allegati = array($data['attachment1']);
            // AGGIUNGERE GLI ALLEGATI 
            if ($data['optional_attachments'] && count($data['optional_attachments']) > 0){
                $allegati = array_merge($allegati, $data['optional_attachments']);  
            }

            //digitale
            //cerca tra gli allegati il documento firmato digitalmente
            if ($data['bollo_virtuale'] && $data['attachment1']['filename']==null){
                //leggi convenzione firmata CONV_FIRM_ENTRAMBI - firmato digitalmente da entrambi
                //potrebbe NON esserci
                $attach = $conv->attachments()->where('attachmenttype_codice','CONV_FIRM_ENTRAMBI')->first();                                

                //stipula formato digitale DEVE ESSERE PRESENTE la CONV_FIRM_ENTRAMBI 
                $attacharray = [
                    'attachmenttype_codice'=>'CONV_FIRM_ENTRAMBI',
                    'filename' =>   $attach->filename, 
                    'filevalue' =>   base64_encode(Storage::get($attach->filepath)),
                    'model_type' =>  $attach->model_type,      
                ];

                //DOC_BOLLATO_FIRMATO documento bollato virtuale e firmato digitale
                $attacharray['attachmenttype_codice']='DOC_BOLLATO_FIRMATO';
                $allegati = array($attacharray);
            }else{
                //bollo non virtuale stipula digitale 
                //bollo virtuale e inserimento allegato opzionale
                
            }
        }
        
        $data['attachments'] = TitulusHelper::saveDocumentInTitulus(
            'UniConv '.$conv->descrizione_titolo,
            $allegati,  
            Documento::PARTENZA,  
            $aziende, 
            $conv, 
            $conv->nrecord, false, true);                                          

        return $data;
        
    }
}
