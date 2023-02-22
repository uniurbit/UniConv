<?php
/**
 * uniurb/unidem package configuration file. 
 */
return [

    /**
     * Datepicker configuration:
     */
    'date_format'        => 'd-m-Y',
    'date_format_jquery' => 'dd-mm-yyyy',
    'time_format'        => 'H:i:s',
    'time_format_jquery' => 'HH:mm:ss',

    /**
     * Quickadmin settings
     */
    'route'              => 'https://unidemdev.uniurb.it/unidem/uniconv/',  
    
    'client_url' => env('CLIENT_URL', 'https://unidemdev.uniurb.it/unidem/uniconv/uniconvclient'),   


    //utilizzato per la creazione del task di sottoscrizione
    'uff_sottoscrizione' => '005199', 
    'resp_uff_sottoscrizione' => '',
     
    'unitaSuperAdmin' => ['005680'],
    'unitaAdmin' => explode(',',env('UFF_ADMIN','005199')),

    //eliminati 005363, 005364
    //per test mio 005680
    'ufficiPerValidazione' =>  explode(',',env('UFF_VALIDAZIONE', '005146,005340,005361,005362,005622,005626,005344,005840,005841,005680')),
    'uffFiscale' => explode(',',env('UFF_FISCALE', '005259')),
     
    'administrator_email' =>  explode(',',env('ADMINISTRATOR_EMAIL', 'enrico.oliva@uniurb.it')),   

];