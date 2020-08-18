<?php
/**
 * uniurb/unidem package configuration file. 
 */
return [

    /**
     * Datepicker configuration:
     */
    'date_format'        => 'd-m-Y',
    'time_format'        => 'H:i:s',

    /**
     * Quickadmin settings
     */
    'route'              => '',  
    
    'client_url' => env('CLIENT_URL', ''),   


    //utilizzato per la creazione del task di sottoscrizione
    'uff_sottoscrizione' => 'XXXXX', 
    'resp_uff_sottoscrizione' => '',
     
    'unitaSuperAdmin' => ['YYYYY'],
    'unitaAdmin' => explode(',',env('UFF_ADMIN','ZZZZZ')),

    'ufficiPerValidazione' =>  explode(',',env('UFF_VALIDAZIONE', 'HHHHH')),
    'uffFiscale' => explode(',',env('UFF_FISCALE', 'CCCCC')),
     
    'administrator_email' =>  explode(',',env('ADMINISTRATOR_EMAIL', 'enrico.oliva@uniurb.it')),   

];