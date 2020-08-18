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
use App\Tasks\SottoscrizioneTask;
use App\AttachmentType;
use App\Attachment;
use App\Notifications\ConvenzioneRepertoriata;
use Exception;

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

class CompletamentoSottoscrizione 
{

    public static function exec($request){
        //DETERMINARE SE 
        //stipula digitale o analogica
        //stipula uniurb o ditta         
        $data = $request->all();
        $conv = Convenzione::findOrFail($data['convenzione_id']);    
        $aziende = $conv->aziende()->get();
        $tipodoc =null;

        if ($conv->stipula_type == Convenzione::STIPULA_UNIURB){
           $tipodoc = Documento::ARRIVO;
        }else{
           $tipodoc = Documento::PARTENZA;
        }

        //caso stipula uniurb completamento da controparte cartaceo
        // * ricevuta lettera da protocollare 
        // * nessun documento salva la data di completamento NESSUN_DOC
        if  ($conv->stipula_type == Convenzione::STIPULA_UNIURB && $request->stipula_format == Convenzione::STIPULA_ANALOGICA){
            $objdata = (object) $data;
            if ($objdata->attachment1['attachmenttype_codice'] == 'NESSUN_DOC'){
                $data['data_sottoscrizione'] =  $objdata->attachment1['data_sottoscrizione'];
                if ($objdata->attachment2['filename'])
                    $data['attachments'] = array($objdata->attachment2);                  
            } else if ($objdata->attachment1['attachmenttype_codice'] == 'LTE_FIRM_ENTRAMBI') {
                $data['attachments'] = array($objdata->attachment1);                       
                if ($objdata->attachment2['filename'])
                    array_push($data['attachments'], $objdata->attachment2);          
                //protocollo lettera in arrivo + convenzione firmata da controparte
                $data['attachments'] = TitulusHelper::saveDocumentInTitulus(TitulusHelper::OGGETTO_SOTTOSCRIZIONE,$data['attachments'], $tipodoc, $aziende, $conv, $conv->nrecord, true);                    
            }
        }   

        //caso stipula uniurb completamento da controparte digitale
        // * ricevuta tramite pec salvare link in locale
        // * ricevuta tramite email protocollare e salvare doc
        // * nessun documento salva la data di completamento
        // RES - CRITICA - 2)	Utente [E] eliminato questo punto --> * necessaria la convenzione firmata da entrambi 
        if ($conv->stipula_type == Convenzione::STIPULA_UNIURB && $request->stipula_format == Convenzione::STIPULA_DIGITALE){            

            $objdata = (object) $data;
            //ricezione pec
            if ($objdata->attachment1['attachmenttype_codice'] == 'LTE_FIRM_ENTRAMBI_PROT'){
                
                $attch['attachmenttype_codice'] = 'LTE_FIRM_ENTRAMBI_PROT';

                $doc = $objdata->attachment1['doc'];

                if (array_key_exists('nrecord',$doc))
                    $attch['nrecord'] = $doc['nrecord'];
                
                if (array_key_exists('num_prot',$doc))
                    $attch['num_prot'] = $doc['num_prot'];

                $attch['emission_date'] =  $doc['data_prot']; 

                $data['attachments'] = array($attch);
                if ($objdata->attachment2['filename'])
                    array_push($data['attachments'], $objdata->attachment2);          

            }

            //ricezione email 
            if ($objdata->attachment1['attachmenttype_codice'] == 'LTE_FIRM_ENTRAMBI'){
                $data['attachments'] = array($objdata->attachment1);                       
                if ($objdata->attachment2['filename'])
                    array_push($data['attachments'], $objdata->attachment2);          
                //protocollo lettera in arrivo + convenzione firmata da controparte
                $data['attachments'] = TitulusHelper::saveDocumentInTitulus(TitulusHelper::OGGETTO_SOTTOSCRIZIONE,$data['attachments'], $tipodoc, $aziende, $conv, $conv->nrecord, true);                    
            }
            
            //nessun documento
            if ($objdata->attachment1['attachmenttype_codice'] == 'NESSUN_DOC'){
                $data['data_sottoscrizione'] =  $objdata->attachment1['data_sottoscrizione'];
                if ($objdata->attachment2['filename'])
                    $data['attachments'] = array($objdata->attachment2);                  
            }

        }   

        //caso stipula controparte completamento UniUrb cartacea 
        if ($conv->stipula_type == Convenzione::STIPULA_CONTROPARTE && $request->stipula_format == Convenzione::STIPULA_ANALOGICA){
            $objdata = (object) $data;      
            
            //invio o consegno alla ditta la convenzione firmata da entrambe le parti

            if ($objdata->attachment1['attachmenttype_codice'] == 'NESSUN_DOC'){
                $data['data_sottoscrizione'] =  $objdata->attachment1['data_sottoscrizione'];
                if ($objdata->attachment2['filename'])
                    $data['attachments'] = array($objdata->attachment2);                  
            }else if ($objdata->attachment1['attachmenttype_codice'] == 'LTU_FIRM_ENTRAMBI') {
                $data['attachments'] = array($objdata->attachment1);                       
                if ($objdata->attachment2['filename'])
                    array_push($data['attachments'], $objdata->attachment2);          
                //protocollo lettera in arrivo + convenzione firmata da controparte
                $data['attachments'] = TitulusHelper::saveDocumentInTitulus(TitulusHelper::OGGETTO_SOTTOSCRIZIONE,$data['attachments'],  $tipodoc, $aziende, $conv, $conv->nrecord, true);                    
            }
        }

        //caso stipula controparte completamento UniUrb digitale
        if ($conv->stipula_type == Convenzione::STIPULA_CONTROPARTE && $request->stipula_format == Convenzione::STIPULA_DIGITALE){           
            $objdata = (object) $data;
            //invio o consegno alla ditta la convenzione firmata da entrambe le parti
            //inivio tramite pec
            if ($objdata->attachment1['attachmenttype_codice'] == 'LTU_FIRM_ENTRAMBI_PROT'){
                
                $attch['attachmenttype_codice'] = 'LTU_FIRM_ENTRAMBI_PROT';
                
                $data['attachments'] = array($objdata->attachment1);           
                array_push($data['attachments'], $objdata->attachment2);       
                $data['attachments'] = TitulusHelper::saveDocumentInTitulus(TitulusHelper::OGGETTO_SOTTOSCRIZIONE,$data['attachments'], $tipodoc, $aziende, $conv, $conv->nrecord, true);                                    
                                  
                //inviare la PEC o email al destinatario attraverso titulus
                TitulusHelper::sendDocumentByEmail($data['attachments'][0]['nrecord']);
            }

            //invio tramite email NON a carico UNICONV
            if ($objdata->attachment1['attachmenttype_codice'] == 'LTU_FIRM_ENTRAMBI'){
                $data['attachments'] = array($objdata->attachment1);                       
                if ($objdata->attachment2['filename'])
                    array_push($data['attachments'], $objdata->attachment2);      
                    
                //protocollo lettera in PARTENZA + convenzione firmata da ENTRAMBI            
                $data['attachments'] = TitulusHelper::saveDocumentInTitulus(TitulusHelper::OGGETTO_SOTTOSCRIZIONE,$data['attachments'], $tipodoc, $aziende, $conv, $conv->nrecord, true);                    
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