<?php
//-------- For enums in Seeders --------
return [
    'company_status' => [
        'active'   => 'Active',
        'inactive' => 'Inactive',
    ],    
    
    'verification' => [
        'yes' => 'yes',
        'no'  => 'no',
    ],
        
    'stipula_type' =>[
        'uniurb' => 'Uniurb',
        'controparte' => 'Controparte',
    ],

    'stipula_format' =>[
        'cartaceo' => 'Cartaceo',
        'digitale' => 'Digitale',
    ],

        
    'tipo_emissione' => [
        'NOTA_DEBITO' => 'Emissione nota di debito',
        'FATTURA_ELETTRONICA' => 'Fattura elettronica',
        'RICHIESTA_PAGAMENTO' => 'Richiesta pagamento',
    ],

    'convenzione_from' => [
        'dip' => 'Dipartimentale',
        'amm' => 'Amministrativa',
    ],

    'rinnovo_type' => [
        'non_rinnovabile' => 'Non rinnovabile',
        'esplicito' => 'Rinnovo esplicito',
        'tacito' => 'Rinnovo tacito',
    ],

    'utente_non_autorizzato' => 'Utente non autorizzato',
    'conv_stato_non_valido' => 'Stato convenzione non consentito per questa operazione',
    'scad_stato_non_valido' => 'Stato scadenza non consentito per questa operazione',
];