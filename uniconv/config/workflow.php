<?php

 // Transitions are defined with a unique name, an origin place and a destination place
 //https://github.com/brexis/laravel-workflow

//attenzione quando si aggiunge una transizione OCCORRE aggiungere e assegnare il relativo permesso

return [
    'convenzione'   => [
        'type'          => 'workflow', //state_machine
        'audit_trail'   => [
            'enabled' => true
        ],
        'marking_store' => [
            'type' => 'single_state', //multiple_state single_state
            'arguments' => ['current_place']
        ],
        'supports'      => ['App\Convenzione'],
        'places'        => ['start', 'proposta', 'approvato','inapprovazione','da_firmare_direttore','da_firmare_controparte2','firmato', 'repertoriato'],
        'transitions'   => [                    
            'store_proposta' => [
                'from' => 'start',
                'to'   => 'proposta',
            ],                       
            'store_to_approvato' => [
                'from' => 'proposta',
                'to'   => 'approvato',                
            ], 
            'store_to_inapprovazione' => [
                'from' => 'proposta',
                'to'   => 'inapprovazione',
            ],     
            'store_validazione' => [
                'from' => 'inapprovazione',
                'to'   => 'approvato',
            ], 
            'firma_da_controparte1' => [ 
                'from' => 'approvato',
                'to'   => 'da_firmare_direttore',
            ],    
                           
            'firma_da_direttore2' => [
                'from' => 'da_firmare_direttore',
                'to'   => 'firmato',
            ],

            //backtrace annulla sottoscrizione contr
            'cancella_sottoscrizione_contr' => [
                'from'   => 'da_firmare_direttore',
                'to'   => 'approvato',
            ],      

            'firma_da_direttore1' => [
                'from' => 'approvato',
                'to'   => 'da_firmare_controparte2',
            ],
            
            'firma_da_controparte2' => [
                'from' => 'da_firmare_controparte2',
                'to'   => 'firmato',
            ],

            //backtrace annulla sottoscrizione uniurb 
            'cancella_sottoscrizione_uniurb' => [
                'from'   => 'da_firmare_controparte2',
                'to'   => 'approvato',
            ],     

            'repertorio' => [
                'from' => 'firmato',
                'to'   => 'repertoriato',
            ],
        ],
    ],


    //workflow relativo ai task per gli utenti
    'usertask'   => [
        'type'          => 'state_machine', 
        'audit_trail'   => [
            'enabled' => true
        ],
        'marking_store' => [
            'type' => 'single_state', 
            'arguments' => ['state']
        ],
        'supports'      => ['App\UserTask'],
        'places'        => ['aperto', 'completato', 'annullato'],
        'transitions'   => [                                
            'store_eseguito' => [
                'from' => 'aperto',
                'to'   => 'completato',
            ],   
            'store_conerrori' => [
                'from' => 'aperto', 
                'to'   => 'annullato',
            ],            
        ],
    ],

    //php artisan workflow:dump scadenza --class App\Scadenza
    'scadenza' => [
        'type'          => 'state_machine', 
        'audit_trail'   => [
            'enabled' => true
        ],
        'marking_store' => [
            'type' => 'single_state', 
            'arguments' => ['state']
        ],
        'supports'      => ['App\Scadenza'],
        'places'        => ['attivo', 'inemissione', 'emesso', 'inpagamento', 'pagato', 'cancellato'],
        'transitions'   => [        
            'richiestaemissione' => [
                'from' => 'attivo',
                'to'   => 'inemissione', //emesso nota di debito o fattura elettronica o cartacea (emessa richiesta di pagamento)
            ],
            'richiestapagamento' => [
                'from' => 'attivo',
                'to' => 'emesso'
            ],
            'emissione' => [
                'from' => 'inemissione',
                'to'   => 'emesso',
            ],                  
            'ordineincasso' => [
                'from' => 'emesso',
                'to'   => 'inpagamento',
            ],        
            'registrazionepagamento' => [
                'from' => 'inpagamento',
                'to'   => 'pagato',
            ],               
            'delete' => [
                'from' => ['attivo','inemissione'],
                'to' => 'cancellato'
            ]        
        ]
    ],
];
