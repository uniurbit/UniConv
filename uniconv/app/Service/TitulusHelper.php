<?php

namespace App\Service;

use Carbon\Carbon;
use App\Convenzione;
use App\UserTask;
use App\Permission;
use App\Repositories\ConvenzioneRepository;
use DateTime;
use Auth;
use App;
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
use App\MappingUfficio;
use App\Http\Controllers\Api\V1\StrutturaInternaController;
use App\Http\Controllers\Api\V1\PersonaInternaController;
use PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TitulusHelper 
{
    
    const OGGETTO_SOTTOSCRIZIONE = 'UniConv sottoscrizione';

    /**
     * @param $attachments elenco allegati
     * @param $tipodoc partenza arrivo
     * @param $conv convenzione
     * @param $fascicolo_nrecord nrecord del fascicolo in cui mettere il documento protocollato
     * @param $send_email appaiono i tasti di invio email
     * @param $addrepertorio il documento protocolollato viene aggiunto al repertorio 'Coac','Convenzione e Accordi'
     */
    public static function saveDocumentInTitulus($oggetto,  $attachments, $tipodoc, $aziende, $conv, $fascicolo_nrecord, $send_mail=false, $addrepertorio=false){

        $titolario = $conv->titolario_classificazione;        

        $dip = $conv->dipartimento->first();
        $unitaorganizzativa_uo = null;
        if ($dip){
            $unitaorganizzativa = $dip->unitaOrganizzativa()->first();
            $unitaorganizzativa_uo = $unitaorganizzativa->uo;
        }

        //legge la il tipo di allegato per inserire la descrizione corretta
        $attch_type = AttachmentType::where('codice', $attachments[0]['attachmenttype_codice'])->first();        

        $sc = new SoapControllerTitulus(new SoapWrapper);
        $doc = new Documento;
        $doc->rootElementAttributes->tipo = $tipodoc;

        if ($addrepertorio)
            $doc->addRepertorio('Coac','Convenzione e Accordi');    
            
        if ($oggetto){
            $doc->oggetto = $oggetto.' '.$attch_type->descrizione; //almeno 30 caratteri          
        }else {
            $doc->oggetto = 'UniConv sottoscrizione '.$attch_type->descrizione; //almeno 30 caratteri          
        }
        
        $doc->addClassifCod($titolario);
        
        if (count($attachments)==1)
            $doc->allegato = '0 - Nessun allegato';                              
        else {
            for ($i=1; $i < count($attachments); $i++) { 
                $temp_attch_type = AttachmentType::where('codice', $attachments[$i]['attachmenttype_codice'])->first();        
                $doc->allegato = $i.' - '.$temp_attch_type->descrizione;    
            }
        }
            
        if ($unitaorganizzativa_uo){
            TitulusHelper::addRPA_Titulus($doc,$unitaorganizzativa_uo);        
            TitulusHelper::addCC_Titulus($doc);
        }else{            
            //unità organizzativa a cui è associata la convenzione 
            //caso convenzioni amministrative
            TitulusHelper::addRPA_Titulus($doc, $conv->unitaorganizzativa_uo);        
            //in cc utente corrente ... 
            TitulusHelper::addCC_Titulus($doc);
        }

        if (App::environment(['local','preprod'])) {         
            $doc->addCC("Attività sistemistiche e software Gestionali e Documentali", "Oliva Enrico");
        }
        
        if ($tipodoc ==  Documento::ARRIVO){
            $doc->addAzienda($aziende[0]);
        } else {
            foreach ($aziende as $key => $value) {
                $doc->addAzienda($value);
            }
        }
        
        
        //costruzione degli allegati
        $attachBeans = array();
        foreach ($attachments as $attachment) {
            if ($attachment['filevalue']!=null){
                $attachment1 = new AttachmentBean();
                $attachment1->setFileName($attachment['filename']);
                $temp_attch_type = AttachmentType::where('codice', $attachment['attachmenttype_codice'])->first();    
                $attachment1->setDescription($temp_attch_type->descrizione.' ('.$attachment['filename'].')');
                //$attachment1->setMimeType("application/pdf");
                $attachment1->setContent(base64_decode($attachment['filevalue']));      
                array_push($attachBeans,  $attachment1);
            }
        }        
           
        Log::info('Chiamata saveDocument [' . $doc->toXml() . ']');   
        $sd = new SaveDocument($doc->toXml(), $attachBeans, new SaveParams(false,$send_mail));  
        $response = $sc->saveDocument($sd);    
        Log::info('Risposta saveDocument [' . $response . ']');           
        $obj = simplexml_load_string($response);

        $doc = $obj->Document->doc;

        foreach ($attachments as $key => $value) {
            $attachments[$key]['nrecord'] = (string) $doc['nrecord'];
            $attachments[$key]['num_prot'] = (string) $doc['num_prot'];
            $attachments[$key]['emission_date'] =  Carbon::createFromFormat('Ymd', ((string) $doc['data_prot']))->format(config('unidem.date_format')); //aaaammgg
            Log::info('num_prot [' . $attachments[$key]['num_prot'] . ']');   
            if ($addrepertorio){
                $attachments[$key]['num_rep'] = (string) $doc->repertorio['numero']; 
            }
        }

        //aggiungi al fascicolo ...         
        $xmlInFolder = new Fascicolo();
        $xmlInFolder->rootElementAttributes->nrecord = $fascicolo_nrecord;
        $xmlInFolder->addDoc((string) $doc['nrecord']);

        Log::info('Chiamata addInFolder [' . $xmlInFolder->toXml() . ']');   
        $response = $sc->addInFolder($xmlInFolder->toXml());   

        return $attachments;
    }

    /*
    * funzione prende in ingresso un documento o un fascicolo (struttura xml)
    * e aggiunge un riferimento interno RPA
    * il riferimento è il responsabile ufficio della persona che è Loggata nel sistema o passata come parametro
    *
    * usare solo per utenti non responsabili di una UO
    *
    * @param $element oggetto xml
    * @param $userid id utente
    */
    public static function addRPA($element, $userid = null){        
        $pers = null;
        //lettura responsabile da ugov
        if ($userid){
            $pers =  User::find($userid)->findPersonaleRespons();   
        }else{
            $pers =  Auth::user()->findPersonaleRespons();   
        }
        $mapping = $pers->mappingufficio()->first();

        
        $element->addRPA($mapping->descrizione_uff, $pers->nomepersona);    
    }    

   /*
    * funzione prende in ingresso un documento o un fascicolo (struttura xml) e l'unità organizzativa a cui è assegnata la convenzione (dipartimento)
    * e aggiunge un riferimento interno RPA    
    * il riferimento è il responsabile ufficio della persona che è Loggata nel sistema o passata come parametro
    */ 
    public static function addRPA_Titulus($element, $unitaorganizzativa_uo){       
        // da titulus leggo il codice del responsabile del dipartimento di riferimento         
        // dal codice leggo il nome del resp sempre su titulus
        $mapping = MappingUfficio::where('unitaorganizzativa_uo', $unitaorganizzativa_uo)->first();

        $ctrStr = new StrutturaInternaController();        
        $strint = $ctrStr->getminimal($mapping->strutturainterna_cod_uff);       

        $ctrPers = new PersonaInternaController();
        $persint = $ctrPers->getminimal($strint->cod_responsabile); 

        $element->addRPA($mapping->descrizione_uff, $persint->nomepersona);         
         
    }

    /*
    * funzione prende in ingresso un documento o un fascicolo (struttura xml) 
    * e aggiunge l'utente corrente come operatore incaricato
    */
    public static function addCC_Titulus($element){  
        $pers =  Auth::user()->personaleRelation()->first(); 
        $ctrPers = new PersonaInternaController();
        $persint = $ctrPers->getminimalByName($pers->utenteNomepersona); 

        $ctrStr = new StrutturaInternaController();        
        $strint = $ctrStr->getminimal($persint->cod_uff);

        $element->addCC($strint->nome, $pers->utenteNomepersona); 
    }

    /** 
     *  Invia al destinatario del documento protocollato con nrecord l'email 
     */
    public static function sendDocumentByEmail($nrecord){
        $sc = new SoapControllerTitulus(new SoapWrapper);
        //1) startworkflow
        $response = $sc->startWorkflow($nrecord,'invioPEC1');
         
        //2) getWorkflowId
        $response = $sc->getWorkflowId($nrecord);        
        $obj = simplexml_load_string($response);
        $workflowid = $obj->Workflow['id'];        
        
        //3) getWorkflowAction        
        $response = $sc->getWorkflowAction($nrecord,$workflowid);         
        $obj = simplexml_load_string($response);
        $actionId = $obj->WorkflowAction['id'];          

        //4) continueWorkflow
        $response = $sc->continueWorkflow($nrecord, $workflowid, $actionId);                  
    }


    public static function downloadAttachment($num_prot, $filename=null){
        $sc = new SoapControllerTitulus(new SoapWrapper);                
        $response = $sc->loadDocument($num_prot,false);        
        $obj = simplexml_load_string($response);
        $document = $obj->Document;        
        $doc = $document->doc;   
        Log::info('Chiamata loadDocument [' . $response . ']'); 
        
        //cerca se il "Nome dell'allegato" è all'interno del titolo dell'allegato
        if ($filename){
            foreach ($doc->files->children('xw',true) as $file) {                      
                $title = (string) $file->attributes()->title;
                if (Str::contains($title, $filename)) {
                    $fileId = (string) $file->attributes()->name;                            
                    $attachmentBean =  $sc->getAttachment($fileId);
                    $attachmentBean->title =  (string) $file->attributes()->title;  
                    return $attachmentBean;
                }                
            }                    
            if ($doc->immagini && $doc->immagini[0]){
                foreach ($doc->immagini->children('xw',true) as $file) {                      
                    $title = (string) $file->attributes()->title;
                    if (Str::contains($title, $filename)) {
                        $fileId = (string) $file->attributes()->name;                            
                        $attachmentBean =  $sc->getAttachment($fileId);
                        $attachmentBean->title =  (string) $file->attributes()->title;  
                        return $attachmentBean;
                    }                
                }           
            }
        }
        
        foreach ($doc->files->children('xw',true) as $file) {
            //restuisce il primo
            $fileId = (string) $file->attributes()->name;            
            $attachmentBean =  $sc->getAttachment($fileId);
            $attachmentBean->title =  (string) $file->attributes()->title;  
            return $attachmentBean;
        }
        return null;
    }

    public static function createFatturaPA($xml){
        $xsl = Storage::get('fatturapa_v1.2.xsl');        

        $xslt = new \XSLTProcessor();
        $xslt->importStylesheet(new  \SimpleXMLElement($xsl));
        $result = $xslt->transformToXml(new \SimpleXMLElement($xml));

        $pdf = PDF::loadHTML($result);

        return $pdf;
    }

}