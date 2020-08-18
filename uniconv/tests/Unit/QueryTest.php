<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
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
use  App\Http\Controllers\Api\V1\ConvenzioneController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Service\UtilService;

class QueryTest extends TestCase
{

    use WithoutMiddleware;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter test_query_user
    public function test_query_user(){
        $response = $this->json('post','api/v1/users/query',[
            'rules'=> [
                [
                    'field' => 'email',
                    'operator' => '=',
                    'value' => 'test.admin@uniurb.it'
                ]                                
            ]
        ]);

        $response->assertStatus(200);
        
    }


    
    // ./vendor/bin/phpunit  --testsuite Unit --filter test_exportCSV
    public function test_exportCSV(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);

        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);

        $controller  = new ConvenzioneController($repo,$service);

        //costruzione query 
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');        
        $rules = json_decode('{"rules":[],"limit":1000,"sessionId":null,"page":null}',true);
        $request->json()->replace($rules);

        $paginator = $controller->query($request);

        $collection = collect($paginator->items());
        $page = 1;
        $total = $paginator->total();
        while($collection->count() < $total) {            
            $page = $page+1;
            $rules = json_decode('{"rules":[],"limit":1000,"sessionId":null,"page":'.$page.'}',true);
            $request->json()->replace($rules);    

            $p = $controller->query($request);        
            $collection = $collection->concat($p->items());
        }
        $this->assertGreaterThanOrEqual($collection->count(), $total);
    }

     // ./vendor/bin/phpunit  --testsuite Unit --filter test_exportCSV1
     public function test_exportCSV1(){
        $repo = new ConvenzioneRepository($this->app);          
        $service = new ConvenzioneService($repo);

        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);
        $controller  = new ConvenzioneController($repo,$service);

        //costruzione query 
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');        
        $rules = json_decode('{"rules":[],"limit":25,"sessionId":null,"page":null}',true);
        $request->json()->replace($rules);

        //controllo numero di record restituiti 
        $collection = UtilService::alldata(new Convenzione, $request, $controller->queryparameter($request));
        $total = $controller->query($request)->total();
        $this->assertGreaterThanOrEqual($collection->count(), $total);

        //esportazione csv
        $response = $controller->export($request);
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
      
     }
}
