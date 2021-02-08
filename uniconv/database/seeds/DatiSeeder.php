<?php

use Illuminate\Database\Seeder;
use App\Convenzione;
use App\MappingRuolo;
use App\Role;
use App\Personale;
use Illuminate\Support\Facades\Hash;

//php artisan db:seed --class=DatiSeeder
//composer dump-autoload -o 

////php artisan migrate:fresh --seed
class DatiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {               
        $this->tipopagamenti();
        $this->attachmenttypes();  
        $this->tasktypes();   
        $this->classificazioni();
        $this->mappingtable();
        $this->bollitable();

        $this->mappingruoli();
        $this->insertUsers_Plesso();
    }
  
    private function onlyFirstUpper($value){
        return ucwords(strtolower($value));
    }

    public function insertUsers_Plesso(){
        $adminUsers = [];

        $persColl = Personale::where('aff_org','005199')->get();
        $this->insertUser($persColl, $adminUsers);

        // $adminUsers = ['francesco.calzini@uniurb.it','daniela.capponi@uniurb.it',
        // 'paola.casoli@uniurb.it','alessandra.cupparoni@uniurb.it','adele.guerra@uniurb.it','joseph.fontana@uniurb.it','roberto.pandolfi@uniurb.it'];
        // $persColl = Personale::where('aff_org','004960')->get();
        // $this->insertUser($persColl, $adminUsers);
    }
    
    public function insertUser($persColl, $adminUsers){
        foreach($persColl as $pers){
            $user = new \App\User;                             
            $user->name = $this->onlyFirstUpper((string)$pers->nome).' '.$this->onlyFirstUpper((string)$pers->cognome);
            $user->email = $pers->email;
            $user->password = Hash::make($pers->cod_fis);   
            $user->v_ie_ru_personale_id_ab = $pers->id_ab;
            $user->save();                       

            if (in_array($pers->email, $adminUsers)){
                $user->assignRole('admin');
            }else{
                $user->assignRole('limited');      
            }                        
        }        
    }

    public function mappingruoli(){
    
        $this->insertOffice(config('unidem.unitaSuperAdmin'), 'super-admin');
        $this->insertOffice(config('unidem.unitaAdmin'), 'admin');
        $this->insertOffice(config('unidem.ufficiPerValidazione'), 'op_approvazione');
        $this->insertOffice(config('unidem.uffFiscale'), 'op_contabilita');      
        $this->insertOffice(['005339','005340','005361'], 'admin_amm');           
    }

    private function insertOffice(Array $offices, $rolename){
        $role = Role::where('name', $rolename)->first();
        foreach ($offices as $office) {
            $mp = new MappingRuolo();
            $mp->unitaorganizzativa_uo = $office;
            $uo = $mp->unitaorganizzativa()->get()->first();
            //se esiste l'unità organizzativa
            if ($uo){
                $mp->descrizione_uo = $uo->descr;
                $mp->role_id = $role->id;
                $mp->save();
            }
        }       
    }

    public function bollitable(){
        DB::table('tipobolli')->insert([                          
            'codice' => 'BOLLO_ATTI',
            'descrizione' => 'Bollo convenzione',                     
            'importo' => 16.00
        ]);

        DB::table('tipobolli')->insert([                          
            'codice' => 'BOLLO_TEC_ALLEGATO',
            'descrizione' => 'Bollo allegato tecnico',                     
            'importo' => 2.00
        ]);

    }

    public function mappingtable(){
        

        DB::table('mappinguffici')->insert([                          
            'unitaorganizzativa_uo' => 'XXXXX',
            'descrizione_uo' => 'Attività Sistemistiche  e Software Gestionali e Documentali - S.S.I.A.',                     
            'strutturainterna_cod_uff' => 'YYYYY',
            'descrizione_uff' => 'Attività sistemistiche e software Gestionali e Documentali',                     
        ]);


    }


    public function classificazioni(){
        DB::table('classificazioni')->insert([                          
            'codice' => '03/13',
            'descrizione' => 'III/13 - Progetti e finanziamenti',                     
        ]);

        DB::table('classificazioni')->insert([                          
            'codice' => '03/14',
            'descrizione' => 'III/14 - Accordi per la didattica e per la ricerca',                     
        ]);

        DB::table('classificazioni')->insert([                          
            'codice' => '03/19',
            'descrizione' => 'III/19 - Attività per conto terzi',                     
        ]);
    }


    public function tasktypes(){
        DB::table('tasktypes')->insert([                          
            'name' => 'creazione',
            'code' => 'CREATE_CONV',
            'descrizione' => 'Creazione',         
            'subject' => 'Inserire la convenzione',   
            'content' => 'Inserire la convenzione seguendo lo schema',
        ]);

        DB::table('tasktypes')->insert([   
            'name' => 'validazione',
            'code' => 'VALID_CONV',
            'descrizione' => 'Validazione convenzione',         
            'subject' => 'La convenzione va validata', 
            'content' => 'Attendere la validazione degli organi',  
        ]);
    
        DB::table('tasktypes')->insert([                          
            'name' => 'sottoscrizione',
            'code' => 'SOTT_CONV',
            'descrizione' => 'Sottoscrizione',         
            'subject' => '',
            'content' => '',   
        ]);
    }

    public function tipopagamenti(){
        DB::table('tipopagamenti')->insert([   
            'codice' => 'SU',        
            'descrizione' => 'Soluzione unica al termine della convenzione e/o alla stipula',            
        ]);
        DB::table('tipopagamenti')->insert([           
            'codice' => 'TP',
            'descrizione' => 'Termini di pagamento e tempi di consegna dell’eventuale documentazione',            
        ]);
        DB::table('tipopagamenti')->insert([           
            'codice' => 'SA',
            'descrizione' => 'Stato d’avanzamento',            
        ]);
        DB::table('tipopagamenti')->insert([           
            'codice' => 'CA',
            'descrizione' => 'Conclusione dell’attività',            
        ]);
        DB::table('tipopagamenti')->insert([           
            'codice' => 'RA',
            'descrizione' => 'Rate stabilendo importi',            
        ]);
    }

    public function attachmenttypes(){
        DB::table('attachmenttypes')->insert([   
            'codice' => 'DDD',        
            'gruppo' => 'proposta',
            'descrizione' => 'Decreto del direttore di dipartimento',
            'descrizione_compl' => 'Decreto del direttore di dipartimento',         
            'parent_type' => Convenzione::class,   
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'DCD',        
            'gruppo' => 'proposta',
            'descrizione' => 'Delibera Consiglio di Dipartimento',  
            'descrizione_compl' => 'Delibera Consiglio di Dipartimento',        
            'parent_type' => Convenzione::class,    
        ]);
        
        DB::table('attachmenttypes')->insert([   
            'codice' => 'DR',        
            'gruppo' => 'validazione',
            'descrizione' => 'Decreto Rettorale',            
            'descrizione_compl' => 'Decreto Rettorale',            
            'parent_type' => Convenzione::class,
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'DRU',        
            'gruppo' => 'validazione',
            'descrizione' => "Decreto Rettorale d'urgenza",  
            'descrizione_compl' => "Decreto Rettorale d'urgenza",            
            'parent_type' => Convenzione::class,
        ]);


        DB::table('attachmenttypes')->insert([   
            'codice' => 'DSA',        
            'gruppo' => 'validazione',
            'descrizione' => 'Delibera Senato Accademico',   
            'descrizione_compl' => 'Delibera Senato Accademico',            
            'parent_type' => Convenzione::class,
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'DCA',        
            'gruppo' => 'validazione',
            'descrizione' => 'Delibera Consiglio di Amministrazione',   
            'descrizione_compl' => 'Delibera Consiglio di Amministrazione',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'CB',        
            'gruppo' => 'proposta',
            'descrizione' => 'Convenzione bozza',   
            'descrizione_compl' => 'Convenzione bozza',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'PR',        
            'gruppo' => 'proposta',
            'descrizione' => 'Prospetto ripartizione costi e proventi',   
            'descrizione_compl' => 'Prospetto ripartizione costi e proventi',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'DA',      
            'gruppo' => 'proposta',
            'descrizione' => 'Documento appoggio',   
            'descrizione_compl' => 'Documento appoggio',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'LTU_FIRM_UNIURB',      
            'gruppo' => 'sottoscrizione',
            'descrizione' => 'Lettera di trasmissione stipula',   
            'descrizione_compl' => 'Lettera di trasmissione stipula',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'LTU_FIRM_ENTRAMBI',      
            'gruppo' => 'sottoscrizione',
            'descrizione' => 'Lettera di trasmissione completamento',   
            'descrizione_compl' => 'Lettera di trasmissione completamento',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'LTU_FIRM_ENTRAMBI_PROT',      
            'gruppo' => 'sottoscrizione',
            'descrizione' => 'Lettera di trasmissione completamento',   
            'descrizione_compl' => 'Lettera di trasmissione completamento già protocollata',   
            'parent_type' => Convenzione::class,         
        ]);
       
        DB::table('attachmenttypes')->insert([   
            'codice' => 'LTE_FIRM_CONTR',      
            'gruppo' => 'sottoscrizione',
            'descrizione' => 'Lettera ricevuta dalla ditta',   
            'descrizione_compl' => 'Lettera ricevuta dalla ditta per convenzione firmata',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'LTE_FIRM_CONTR_PROT',      
            'gruppo' => 'sottoscrizione',
            'descrizione' => 'Lettera ricevuta dalla ditta',   
            'descrizione_compl' => 'Lettera ricevuta dalla ditta per convenzione firmata già protocollata',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'LTE_FIRM_ENTRAMBI',      
            'gruppo' => 'sottoscrizione',
            'descrizione' => 'Lettera ricevuta dalla ditta',   
            'descrizione_compl' => 'Lettera ricevuta dalla ditta per convenzione firmata da entrambi',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'LTE_FIRM_ENTRAMBI_PROT',      
            'gruppo' => 'sottoscrizione',
            'descrizione' => 'Lettera ricevuta dalla ditta',   
            'descrizione_compl' => 'Lettera ricevuta dalla ditta firmata per convenzione da entrambi già protocollata',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'DOC_BOLLATO_FIRMATO',      
            'gruppo' => 'repertoriazione',
            'descrizione' => 'Convenzione firmata e bollata',  
            'descrizione_compl' => 'Convenzione firmata e bollata',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'DOC_BOLLATO',      
            'gruppo' => 'repertoriazione',
            'descrizione' => 'Convenzione bollata',   
            'descrizione_compl' => 'Convenzione bollata',   
            'parent_type' => Convenzione::class,         
        ]);


        DB::table('attachmenttypes')->insert([   
            'codice' => 'CONV_FIRM_ENTRAMBI',      
            'gruppo' => 'sottoscrizione',
            'descrizione' => 'Convenzione firmata',   
            'descrizione_compl' => 'Convenzione firmata',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'CONV_FIRM_CONTR',      
            'gruppo' => 'sottoscrizione',
            'descrizione' => 'Convenzione firmata dalla controparte',   
            'descrizione_compl' => 'Convenzione firmata dalla controparte',   
            'parent_type' => Convenzione::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'CONV_FIRM_UNIURB',      
            'gruppo' => 'sottoscrizione',
            'descrizione' => 'Convenzione firmata da UniUrb',   
            'descrizione_compl' => 'Convenzione firmata da UniUrb',   
            'parent_type' => Convenzione::class,         
        ]);


        
        DB::table('attachmenttypes')->insert([   
            'codice' => 'NOTA_DEBITO',      
            'gruppo' => 'emissione',
            'descrizione' => 'Nota di debito',   
            'descrizione_compl' => 'Nota di debito',   
            'parent_type' => Scadenza::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'FATTURA_ELETTRONICA',      
            'gruppo' => 'emissione',
            'descrizione' => 'Fattura elettronica',   
            'descrizione_compl' => 'Fattura elettronica',   
            'parent_type' => Scadenza::class,         
        ]);

        DB::table('attachmenttypes')->insert([   
            'codice' => 'RICHIESTA_PAGAMENTO',      
            'gruppo' => 'emissione',
            'descrizione' => 'Richiesta pagamento',   
            'descrizione_compl' => 'Richiesta pagamento',   
            'parent_type' => Scadenza::class,         
        ]);
    }
}
