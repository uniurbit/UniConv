<?php

namespace App\Models\Titulus;

use Spatie\ArrayToXml\ArrayToXml;


// /doc/oggetto	oggetto
// /doc/classif	classificazione
// /doc/voce_indice	voce di indice
// /doc/allegato	campo “Allegato” (ripetibile)
// /doc/repertorio	contiene la descrizione del repertorio (solo nei repertori)
// /doc/repertorio/@cod	contiene il codice del repertorio (solo nei repertori)
// /doc/rif_esterni/rif	mittente o destinatario (ripetibile)
// /doc/rif_interni/rif	riferimenti interni (RPA, CC…)
// /doc/files	contiene i file allegati (non le immagini)
// /doc/immagini	contiene le immagini allegate
// /doc/minuta	contiene le informazioni sulla minuta (solo documenti tra uffici)
// /doc/storia	contiene la storia degli interventi sul documento

//In questo paragrafo per “documento” si intende un documento in arrivo, in partenza, tra uffici o non protocollato.
class Documento extends ModelBase
{
     
    // /doc/@tipo	tipo (“arrivo” o “partenza” o “interno” o “varie”)
     const ARRIVO = 'arrivo';
     const PARTENZA = 'partenza';
     const INTERNO = 'interno';
     const VARIE = 'varie';


    public $fillable = ['oggetto','classif','voce_indice','allegato','repertorio','rif_esterni','rif_interni','files','immagini','minuta','storia'];

    public function __construct()
    {
        $this->rootElementName = 'doc';
        $this->rootElementAttributes = new DocumentoAttributi;
    }

    public function addRifInt($nome_uff, $nome_persona, $diritto, $cod_uff=NULL){
        $rif_interno = new Rif('rif_interno');   
        $rif_interno->rootElementAttributes->diritto =  $diritto;       
        $rif_interno->rootElementAttributes->nome_uff= $nome_uff;                             
        $rif_interno->rootElementAttributes->nome_persona= $nome_persona;                        
        if ($cod_uff!=null)
            $rif_interno->rootElementAttributes->cod_uff= $cod_uff;       

        if (isset($this->attributes['rif_interni']) && is_array($this->attributes['rif_interni'])){
            $this->attributes['rif_interni'] = array_merge($this->attributes['rif_interni'],array($rif_interno));
        }else{
            $this->attributes['rif_interni'] = array($rif_interno);
        }        
    }


//     <rif_interni>
//     <rif nome_persona="Mancini Mara" nome_uff="Dipartimento di Scienze Pure e Applicate - DISPeA" cod_persona="PI000083" cod_uff="SI000084" diritto="RPA" cod_fasc="2019-UNURCLE-03/13.00058"/>
//     <rif nome_persona="Buchi Anna Maria" nome_uff="Dipartimento di Scienze Biomolecolari" cod_persona="PI000196" cod_uff="SI000060" diritto="OP"/>
//   </rif_interni>

    public function addRPA($nome_uff, $nome_persona=NULL, $cod_uff=NULL){
        $this->addRifInt($nome_uff, $nome_persona, 'RPA');              
    }

    public function addCDS($nome_uff, $nome_persona, $cod_uff=NULL){
        $this->addRifInt($nome_uff, $nome_persona, 'CDS');
    }

    public function addOP($nome_uff, $nome_persona, $cod_uff=NULL){
        $this->addRifInt($nome_uff, $nome_persona, 'OP');
    }

    public function addCC($nome_uff, $nome_persona, $cod_uff=NULL){
        $this->addRifInt($nome_uff, $nome_persona, 'CC');
    }


    public function addAzienda($azienda){
    //     <rif_esterni>
    //     <rif_esterno data_prot="20040101" n_prot="1243">
    //       <nome cod="SE000095" xml:space="preserve">Ditta srl</nome>
    //       <referente cod="PE000928" nominativo="Mario Rossi"/>
    //       <indirizzo email="mrossi@ditta.it"
    //                  fax="051 451242"
    //                  tel="051 452844"
    //                  xml:space="preserve">
    //          Via Manzoni 2 - 40033 Casalecchio di Reno - BO - Italia
    //       </indirizzo>
    //     </rif_esterno>
    //   </rif_esterni>

    //azienda
    // 'denominazione' => 'azienda di test',
    // 'contatto_email' => 'email@test.it',
    // 'pec_email' => 'email@pec.it',
    // 'cod_fisc' => '1234567891011',
    // 'indirizzo1' => 'test', 

        //il rif_esterno è l'azienda a cui si invia è obbligatorio! 
        $rif_esterno = new Rif('rif_esterno');

        $nome = new Element('nome');
        $nome->_value = $azienda->denominazione;
        $rif_esterno->nome = $nome;       
    
        //if ($azienda->indirizzo1){
        $indirizzo = new Element('indirizzo');
        $indirizzo->rootElementAttributes->email = $azienda->contatto_email;
        $indirizzo->rootElementAttributes->email_certificata = $azienda->pec_email;
        $indirizzo->_value = $azienda->indirizzoToString();
        $rif_esterno->indirizzo = $indirizzo;        
        //}
        
        if (isset($this->attributes['rif_esterni']) && is_array($this->attributes['rif_esterni'])){
            $this->attributes['rif_esterni'] = array_merge($this->attributes['rif_esterni'],array($rif_esterno));
        }else{
            $this->attributes['rif_esterni'] = array($rif_esterno);
        }
    }

    public function addClassifCod($titolario){
        $classif = new Element('classif');
        $classif->rootElementAttributes->cod = $titolario;        
        $this->attributes['classif'] = $classif;
    }

    public function addRepertorio($codice, $nomerepertorio){
        $rep = new Element('repertorio');
        $rep->rootElementAttributes->cod = $codice;        
        $rep->_value =  $nomerepertorio;
        $this->attributes['repertorio'] = $rep;
    }


    public function addAllegato($descr){
        if (!isset($this->attributes['allegato'])) 
            $this->attributes['allegato'] = [];

        if (!is_array($this->attributes['allegato']))
            $this->attributes['allegato'] = [];

        array_push($this->attributes['allegato'],['_value' => $descr]);
    }

}

