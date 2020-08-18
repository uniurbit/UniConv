<?php

namespace App\Models;

use Jenssegers\Model\Model;

class StrutturaEsterna extends Model {
   
    // <struttura_esterna codice_fiscale="02191651203" partita_iva="02191651203" tipologia="Cineca Company">
    // <nome>Kion s.p.a.</nome>
    // <indirizzo nazione="Italia" prov="Bologna" comune="Casalecchio di Reno" cap="40033">via Magnanelli, 2</indirizzo>
    // <telefono num="+39 051 6111411" tipo="tel"/>
    // <telefono num="+39 051 570423" tipo="fax"/>
    // <email addr="email1@kion.it"/>
    // <email addr="email2@kion.it"/>
    // <email_certificata addr="email_cert@kion.it"/>
    // <sito_web url="www.kion.it"/>
    // <sito_web url="www.cineca.it"/>
    // <note>Queste sono note</note>
    // </struttura_esterna>
    public $elementName = 'struttura_esterna';

    protected $fillable = [
        'physdoc',
        'nrecord',         
        'cod_uff',     
        'codice_fiscale',
        'partita_iva',
        'tipologia',
        'nome',
        'indirizzo',
        'email_certificata_addr',
        'telefoni',
        'emails',
        'siti_web',       
        'email_certificata',
    ];
    
    protected $hidden = ['nrecord','physdoc'];

    protected $queryparam = [
        'struest_coduff',	
        'struest_codsap',				
        'struest_codfisc',				
        'struest_piva',					
        'struest_nome',				
        'struest_tipologia',				
        'struest_indirizzocomune',					
        'struest_indirizzoprov',				
        'struest_competenze',				
        'struest_emailaddr',		
        'struest_codresponsabile',			     
        'struestcreazione',
        'struest_categoria',
        'struest_operatore',
        'struest_uffoperatore',
        'struest_telnum'
    ];

    public $querykey = '([UD,/xw/@UdType/]=struttura_esterna)';


    public function setIndirizzoAttribute($value)    
    {
        if ( is_object($value) || is_array($value) ){
            return $this->attributes['indirizzo'] = $value;
        }
        return  $this->attributes['indirizzo'] = array('value' => $value);
    }
}