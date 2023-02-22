<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Repositories\ConvenzioneRepository;
use App\Service\ConvenzioneService;
use App\Convenzione;
use App\User;
use App\Provincia;
use App\AttachmentType;
use App\Attachment;
use App\AziendaLoc;
use JWTAuth;
use App\Http\Controllers\SoapControllerTitulus;
use Artisaninweb\SoapWrapper\SoapWrapper;
use App\Http\Controllers\Api\V1\LocalitaController;
use App\Http\Controllers\Api\V1\DipartimentoController;
use Illuminate\Http\UploadedFile;
use Exception;
use Storage;
use Workflow;
use Auth;
use App\Notifications\ConvenzioneApprovata;
use Illuminate\Support\Facades\Notification;
use App\Soap\Request\SaveDocument;
use App\Soap\Request\SaveParams;


//./vendor/bin/phpunit  --testsuite Unit
//./vendor/bin/phpunit  --testsuite Unit --filter testConnection
class ConvenzioneTest extends TestCase
{
    use WithoutMiddleware;
    
    //https://unidemdev.uniurb.it/unidem/uniconv/uniconv/public/              


    // ./vendor/bin/phpunit  --testsuite Unit --filter testCreate1
    public function testCreate1()
    {
        $repo = new ConvenzioneRepository($this->app);          
        $user = User::where('email','test.admin@uniurb.it')->first();        

        $this->actingAs($user);

        //mi occorrono due aziende
        $az1 = AziendaLocTest::getOrCreateAziendaLoc('azienda uniconv1');
        $this->assertNotNull($az1);

        $az2 = AziendaLocTest::getOrCreateAziendaLoc('azienda uniconv2');
        $this->assertNotNull($az2);

        $data = [
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
            'aziende' => [
                $az1->toArray(),
                $az2->toArray(),
            ],
            'bollo_virtuale' => true,    
            'bollo_atti' => 
                [
                    'tipobolli_codice' => 'BOLLO_ATTI',
                    'num_bolli' => 10,
                    'num_righe' => 1000
                ],            
                'bollo_allegati' =>
                    [
                        'tipobolli_codice' => 'BOLLO_ALLEGATI',
                        'num_bolli' => 1,
                        'num_righe' => 100
                    ],            
            'convenzione_type' => 'TO',
            'user' => $user,            
            'ambito' => 'istituzionale',            
            'current_place' => null,
            'titolario_classificazione' => '03/13',
            'oggetto_fascicolo'=> 'fascicolo oggetto'
        ];           

        $result = $repo->create($data);

        $this->assertNotNull($result);        
        //rilettura
        $convenzione = $repo->findBy('id',$result->id);
        $this->assertEquals(2, $convenzione->aziende()->get()->count());
        $this->assertEquals(1250.00,$convenzione->importo);
        $this->assertEquals(12345.23,$convenzione->corrispettivo);
        $this->assertEquals('istituzionale',$convenzione->ambito);
        $this->assertGreaterThan(0,count($convenzione->bolli));

        $convenzione->usertasks()->delete();
        $repo->delete($convenzione->id);
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testCreateApi
    public function testCreateApi()
    {
        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $token = JWTAuth::fromUser( $user);
        // "Accept" => "application/json"
        $headers = ['Authorization' => "Bearer $token"];

        $conv = new Convenzione(ConvenzioneData::getArrayConvenzione());
        $data = $conv->toArray();
        $data['user'] = $user->toArray();

        $response = $this->json('POST', 'api/v1/convenzioni', $data, $headers)
            ->assertStatus(200);            

        $conv = Convenzione::find($response->getData()->id);
        $this->assertNotNull($conv->nrecord);

        $conv->usertasks()->delete();
        $repo = new ConvenzioneRepository($this->app);      
        $repo->delete($conv->id);
    }



    //./vendor/bin/phpunit  --testsuite Unit --filter testStore
    public function testStore()
    {
        $repo = new ConvenzioneRepository($this->app);          

        $user = User::where('email','test.admin@uniurb.it')->first();
        $this->actingAs($user);

        $data = ConvenzioneData::getArrayConvenzione();
        $data['user'] = ['id'=>$user->id];

        $result = $repo->create($data);
                               
        $convenzione = $repo->update($result->toArray(), $result->id);
        $this->assertEquals("proposta",$convenzione->current_place);      
        
        $result->usertasks()->delete();
        $repo->delete($result->id);
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testCreateConvenzioneAmministrativa
    public function testCreateConvenzioneAmministrativa(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);

        $user = User::where('email','enrico.oliva@uniurb.it')->first();      
        $this->actingAs($user);
        
        $data = ConvenzioneData::getConvenzioneAmministrativa($user);
        $conv = $service->create_amministrativa($data);
        
        $this->assertNotNull($conv);   
        
        //verifica task creato 
        $task = $conv->usertasks()->first();
        $this->assertNotNull($task);        
        $this->assertEquals('aperto',$task->state);

        $conv->usertasks()->delete();
        $repo->delete($conv->id);

    }



     /**
     * test risposta percorso
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');
        $response->assertStatus(404);
    }

    // public function testAttributeExistance()
    // {
    //     $this->assertObjectHasAttribute('user_id', new Convenzione);
    // }
    
    
    public function testConvenzionePagamenti()
    {
        $user = User::where('email','test.admin@uniurb.it')->first();
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', 'api/v1/convenzioni/pagamenti', [], $headers)
            ->assertStatus(200)
            ->assertJsonCount(5);
        //echo($response->getContent());
    }

    /**
     * test risposta con decodifiche
     *
     * @return void
     */
    //./vendor/bin/phpunit  --testsuite Unit --filter testConvenzioneFindById
    public function testConvenzioneFindById()
    {
        $user = User::where('email','test.admin@uniurb.it')->first();      
        $this->actingAs($user);

        $data = ConvenzioneData::getArrayConvenzione();
        $data['user_id']=$user->id;
         
        $conv = factory(Convenzione::class)->create($data);

        
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', 'api/v1/convenzioni/'.$conv->id, [], $headers)
            ->assertStatus(200)
            ->assertJson(
                 [
                    "descrizione_titolo" =>  "convenzione di esempio",
                    'dipartimento' => [[
                        'cd_dip'=>'21',
                        "nome_dip"=> "Dipartimento di Studi Umanistici (DISTUM)",
                        "nome_breve"=> "Dipartimento di Studi Umanistici (DISTUM)",
                        "dip_id"=> "26121",
                        "id_ab"=> "26121"
                    ]]
                ] 
            );

        $conv->usertasks()->delete();
        $repo = new ConvenzioneRepository($this->app);    
        $repo->delete($conv->id);
        //$json = $response->getContent();
        //echo($json);
        
    }

    /**
     * test risposta percorso
     *
     * @return void
     */
    //./vendor/bin/phpunit  --testsuite Unit --filter testConvezioniAreListedCorrectly
    public function testConvezioniAreListedCorrectly()
    {
        $user = User::where('email','test.admin@uniurb.it')->first();      
        $this->actingAs($user);

        $data = ConvenzioneData::getArrayConvenzione();
        $data['user_id']=$user->id;      
            
        $conv = factory(Convenzione::class)->create($data);
        
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', 'api/v1/convenzioni', [], $headers)
            ->assertStatus(200);
            // ->assertJson([         
            //     'data' => [ ['descrizione_titolo' => 'convenzione di esempio' ] ]                   
            // ]);

            // ->assertJsonStructure([
            //     '*' => ['id', 'body', 'title', 'created_at', 'updated_at'],
            // ]);

        $conv->usertasks()->delete();
        $repo = new ConvenzioneRepository($this->app);    
        $repo->delete($conv->id);
    }

   

 //./vendor/bin/phpunit  --testsuite Unit --filter testApiDipartimenti
    public function testApiDipartimenti(){
        $user = User::where('email','test.admin@uniurb.it')->first();      
        $this->actingAs($user);
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', 'api/v1/dipartimenti', [], $headers)
            ->assertStatus(200)
            ->assertJson([        
                 ["dip_id"=>"4499","cd_dip"=>"1","cd_miur"=>"15230"]                    
            ]);

            //"id_ab"=>"4499","part_iva"=>"00448830414","nome_dip"=>"DIPARTIMENTO DI GIURISPRUDENZA" 
        $response = $this->json('GET', 'api/v1/dipartimenti/docenti/21', [], $headers)
            ->assertStatus(200)
            ->assertJsonFragment([[
                 "nome" =>"MARIO", "cognome" => "RIZZARDI",  "user_email"=> null //"mario.rizzardi@uniurb.it"                   
            ]]);
            
        $response = $this->json('GET', 'api/v1/dipartimenti/direttore/21', [], $headers)
            ->assertStatus(200)
            ->assertJsonFragment([        
                "cognome"=>"Martini","nome"=>"Berta", "nome_esteso" => "Martini Berta"
            ]);
            
    }

    /** @test */
    public function testControllerLocalita()
    {
        $contr = new LocalitaController();
        $result = $contr->getComuniByCodiceProvincia('PU');

        $this->assertNotNull($result);
        $this->assertGreaterThanOrEqual(72,$result->count());
 
        $result = $contr->getComuniByCodiceProvincia('ZZ');
        $this->assertNotNull($result);
        //non è più una colleciton laravel
        $this->assertGreaterThanOrEqual(0,count($result));
    }


    public function testApiLocalita(){
        $user = User::where('email','test.admin@uniurb.it')->first();      
        $this->actingAs($user);
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', 'api/v1/provincie', [], $headers)
            ->assertStatus(200);

        $response = $this->json('GET', 'api/v1/provincie/PU', [], $headers)
            ->assertStatus(200);

        $response = $this->json('GET', 'api/v1/comuni/', [], $headers)
            ->assertStatus(200);                    

        $response = $this->json('GET', 'api/v1/comuni?prov=PU', [], $headers)
            ->assertStatus(200);                
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testDecode
    public function testDecode(){

        $contr = new DipartimentoController();
        $decode = $contr->decodeDescrizione('20');

        $this->assertEquals( "Dipartimento di Scienze Pure e Applicate (DiSPeA)",  $decode);

        try{
            $decode = $contr->decodeDescrizione('00000');
        }catch(Exception $e){
            $this->assertEquals('No query results for model [App\Dipartimento] 00000', $e->getMessage());
        }
        
    }

     //./vendor/bin/phpunit  --testsuite Unit --filter testApiAzienda
    public function testApiAzienda(){
        $user = User::where('email','test.admin@uniurb.it')->first();
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];
        $id = '4674';
        $response = $this->json('GET', 'api/v1/aziende/'.$id, [], $headers)
            ->assertStatus(200);  

        $response = $this->json('GET', 'api/v1/aziende/indirizzo/'.$id, [], $headers)
            ->assertStatus(200)
            ->assertJsonFragment(
                ["indirizzo"=>"Via Donizetti, 27/3", "cd_cap"=>"61033", "comune"=>[
                    "cod"=> "D541",
                    "descr"=> "Fermignano"
                ]
            ]);

        echo($response->getContent());
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testReadStoreFile
    public function testReadStoreFile(){
        
        Storage::disk('local')->put('file.txt', 'Primo contenuto');
        $contents = Storage::get('file.txt');
        $this->assertEquals('Primo contenuto',$contents);
    }

    // public function testDocumentUpload()
    // {
    //     $user = User::where('email','test.admin@uniurb.it')->first();
    //     $token = JWTAuth::fromUser($user);

    //     $conv = Convenzione::first();
    //     //'Content-Type'=>'application/pdf' multipart/form-data
    //     $headers = ['Authorization' => "Bearer $token"];
    //     //Storage::fake('avatars');        
    //     $this->json('POST', 'api/v1/convenzioni/pdf', [
    //         'id' => $conv->id,
    //         'convenzione_pdf' => ['value' => base64_encode(Storage::get('convenzione.pdf'))]            
    //     ], $headers)->assertStatus(200);

    //     //UploadedFile::fake()->create('document.pdf', $sizeInKilobytes)
    //     // Assert the file was stored...
    //     Storage::disk('local')->assertExists('/convenzioni/'.$conv->id.'_convenzione.pdf');    
    // }

    //./vendor/bin/phpunit  --testsuite Unit --filter testWorkFlow
    public function testWorkFlow()
    {     
        $user = User::where('email','test.admin@uniurb.it')->first();      
        $this->actingAs($user);

        $conv = Convenzione::where('current_place','=','proposta')->first();
        $workflow = Workflow::get($conv, $conv->getWorkflowName());
        // if more than one workflow is defined for the BlogPost class            

        $this->assertFalse($workflow->can($conv, 'test')); // False
        $this->assertTrue($workflow->can($conv, 'store_to_approvato')); // True
        $this->assertFalse($workflow->can($conv, 'store_proposta')); // True

        // Apply a transition
        $workflow->apply($conv, 'store_to_approvato');
        //$conv->save();
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testReadStoreAttachment
    public function testReadStoreAttachment(){
        $filename = 'filetest.txt';
        Storage::disk('local')->put('filetest.txt', 'Primo contenuto');
        $contents = Storage::get('filetest.txt');

        $user = User::where('email','enrico.oliva@uniurb.it')->first();   
        $this->actingAs($user);
        $data = ConvenzioneData::getArrayConvenzione();
        $data['user_id']=$user->id;                  
        $conv = factory(Convenzione::class)->create($data);
        //$conv = Convenzione::where('id','>',0)->first();

        $type = AttachmentType::where('codice','DCD')->first();
        /** @var Attachment $attachment */
        $attachment = new Attachment();
        $attachment->docnumber = 'ab123';
        $attachment->emission_date = '12-03-2019';
        $attachment->model()->associate($conv);
        $attachment->fromStream($contents, $filename, $type);
        $attachment->save();
        //$conv->attachments()->save($attachment);
        //echo($attachment);

        $tot = $conv->attachments->count();
        $this->assertGreaterThan(0,$tot);
        $attachment->delete();
        $conv->refresh();
        $tot = $tot - 1;
        $this->assertEquals($tot, $conv->attachments->count());

        $conv->usertasks()->delete();
        $repo = new ConvenzioneRepository($this->app);    
        $repo->delete($conv->id);
    }

      //./vendor/bin/phpunit  --testsuite Unit --filter testCreateSchemaTipo
    public function testCreateSchemaTipo()
    {
        $repo = new ConvenzioneRepository($this->app);          
        $user = User::where('email','test.admin@uniurb.it')->first();      
        $this->actingAs($user);

        $data = ConvenzioneData::getArrayConvenzione();
        $data['user'] =  $user;
        $data['attachments'] = [
            [ 
                'filename' => 'nomefile.pdf', 
                'filevalue' =>  base64_encode(Storage::get('convenzione.pdf')), 
                'model_type' => 'Convenzione', 
                'attachmenttype_codice' => 'DA'
            ]
        ];

        $result = $repo->create($data);
        $this->assertNotNull($result);

        $dip = $result->dipartimento->first();
        $uo = $dip->unitaOrganizzativa()->first();
        $this->assertEquals('004939',$uo->uo);

        $att = $result->attachments()->first();
        $this->assertStringMatchesFormat('%sConvenzione_'.$result->id.'%s',$att->filepath);
        Storage::disk('local')->assertExists($att->filepath);

        $result->usertasks()->delete();
        $repo->delete($result->id);
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testAPI_UnitaOrganizzativa_validationOffices
    public function testAPI_UnitaOrganizzativa_validationOffices(){
        $user = User::where('email','test.admin@uniurb.it')->first();      
        $this->actingAs($user);
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', 'api/v1/convenzioni/uffici/validazione', [], $headers)
            ->assertStatus(200);
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testUnitaOrganizzativa_validationOffices
    public function testUnitaOrganizzativa_validationOffices(){    
        $result = \App\UnitaOrganizzativa::UfficiValidazione()->get();    
        //$this->assertEquals(9, $result->count());        
        //$this->assertEquals(7, $result->count());        

        // $result = \App\UnitaOrganizzativa::find(38344);
        // $org = $result->organico();  
        // $this->assertEquals(4, $org->count());        

        // $personale = $result->personale();
        // $this->assertEquals(2, $personale->count());        

        //PLESSO Plesso Scientifico (DiSPeA-DiSB)
        $result = \App\UnitaOrganizzativa::find(32718);
        $this->assertTrue($result->isPlesso());

        $dipartimenti = $result->dipartimenti();
        $this->assertEquals(2, $dipartimenti->count());        
    }


    //./vendor/bin/phpunit  --testsuite Unit --filter testDipartimentiByUser
    public function testDipartimentiByUser(){          
        $user = User::where('email','enrico.oliva@uniurb.it')->first();      
        $this->actingAs($user);

        $ctr = new DipartimentoController();
        $dipartimenti = $ctr->getDipartimentiByUser();

        //$this->assertGreaterThanOrEqual(6, $dipartimenti->count());  
        
        //TODO temporaneamente sette? 
        $this->assertEquals(6, $dipartimenti->count());        
        //impersono utente 'BUCHI'
        $user->v_ie_ru_personale_id_ab = 33123;
        $this->actingAs($user);

        $dipartimenti = $ctr->getUserDepartments();
        $this->assertEquals(2, $dipartimenti->count());                
    }


    //./vendor/bin/phpunit  --testsuite Unit --filter testCreateNONSchemaTipo
    public function testCreateNONSchemaTipo(){    
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);

        $user = User::where('email','enrico.oliva@uniurb.it')->first();      
        $this->actingAs($user);
        
        $data = ConvenzioneData::getNONSchemaTipo($user);
        $conv = $service->create($data);
        
        $this->assertNotNull($conv);        
        //verifica task creato 
        $task = $conv->usertasks()->first();
        $this->assertNotNull($task);        
        $this->assertEquals('aperto',$task->state);

        $this->assertFalse($task->checkAndChangeState());

        $conv->usertasks()->delete();
        $repo->delete($conv->id);

    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testApiUserTasks
    public function testApiUserTasks(){
        $user = User::where('email','test.admin@uniurb.it')->first();      
        $this->actingAs($user);
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', 'api/v1/usertask/users/'.$user->id.'/tasks', [], $headers)
            ->assertStatus(200);

        $response = $this->json('GET', 'api/v1/usertask/tasks', [], $headers)
            ->assertStatus(200);       
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testApiTipoPagamenti
    public function testApiTipoPagamenti(){
        $user = User::where('email','test.admin@uniurb.it')->first();
        $token = JWTAuth::fromUser( $user);        
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', 'api/v1/tipopagamenti', [], $headers)
            ->assertStatus(200);
    }


    //./vendor/bin/phpunit  --testsuite Unit --filter testApiClassificazione
    public function testApiClassificazione(){
        $user = User::where('email','test.admin@uniurb.it')->first();
        $token = JWTAuth::fromUser( $user);        
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', 'api/v1/classificazioni', [], $headers)
            ->assertStatus(200);
    }


    //In caso di clonazione del db in preprod non va eseguito
     //./vendor/bin/phpunit  --testsuite Unit --filter testApiAttachment
    //  public function testApiAttachment(){
    //     $user = User::where('email','test.admin@uniurb.it')->first();
    //     $token = JWTAuth::fromUser( $user);        
    //     $headers = ['Authorization' => "Bearer $token"];

    //     $attach = Attachment::where('id','>','0')->first();
    //     $response = $this->json('GET', 'api/v1/attachments/download/'.$attach->id, [], $headers)
    //         ->assertStatus(200);
            
    // }

    

     //./vendor/bin/phpunit  --testsuite Unit --filter testApiValidazione
     public function testApiValidazione(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);
        
        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);

        $data = ConvenzioneData::getNONSchemaTipo($user);
        $conv = $service->create($data);

        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        //$request->replace(ConvenzioneData::getAttachmentForValidazione($conv->id));
        $request->replace(ConvenzioneData::getEmpty_AttachmentForValidazione($conv->id));
        $conv = $service->updateValidationStep($request);

        $this->assertEquals('approvato', $conv->current_place);        

        //pulire tutti gli attach collegati
        //pulire tutti i task collegati
        //pulire la convenzione
        $conv->usertasks()->delete();
        $repo->delete($conv->id);
     }

    //./vendor/bin/phpunit  --testsuite Unit --filter testApiValidazione_SenzaDocumento
    public function testApiValidazione_SenzaDocumento(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);
        
        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);

        $data = ConvenzioneData::getNONSchemaTipo($user);
        $conv = $service->create($data);

        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');    
        $request->replace(ConvenzioneData::getEmpty_AttachmentForValidazione($conv->id));
        $conv = $service->updateValidationStep($request);

        $this->assertEquals('approvato', $conv->current_place);        
        $this->assertNotNull($conv->attachments()->first());

        //pulire tutti gli attach collegati
        //pulire tutti i task collegati
        //pulire la convenzione
        $conv->usertasks()->delete();
        $repo->delete($conv->id);
    }


    //./vendor/bin/phpunit  --testsuite Unit --filter testApiTransitions
    public function testApiTransitions(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);      
        
        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);

        $data = ConvenzioneData::getNONSchemaTipo($user);
        $conv = $service->create($data);

        $conv1 = $repo->findBy('current_place','inapprovazione')->first();
        $actions = $service->nextPossibleActions($conv1->id);

        $this->assertGreaterThan(0, $actions->count());

        $conv->usertasks()->delete();
        $repo->delete($conv->id);
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testApiNotification
    public function testApiNotification(){
        Notification::fake();

        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);
        $conv = Convenzione::where('id','>',0)->first();
        
        $user->notify(new ConvenzioneApprovata($conv));        

        // Assert a notification was sent to the given users...
        Notification::assertSentTo(
            [$user], ConvenzioneApprovata::class
        );

        $this->assertGreaterThanOrEqual(1,$user->notifications->count());

        foreach ($user->notifications as $notification) {
            //$this->assertEquals('App\Notifications\ConvenzioneApprovata', $notification->type);       
            $notification->markAsRead();           
        }

        foreach ($user->notifications as $notification) {
            $this->assertNotNull($notification->read_at);  
        }
    }  


    //./vendor/bin/phpunit  --testsuite Unit --filter testApiSottoscrizione_cartaceo_uniurb
    public function testApiSottoscrizione_cartaceo_uniurb(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);
        
        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);
        
        $conv = ConvenzioneData::getConvenzioneValidata($service, $user);
        $this->assertEquals('approvato', $conv->current_place);        

        //test cartaceo uniurb
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace(ConvenzioneData::getAttachmentForSottoscrizione_cartaceo_uniurb($conv->id));
        
        $conv = $service->updateSottoscrizioneStep($request)['data'];

        foreach ($conv->attachments()->get() as $attachment) {
            if ($attachment->attachmenttype_codice == 'LTU_FIRM_UNIURB'){
                $this->assertNotNull($attachment->nrecord);
                var_dump($attachment->nrecord);
                $this->assertNotNull($attachment->num_prot);
            }
        }        

        //pulire tutti gli attach collegati
        //pulire tutti i task collegati
        //pulire la convenzione
        $conv->usertasks()->delete();
        $repo->delete($conv->id);
    }
   

    //./vendor/bin/phpunit  --testsuite Unit --filter testApiSottoscrizione_digitale_uniurb
    public function testApiSottoscrizione_digitale_uniurb(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);
        
        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);
        
        $conv = ConvenzioneData::getConvenzioneValidata($service, $user);
        $this->assertEquals('approvato', $conv->current_place);        

        //test cartaceo uniurb
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace(ConvenzioneData::getAttachmentForSottoscrizione_digitale_uniurb($conv->id));
        
        $conv = $service->updateSottoscrizioneStep($request)['data'];

        foreach ($conv->attachments()->get() as $attachment) {        
            if ($attachment->attachmenttype_codice == 'LTU_FIRM_UNIURB' || $attachment->attachmenttype_codice == 'LTU_FIRM_UNIURB'){    
                $this->assertNotNull($attachment->nrecord);
                var_dump($attachment->nrecord);
                $this->assertNotNull($attachment->num_prot);            
                var_dump($attachment->num_prot);
            }
        }  

        $conv->usertasks()->delete();
        $repo->delete($conv->id);      
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testApiSottoscrizione_cartacea_controparte_noprotocol
    public function testApiSottoscrizione_cartacea_controparte_noprotocol(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);
        
        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);
        
        $conv = ConvenzioneData::getConvenzioneValidata($service, $user);
        $this->assertEquals('approvato', $conv->current_place);        

        //test cartaceo uniurb
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace(ConvenzioneData::getAttachmentForSottoscrizione_cartacea_controparte($conv->id));
        
        $conv = $service->updateSottoscrizioneStep($request)['data'];

        $this->assertEquals($conv->data_sottoscrizione,'10-04-2019');

        $conv->usertasks()->delete();
        $repo->delete($conv->id);    
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testApiSottoscrizione_cartacea_controparte_protocol
    public function testApiSottoscrizione_cartacea_controparte_protocol(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);
        
        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);
        
        $conv = ConvenzioneData::getConvenzioneValidata($service, $user);
        $this->assertEquals('approvato', $conv->current_place);        

        //test cartaceo uniurb
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace(ConvenzioneData::getAttachmentForSottoscrizione_cartacea_controparte_protocollo($conv->id));
        
        $conv = $service->updateSottoscrizioneStep($request)['data'];

        foreach ($conv->attachments()->get() as $attachment) {        
            if ($attachment->attachmenttype_codice == 'LTE_FIRM_CONTR' || $attachment->attachmenttype_codice == 'LTE_FIRM_CONTR'){    
                $this->assertNotNull($attachment->nrecord);
                var_dump($attachment->nrecord);
                $this->assertNotNull($attachment->num_prot);            
                var_dump($attachment->num_prot);
            }
        }

        //Completamento
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace(ConvenzioneData::getAttachmentForCompletamentoSottoscrizione_cartacea_controparte_protocollo($conv->id));

        $conv = $service->updateComplSottoscrizioneStep($request)['data'];

        foreach ($conv->attachments()->get() as $attachment) {        
            if ($attachment->attachmenttype_codice == 'LTU_FIRM_ENTRAMBI' || $attachment->attachmenttype_codice == 'CONV_FIRM_ENTRAMBI'){    
                $this->assertNotNull($attachment->nrecord);
                var_dump($attachment->nrecord);
                $this->assertNotNull($attachment->num_prot);            
                var_dump($attachment->num_prot);
            }
        }

        $conv->usertasks()->delete();
        $repo->delete($conv->id); 
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testApiSottoscrizione_digitale_controparte
    public function testApiSottoscrizione_digitale_controparte(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);
        
        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);
        
        $conv = ConvenzioneData::getConvenzioneValidata($service, $user);
        $this->assertEquals('approvato', $conv->current_place);        

        //test cartaceo uniurb
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace(ConvenzioneData::getAttachmentForSottoscrizione_digitale_controparte($conv->id));

        $conv = $service->updateSottoscrizioneStep($request)['data'];
        foreach ($conv->attachments()->get() as $attachment) {        
            if ($attachment->attachmenttype_codice == 'LTE_FIRM_CONTR_PROT'){    
                $this->assertNotNull($attachment->nrecord);
                var_dump($attachment->nrecord);
                $this->assertNotNull($attachment->num_prot);            
                var_dump($attachment->num_prot);
            }
        }   

        $conv->usertasks()->delete();
        $repo->delete($conv->id);      
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testRegistrazioneSottoscrizione_cartacea_controparte_noprotocol
    public function testRegistrazioneSottoscrizione_cartacea_controparte_noprotocol(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);
        
        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);
        
        $conv = ConvenzioneData::getConvenzioneValidata($service, $user);
        $this->assertEquals('approvato', $conv->current_place);        

        //test cartaceo uniurb
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace(ConvenzioneData::getAttachmentForRegistrazioneSottoscrizione_digitale_controparte($conv->id));
        
        $conv = $service->registrazioneSottoscrizione($request)['data'];
        
        $attach = $conv->attachments()->get();

        $this->assertGreaterThan(1,$attach->count());    

        //test cartaceo uniurb
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace($conv->toArray());
        
        $conv = $service->cancellazioneSottoscrizione($request)['data'];
        $this->assertEquals($conv->currentPlace, 'approvato');

        $conv->usertasks()->delete();
        $repo->delete($conv->id); 
    }


}


//https://laracasts.com/discuss/channels/eloquent/associating-hasone-relationship-without-saving-it-in-laravel-5

//https://www.toptal.com/laravel/restful-laravel-api-tutorial
//https://blog.pusher.com/build-rest-api-laravel-api-resources/