<?php

namespace App\Models;

use Jenssegers\Model\Model;

class StrutturaInterna extends Model {
   
    // <struttura_interna physdoc="630321" nrecord="000630321-UNURTST-3bab0ffb-69b6-4542-a035-7eae2fd15bd1" cod_uff="SI000104" cod_amm="UNUR" cod_aoo="TST" cod_responsabile="PI000201" cod_padre="SI000103">
    // <nome xml:space="preserve">Ufficio per eseguire dei test</nome>
    // </struttura_interna>
    // <struttura_interna physdoc="630310" nrecord="000428742-UNURCLE-f9fcf8d6-b72b-4d9c-8f14-f0b992f67d41" cod_uff="SI000093" cod_amm="UNUR" cod_aoo="CLE" cod_responsabile="002825" cod_padre="SI000092">
    // <nome xml:space="preserve">Ufficio Contratti e Convenzioni</nome>
    // </struttura_interna>
    public $elementName = 'struttura_interna';
    
    protected $fillable = [
        'physdoc',
        'nrecord',              
        'cod_padre',
        'cod_amm',
        'cod_aoo',
        'cod_uff',
        'cod_responsabile',
        'nome',
    ];
    
    protected $hidden = ['nrecord','physdoc'];

    protected $queryparam = [
        'struint_coduff',	
        'struint_nome',				
        'struint_codresponsabile',				
        'struint_tipologia',					
        'struint_indirizzocomune',				
        'struint_indirizzoprov',				
        'struint_competenze',					
        'struint_codammaoo',				
        'struint_operatore',				
        'struint_uffoperatore',			      
    ];

    public $querykey = '([UD,/xw/@UdType/]=struttura_interna)';
}