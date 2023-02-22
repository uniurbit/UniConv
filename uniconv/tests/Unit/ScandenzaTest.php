<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Role;
use App\User;
use App\Permission;
use App\Scadenza;
use App\Convenzione;
use App\FindParameter;
use App\UserTask;
use JWTAuth;
use Faker\Generator as Faker;
use App\Http\Controllers\Api\V1\QueryBuilder;
use App\Repositories\ConvenzioneRepository;
use App\Service\ConvenzioneService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class ScadenzaTest extends TestCase
{
    use WithoutMiddleware;

    public static function getArrayScadenza(){
        return [
            'data_tranche' => '10-03-2019',
            'dovuto_tranche' => '1000.00',                             
        ];
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testScadenza
    public function testScadenza(){      
                       
        $user = User::where('email','test.admin@uniurb.it')->first();      
        $this->actingAs($user);
      
        $entity = new Scadenza;        
        
        $data = ScadenzaTest::getArrayScadenza();
        $data['convenzione_id']= ConvenzioneData::getOrCreateDefaultConvenzione()->id;

        $entity->fill($data);                         
        $res = $entity->save();                            
        $result = Scadenza::find($entity->id)->toArray();

        $this->assertEquals($data['data_tranche'], $result['data_tranche']);    
        $this->assertEquals($data['dovuto_tranche'], $result['dovuto_tranche']);      

        $entity->delete();
        $repo = new ConvenzioneRepository($this->app);     
        $entity->usertasks()->delete();
        $entity->convenzione->usertasks()->delete();
        $repo->delete($entity->convenzione_id);
        //$az->delete();
    }

     //./vendor/bin/phpunit  --testsuite Unit --filter testApiScadenza
     public function testApiScadenza(){
        $user = User::where('email','test.admin@uniurb.it')->first();     
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];        
        $user->assignRole('super-admin');

        $response = $this->json('GET', 'api/v1/scadenze/new', [], $headers)
            ->assertStatus(200);
        
        $response = $this->json('POST', 'api/v1/scadenze/query', [
            'rules'=> [                                                
            ]
        ], $headers)
            ->assertStatus(200);      
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testQueryScadenza
    public function testQueryScadenza(){

        $entity = new Scadenza;                
        
        $user = User::where('email','test.admin@uniurb.it')->first();      
        $this->actingAs($user);

        $data = ScadenzaTest::getArrayScadenza();
        $data['convenzione_id']= ConvenzioneData::getOrCreateDefaultConvenzione()->id;
        $entity->fill($data);                         
        $res = $entity->save();                            
        //{"operator":"=","field":"convenzione.id","value":"1"},
        $rules = json_decode('{"rules":[{"operator":"contains","field":"convenzione.descrizione_titolo","value":"convenzione di esempio"}],"limit":25,"sessionId":null,"page":null}',true);

        $parameters = [];
        $parameters['rules'] = $rules['rules'];
        $parameters['includes'] = 'convenzione';
        $parameters['columns'] = 'id,data_tranche,dovuto_tranche,convenzione_id,convenzione.id,convenzione.descrizione_titolo';
        $findparam =new FindParameter($parameters);      

        $queryBuilder = new QueryBuilder(new Scadenza, null, $findparam);
        $result = $queryBuilder->build()->paginate(); 
        $this->assertNotNull($result);      
        $data = $result->getCollection();
        $this->assertNotNull($data);      
        $this->assertGreaterThan(0,  $data->count());

        $entity->delete();
        $repo = new ConvenzioneRepository($this->app);     
        $entity->usertasks()->delete();
        $entity->convenzione->usertasks()->delete();
        $repo->delete($entity->convenzione_id);
    }

      //./vendor/bin/phpunit  --testsuite Unit --filter testRichiestaEmissioneScandenza
    public function testRichiestaEmissioneScandenza(){
        
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);

        $entity = new Scadenza;                
                
        $user = User::where('email','enrico.oliva@uniurb.it')->first();      
        $this->actingAs($user);

        $data = ScadenzaTest::getArrayScadenza();
        $data['convenzione_id']= ConvenzioneData::getOrCreateDefaultConvenzione()->id;
        $entity->fill($data);                         
        $res = $entity->save();   

        // $requestdata = '{
        //     "id":11,
        //     "convenzione_id":12,
        //     "data_tranche":"19-05-2019",
        //     "dovuto_tranche":"9999.00",
        //     "data_emisrichiesta":null,"protnum_emisrichiesta":null,"data_fattura":"","num_fattura":null,"data_ordincasso":"","num_ordincasso":null,"prelievo":null,"note":null,
        //     "state":"attivo",
        //     "transitions":[{"label":"Attiva","value":"self_transition","transitions":{}},{"label":"Richiesta emissione","value":"richiestaemissione","transitions":{}}],
        //     "convenzione":{"id":12,"descrizione_titolo":"convenzione di esempio"},
        //     "assignments":[{"v_ie_ru_personale_id_ab":"39842"}],"unitaorganizzativa_uo":"005680","respons_v_ie_ru_personale_id_ab":"5266",
        //     "description":"richiesta emissione ..."}'
        // ;

        $data = $entity->toArray();
        $data['transitions'] = json_decode('[{"label":"Attiva","value":"self_transition","transitions":{}},{"label":"Richiesta emissione","value":"richiestaemissione","transitions":{}}]');   
        $data['assignments'] = [
            ["v_ie_ru_personale_id_ab"=>"39842"]
        ];
        $data['unitaorganizzativa_uo'] = "005680";
        $data["respons_v_ie_ru_personale_id_ab"] = "5266";
        $data['tipo_emissione'] = 'FATTURA_ELETTRONICA';
        $data['description'] = "richiesta emissione ...";

        //richiesta di emissione        
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace($data);

        $scad = $service->updateRichiestaEmissioneStep($request);

        //1) la scadenza deve essere nello stato di inrichiestaemissione
        $this->assertNotNull($scad);    
        $this->assertEquals('inemissione', $scad->state);    

        $scad->delete();
        $scad->usertasks()->delete();       
        $scad->convenzione->usertasks()->delete(); 
        $repo->delete($scad->convenzione_id);
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testEmissionePagamento
    public function testEmissionePagamento(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);

        $scad = new Scadenza;                
                
        $user = User::where('email','enrico.oliva@uniurb.it')->first();      
        $this->actingAs($user);
        //preparazione dati
        $data = ScadenzaTest::getArrayScadenza();        
        $data['convenzione_id']= ConvenzioneData::getOrCreateDefaultConvenzione($user)->id;
        $scad->fill($data);     
        //stato iniziale                 
        $scad->state = 'attivo';   
        $res = $scad->save();   

        //forzo passaggio a inemissione        
        $scad->data_emisrichiesta =  Carbon::now()->format(config('unidem.date_format'));
        $scad->workflow_apply('richiestaemissione', $scad->getWorkflowName());        
        $scad->save();

        //json di emissione con allegato 
        $datarequest = json_decode('{"attachment1":{"attachmenttype_codice":"NOTA_DEBITO",
            "filevalue":null,"doc":{"oggetto":"UniConv sottoscrizione Lettera di trasmissione completamento",
                "num_prot":"2019-UNURCLE-0008593","nrecord":"000822917-UNURCLE-071938c8-ef70-46b3-bd82-fb3edf8bdf2d","tipo":"partenza","anno":"2019","data_prot":"14-05-2019"}
            },"data_fattura": "14-05-2019", "num_fattura":"78917"}',true);        
        $datarequest['id'] = $scad->id;
        $datarequest['convenzione_id'] = $data['convenzione_id'];
        
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace($datarequest);

        $scad = $service->updateEmissioneStep($request);
        $scad = $service->updateModificaEmissioneStep($request);

        //controllare il nuovo stato della scadenza 
        $this->assertNotNull($scad);    
        $this->assertEquals('inpagamento',$scad->state);

        //verifico la creazione di un nuovo task stato aperto model
        $task= UserTask::where('model_id',$scad->id)
                        ->where('model_type',Scadenza::class)
                        ->where('state','aperto')
                        ->where('workflow_place','inpagamento')->first();

        $this->assertNotNull($task);            

        $datarequest = json_decode('{"prelievo":"PRE_10","note":"chiudo","num_ordincasso": "8282","data_ordincasso": "18-05-2019"}',true);        
        $datarequest['id'] = $scad->id;
        $datarequest['convenzione_id'] = $data['convenzione_id'];
        
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->replace($datarequest);

        $scad = $service->updatePagamentoStep($request);

        $this->assertNotNull($scad);    
        $this->assertEquals($scad->num_ordincasso,"8282");           
        
        //verifico la creazione di un nuovo task stato aperto model
        $task= UserTask::where('model_id',$scad->id)
                ->where('model_type',Scadenza::class)
                ->where('state','completato')
                ->where('workflow_place','inpagamento')->first();

        $this->assertNotNull($task); 

        $scad->delete();
        $scad->usertasks()->delete();
        $scad->convenzione->usertasks()->delete();
        $repo->delete($scad->convenzione_id);
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testReadNotifications
    // public function testReadNotifications(){
    //     $scad = Scadenza::with(['usertasks'=> function($query){            
    //         $query->where('workflow_place','inemissione');
    //     }])->where('state','inemissione')->first();
    //     $this->assertNotNull( $scad ); 
    //     $arr = $scad->toArray();

    //     //$arr = Scadenza::with(['usertasks'])->where('scadenze.state','inemissione')->join("usertasks","usertasks.workflow_place","=","scadenze.state")->first();

    //     //$arr = $scad->toArray();

    //     //$notify = $user->notifications()->where('type','App\Notifications\RichiestaEmissione')->whereJsonContains('data->model_id',$scad->id)->get(); 
    //     //$notify = DB::table('notifications')->where(DB::raw('JSON_EXTRACT(`notifications.data`, "$.model_id")'), '=', $scad->id)->get();
    //     //$notify =  DB::table('notifications')->where('data',$scad->id)->get();
    //     //$arr = $notify->toArray();

    // }

}