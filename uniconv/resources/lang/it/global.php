<?php
//-------- For enums in Seeders --------
return [
    'company_status' => [
        'active'   => 'Attivo',
        'inactive' => 'Disattivo',
    ],    
    
    'verification' => [
        'yes' => 'si',
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

    'convenzione_type' => [
        "TO"=>"Titolo oneroso",
        "TG"=>"Titolo gratuito",    
    ],

    'stipula_type' => [
        'uniurb' => 'Stipula UniUrb',
        'controparte' => 'Stipula Azienda o Ente',
    ],

    "istituzionale"=>"Istituzionale",
    "commerciale"=>"Commerciale",

    "proposta"=>"Proposta", 
    "approvato"=>"Approvata", 
    "inapprovazione"=>"In approvazione",                        
    "da_firmare_direttore"=>"Stipula controparte", 
    "da_firmare_controparte2"=>"Stipula UniUrb",  
    "firmato"=>"Firmata",  
    "repertoriato"=>"Repertoriata",   
    "start"=>"Inizio",

    "dip"=>"Dipartimentale",
    "amm"=>"Amministrativa",

    "non_rinnovabile"=>"Non rinnovabile",
    "esplicito"=>"Rinnovo esplicito",
    "tacito"=>"Rinnovo tacito",

    "1"=>"DiGiur",
    "8"=>"DESP",
    "23"=>"DISB",
    "20"=>"DiSPeA",
    "21"=>"DISTUM", 
    "25"=>"DISCUI",

    "DiGiur_tooltip"=>"Dipartimento di Giurisprudenza",
    "DESP_tooltip"=>"Dipartimento di Economia, Società, Politica (DESP)",
    "DISB_tooltip"=>"Dipartimento di Scienze Biomolecolari (DISB)",
    "DiSPeA_tooltip"=>"Dipartimento di Scienze Pure e Applicate (DiSPeA)",
    "DISTUM_tooltip"=>"Dipartimento di Studi Umanistici (DISTUM)", 
    "DISCUI_tooltip"=>"Dipartimento di Scienze della Comunicazione, Studi Umanistici e Internazionali (DISCUI)",
 
    "attivo"=> "Attiva",
    "inemissione"=>"In emissione",
    "inpagamento"=>"In pagamento",
    "pagato"=>"Pagata",

    "005019" =>"DISB",
    "004424" =>"DESP",
    "004940" =>"DISCUI",
    "004419" =>"DiGiur",
    "004939" =>"DISTUM",
    "004919" =>"DiSPeA",
    "005479" =>"DISCUI",
    "005579" =>"DISCUI",
  
    "store_proposta" =>"Proposta",
    "store_to_approvato"=>"Approvato",
    "store_to_inapprovazione" =>"Da approvare",
    "store_validazione" =>"Approvato",
    "firma_da_controparte1" =>"Firmato controparte",      
    "firma_da_direttore2" =>"Firmato direttore",
    "firma_da_direttore1" =>"Firmato direttore",
    "firma_da_controparte2" =>"Firmato controparte",            
    "repertorio" =>"Repertoriato",
    "cancella_sottoscrizione_uniurb" =>"Annulla sottoscrizione",
    "cancella_sottoscrizione_contr" =>"Annulla sottoscrizione",

    "DCD"=>"Delibera Consiglio di Dipartimento",
    "DDD"=>"Decreto del direttore di dipartimento"
];