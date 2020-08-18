<?php 

require_once __DIR__ .'/../src/wsTitulus/SayHello.php'; 
require_once __DIR__ .'/../src/wsTitulus/DocumentTitulus.php'; 

use wsTitulus\SayHello;
use wsTitulus\DocumentTitulus;

echo SayHello::world();

$documento = new DocumentTitulus;

 /* SimpleXML */
 $xml_header = '<extra></extra>';
 $xml = new \SimpleXMLElement($xml_header);

 $dati_conservazione = DocumentTitulus::addDati_conservazione($xml,[
     'tipologia' => 'registro_docente',
     'versione' => 1
 ]);

 $registro =DocumentTitulus::addRegistro($dati_conservazione,[
     'tipo' => 'Registro docente',
     'anno_accademico' => '2018/2019',
     'periodo_didattico' => 'Primo Semestre',
     'vigenza_contrattuale_dal' => 20190312,
     'vigenza_contrattuale_al' => 20190312,
 ]);

 DocumentTitulus::addIstituzione($registro, [
     'cod' => '70019',
     'denominazione' => 'Università degli Studi di Urbino Carlo Bo',
     'dipartimento' =>'Dipartimento DISB',
     'dipartimento_cod' => 'D220000'
 ]);        

 $informazioni_di_corredo = $registro->addchild('informazioni_di_corredo');

 DocumentTitulus::addEvento($informazioni_di_corredo,[
     'denominazione' => 'Verifica ufficio personale Docente',
     'data' =>'12/03/2019',
     'agente_tipo' =>'persona',
     'agente_denominazione' =>'Mirco Rossi',
     'agente_matricola' =>'1234567'
 ]);

 DocumentTitulus::addEvento($informazioni_di_corredo,[
     'denominazione' => 'Sottoscrizione con firma elettronica',
     'data' =>'12/03/2019',
     'agente_tipo' =>'persona',
     'agente_denominazione' =>'NOME COGNOME DOCENTE',
     'agente_matricola' =>'?????'
 ]);        

 DocumentTitulus::addPersona($xml,[
     'codice_fiscale' => 'DLLSFN67A21G224J',
     'cognome' =>'ROSSINI',
     'nome' => 'MICHELE',
     'data_nascita' =>'21/01/1967',
     'luogo_nascita' => 'Padova',
     'sesso' => 'M',
     'nazione_nascita' => 'ITALIA',
     'cod_ANS' => 'IT',
     'email' => 'mail@uniurb.it'
 ]);

 DocumentTitulus::addSistemaMittente($xml, [
     'id_documento'=> 'RD-20310',
     'pers_id'=> '016341',
     'codice_dipartimento_registro'=> 'D220000',
     'applicativo'=> 'Unicontract',
     'versione'=> '1.0',
 ]);

 var_dump(str_replace('<?xml version="1.0"?>', '', $xml->asXML()));