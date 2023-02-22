<?php
namespace Tests\Unit;
use Storage;
use App\AziendaLoc;
use App\Convenzione;
use Auth;

class ConvenzioneData 
{
    public static function getArrayConvenzione(){
        return [
            'descrizione_titolo' => 'convenzione di esempio',
            'resp_scientifico' => 'docente uno',
            'schematipotipo' => 'schematipo',
            'convenzione_from' => 'dip',
            'tipopagamenti_codice' => 'SA',    
            'durata' => '12',
            "dipartimemto_cd_dip" => 21,
            'prestazioni' => 'referente uni3',            
            'corrispettivo' => 12345.23,                             
            'importo' => 1250.00,          
            'convenzione_type' => 'TO',                           
            'ambito' => 'istituzionale',            
            'current_place' => 'start',
            'titolario_classificazione' => '03/13',
            'oggetto_fascicolo'=> 'fascicolo oggetto',
            'rinnovo_type' => 'non_rinnovabile'
        ];               
    }

    public static function getNONSchemaTipo($user){
        $data = ConvenzioneData::getArrayConvenzione();
        $data['schematipotipo'] = 'daapprovare';
        $data['unitaorganizzativa_uo'] = '005680';
        //unità organizzativa affidataria della validazione
        $data['unitaorganizzativa_affidatario'] = '005680';
        //$data['owner_user_id '] = $user->id;
        $data['subject'] = 'Validazione';
        $data['respons_v_ie_ru_personale_id_ab'] = 5266;
        $data['assignments'] = [             
            ['v_ie_ru_personale_id_ab'=> 39842 ,],
        ];
        //$data[''] = 'pierangela.pierini@uniurb.it';    
        $data['description'] = 'testCreateNONSchemaTipo';        
        $data['user'] =  $user;     
        $data['attachments'] = [
            [ 
                'filename' => 'nomefile.pdf', 
                'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
                'model_type' => 'Convenzione', 
                'attachmenttype_codice' => 'DCD'
            ]
        ];
        return $data;
    }

    public static function getConvenzioneAmministrativa($user){
        $data = ConvenzioneData::getArrayConvenzione();
        $data['user'] =  $user;
        $data['schematipotipo'] = 'daapprovare';
        $data['convenzione_from'] = 'amm';
        $data['description'] = 'convenzione amministrativa';
        $data['unitaorganizzativa_uo'] = '005680';
        $data['dipartimemto_cd_dip'] = null;
        return $data;
    }

    public static function getEmpty_AttachmentForValidazione($id){
        $data['convenzione_id'] = $id;
        $data['attachments'] = [
            [ 
                'filename' => '', 
                'filevalue' =>  '', 
                'model_type' => 'Convenzione', 
                'attachmenttype_codice' => 'DA',
                "docnumber"=> "229/2018/DiSPeA",
                "emission_date"=> "13-12-2018"
            ]
        ];
        return $data;
    }



    public static function getAttachmentForValidazione($id){
        $data['convenzione_id'] = $id;
        $data['attachments'] = [
            [ 
                'filename' => 'nomefile.pdf', 
                'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
                'model_type' => 'Convenzione', 
                'attachmenttype_codice' => 'DA'
            ]
        ];
        return $data;
    }


    public static function getAttachmentForSottoscrizione_cartaceo_uniurb($id){
        $data['convenzione_id'] = $id;
        $data['stipula_format'] = 'cartaceo';
        $data['stipula_type'] = 'uniurb';
        $data['an_dg_uniurb_an_controparte'] = [
            'attachment1' =>[ 
                        'filename' => 'nomefile.pdf', 
                        'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
                        'model_type' => 'Convenzione', 
                        'attachmenttype_codice' => 'LTU_FIRM_UNIURB'
                    ]
            ];

        // $data['attachments'] = [
        //     [ 
        //         'filename' => 'nomefile.pdf', 
        //         'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
        //         'model_type' => 'Convenzione', 
        //         'attachmenttype_codice' => 'LTU_FIRM_UNIURB'
        //     ]        
        return $data;
    }

    public static function getAttachmentForSottoscrizione_digitale_uniurb($id){
        $data['convenzione_id'] = $id;
        $data['stipula_format'] = 'digitale';
        $data['stipula_type'] = 'uniurb';
        $data['an_dg_uniurb_an_controparte'] = [
            'attachment1' =>[ 
                        'filename' => 'nomefile1.pdf', 
                        'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
                        'model_type' => 'Convenzione', 
                        'attachmenttype_codice' => 'LTU_FIRM_UNIURB'
            ],
            'attachment2' =>[ 
                'filename' => 'nomefile2.pdf', 
                'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
                'model_type' => 'Convenzione', 
                'attachmenttype_codice' => 'CONV_FIRM_UNIURB'
            ],
            'optional_attachments' =>[  
                [ 
                    'filename' => 'nomefile2.pdf', 
                    'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
                    'model_type' => 'Convenzione', 
                    'attachmenttype_codice' => 'ALLEGATO'
                ]
            ]
        ];

        return $data;
    }

    public static function getAttachmentForSottoscrizione_cartacea_controparte($id){
        $data['convenzione_id'] = $id;
        $data['stipula_format'] = 'cartaceo';
        $data['stipula_type'] = 'controparte';
        $data['an_dg_uniurb_an_controparte'] = [
            'attachment1' =>[ 
                        'data_sottoscrizione' => '10-04-2019', 
                        'attachmenttype_codice' => 'NESSUN_DOC'
            ],
            'attachment2' =>[ 
                'filename' => 'nomefile2.pdf', 
                'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
                'model_type' => 'Convenzione', 
                'attachmenttype_codice' => 'CONV_FIRM_CONTR'
            ]
        ];

        return $data;
    }

    public static function getAttachmentForSottoscrizione_cartacea_controparte_protocollo($id){
        $data['convenzione_id'] = $id;
        $data['stipula_format'] = 'cartaceo';
        $data['stipula_type'] = 'controparte';
        $data['an_dg_uniurb_an_controparte'] = [
            'attachment1' =>[ 
                'filename' => 'nomefile2.pdf', 
                'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
                'model_type' => 'Convenzione', 
                'attachmenttype_codice' => 'LTE_FIRM_CONTR'
            ],       
            'attachment2' =>[     
                'attachmenttype_codice'=>'CONV_FIRM_CONTR',
                'filename' => null,
            ]
        ];

        return $data;
    }

    public static function getAttachmentForCompletamentoSottoscrizione_cartacea_controparte_protocollo($id){
        $data['convenzione_id'] = $id;
        $data['stipula_format'] = 'cartaceo';
        $data['transition'] = 'firma_da_direttore2';        
        $data['stipula_type'] = 'controparte';
        $data['data_fine_conv'] = '31-12-2019';
        $data['data_inizio_conv'] = '04-06-2019';
        $data['data_stipula'] = '03-06-2019';
        $data['attachment1'] = [            
                'filename' => 'nomefile2.pdf', 
                'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
                'model_type' => 'Convenzione', 
                'attachmenttype_codice' => 'LTU_FIRM_ENTRAMBI'
        ];       
        $data['attachment2'] = [
                'attachmenttype_codice'=>'CONV_FIRM_ENTRAMBI',
                'filename' => 'nomefile3.pdf', 
                'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
                'model_type' => 'Convenzione',      
        ];

        return $data;
    }


    public static function getAttachmentForSottoscrizione_digitale_controparte($id){
        $data['convenzione_id'] = $id;
        $data['stipula_format'] = 'digitale';
        $data['stipula_type'] = 'controparte';
        $data['digitale_controparte'] = [
            'attachment1' =>[ 
                'doc' => [
                    'tipo'=>"arrivo",
                    'anno'=>"2019",
                    'num_prot'=>"2019-UNURCLE-0008180",
                    'data_prot'=>"04-04-2019",
                    'nrecord' => '2019-UNURCLE-0008180',
                    'oggetto'=>'UniConv sottoscrizione Lett'
                ],
                'attachmenttype_codice' => 'LTE_FIRM_CONTR_PROT'
            ],       
            'attachment2' =>[     
                'attachmenttype_codice'=>'CONV_FIRM_CONTR',
                'filename' => null,
            ]
        ];

        return $data;
    }


    public static function getAttachmentForRegistrazioneSottoscrizione_digitale_controparte($id){
        $data['convenzione_id'] = $id;
        $data['stipula_format'] = 'digitale';
        $data['stipula_type'] = 'controparte';
        $data['digitale_controparte'] = [
            'attachment1' =>[ 
                'doc' => [
                    'tipo'=>"arrivo",
                    'anno'=>"2019",
                    'num_prot'=>"2019-UNURCLE-0008180",
                    'data_prot'=>"04-04-2019",
                    'nrecord' => '2019-UNURCLE-0008180',
                    'oggetto'=>'UniConv sottoscrizione Lett'
                ],
                'attachmenttype_codice' => 'LTE_FIRM_CONTR_PROT'
            ],       
            'attachment2' =>[     
                'attachmenttype_codice'=>'CONV_FIRM_CONTR',
                'filename' => null,
            ]
        ];

        return $data;
    }





    public static function getOrCreateAzienda($pec){
        //creazione azienda associata
        $az = AziendaLoc::where('pec_email',$pec)->first();
        if ($az==null){
            $az = new AziendaLoc;                        
            $az->fill(AziendaLocTest::getArrayAziendaLoc());                         
            $res = $az->save();
        }
        return $az;
    }

    public static function getConvenzioneValidata($service, $user){
        //preparazione
        $az = ConvenzioneData::getOrCreateAzienda('enrico.oliva@uniurb.it');            
        $data = ConvenzioneData::getNONSchemaTipo($user);
        $data['aziende'] = [ 0 => ['id'=>$az->id]];    
        $conv = $service->create($data);
            
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace(ConvenzioneData::getAttachmentForValidazione($conv->id));

        $conv = $service->updateValidationStep($request);
        return $conv;
    }

    public static function getOrCreateDefaultConvenzione(){
        return ConvenzioneData::getOrCreateConvenzione('convenzione di esempio con scadenza', Auth::user()->id);
    }

    public static function getOrCreateConvenzione($descr, $user_id){

        $res = Convenzione::where('user_id', $user_id)->where('descrizione_titolo',$descr)->first();
        //$res = Convenzione::where('descrizione_titolo',$descr)->first();
        if (!$res){        
            $data = ConvenzioneData::getArrayConvenzione();
            $data['descrizione_titolo'] = $descr;                       
            $entity = new Convenzione;
            $entity->fill($data);                         
            $entity->user_id =  $user_id;
            $res = $entity->save();        
            return $entity;
        }
        return $res;
    } 



}