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

class Sottoscrizione 
{
    
    public static function exec($request){
        //DETERMINARE SE 
        //stipula digitale o analogica
        //stipula uniurb o ditta         
        $data = $request->all();
        $conv = Convenzione::findOrFail($data['convenzione_id']);    
        $aziende = $conv->aziende()->get();

        //caso cartaceo uniub
        if ($request->stipula_type == Convenzione::STIPULA_UNIURB && $request->stipula_format == Convenzione::STIPULA_ANALOGICA){
            //adapter input data
            $objdata = (object) $data['an_dg_uniurb_an_controparte'];            
            $data['attachments'] = array($objdata->attachment1);
            if (isset($objdata->attachment2->filevalue))
                array_push($data['attachments'], array($objdata->attachment2));          

            //inviare il file allegato LTU_FIRM_UNIURB protocollare titulus            
            foreach ($data['attachments'] as $key => $attachment) {
                if ($attachment['attachmenttype_codice'] == 'LTU_FIRM_UNIURB'){
                    //aggiunge le info sul nrecord e num_prot                     
                    //allego un solo documento
                    $data['attachments'][$key] = TitulusHelper::saveDocumentInTitulus(TitulusHelper::OGGETTO_SOTTOSCRIZIONE,array($attachment),  Documento::PARTENZA, $aziende, $conv, $conv->nrecord)[0];                    
                }
            }
        }   

        //caso digitale uniurb
        if ($request->stipula_type == Convenzione::STIPULA_UNIURB && $request->stipula_format == Convenzione::STIPULA_DIGITALE){
            //adapter input data
            $objdata = (object) $data['an_dg_uniurb_an_controparte'];            
            $data['attachments'] = array($objdata->attachment1);           
            array_push($data['attachments'], $objdata->attachment2);   
            
            // AGGIUNGERE GLI ALLEGATI 
            if ($objdata->optional_attachments && count($objdata->optional_attachments) > 0){
                $data['attachments'] = array_merge($data['attachments'], $objdata->optional_attachments);  
            }
           
            //protocollo lettera di trasmissione + convenzione firmata da uniurb (vedere link)
            $data['attachments'] = TitulusHelper::saveDocumentInTitulus(TitulusHelper::OGGETTO_SOTTOSCRIZIONE,$data['attachments'],  Documento::PARTENZA, $aziende, $conv, $conv->nrecord, true);                    

            //inviare la PEC o email al destinatario attraverso titulus
            TitulusHelper::sendDocumentByEmail($data['attachments'][0]['nrecord']);
        }   

         //caso cartacea controparte(ditta)
         if ($request->stipula_type == Convenzione::STIPULA_CONTROPARTE && $request->stipula_format == Convenzione::STIPULA_ANALOGICA){
            $objdata = (object) $data['an_dg_uniurb_an_controparte'];      
            
            if ($objdata->attachment1['attachmenttype_codice'] == 'NESSUN_DOC'){
                $data['data_sottoscrizione'] =  $objdata->attachment1['data_sottoscrizione'];
                if ($objdata->attachment2['filename'])
                    $data['attachments'] = array($objdata->attachment2);                  
            }else{
                $data['attachments'] = array($objdata->attachment1);                       
                if ($objdata->attachment2['filename'])
                    array_push($data['attachments'], $objdata->attachment2);          
                //protocollo lettera in arrivo + convenzione firmata da controparte
                $data['attachments'] = TitulusHelper::saveDocumentInTitulus(TitulusHelper::OGGETTO_SOTTOSCRIZIONE,$data['attachments'],  Documento::ARRIVO, $aziende, $conv, $conv->nrecord, true);                    
            }                        
         }

        //caso cartacea controparte(ditta)
        if ($request->stipula_type == Convenzione::STIPULA_CONTROPARTE && $request->stipula_format == Convenzione::STIPULA_DIGITALE){
            $objdata = (object) $data['digitale_controparte']; 
            //ricezione pec
            if ($objdata->attachment1['attachmenttype_codice'] == 'LTE_FIRM_CONTR_PROT'){
                
                $attch['attachmenttype_codice'] = 'LTE_FIRM_CONTR_PROT';

                $doc = $objdata->attachment1['doc'];

                $attch['nrecord'] = array_key_exists('nrecord', $doc) ? $doc['nrecord'] : null;
                $attch['num_prot'] = array_key_exists('num_prot', $doc) ? $doc['num_prot']: null;
                $attch['emission_date'] = array_key_exists('data_prot',$doc) ? $doc['data_prot'] : null; 

                $data['attachments'] = array($attch);
                if ($objdata->attachment2['filename'])
                    array_push($data['attachments'], $objdata->attachment2);          

            }

            //ricezione email 
            if ($objdata->attachment1['attachmenttype_codice'] == 'LTE_FIRM_CONTR'){
                $data['attachments'] = array($objdata->attachment1);                       
                if ($objdata->attachment2['filename'])
                    array_push($data['attachments'], $objdata->attachment2);          
                //protocollo lettera in arrivo + convenzione firmata da controparte
                $data['attachments'] = TitulusHelper::saveDocumentInTitulus(TitulusHelper::OGGETTO_SOTTOSCRIZIONE,$data['attachments'],  Documento::ARRIVO, $aziende, $conv, $conv->nrecord, true);                    
            }   

            //nessun documento
            if ($objdata->attachment1['attachmenttype_codice'] == 'NESSUN_DOC'){
                $data['data_sottoscrizione'] =  $objdata->attachment1['data_sottoscrizione'];
                if ($objdata->attachment2['filename'])
                    $data['attachments'] = array($objdata->attachment2);                  
            }
        }
            
        return $data;
    }

}