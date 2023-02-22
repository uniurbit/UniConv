<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Repositories\ConvenzioneRepository;
use App\Convenzione;
use App\Provincia;
use App\User;
use App\Dipartimento;
use App\Personale;
use App\FunzioniIncarico;
use App\PersonaleResponsOrg;
use JWTAuth;
use App\Http\Controllers\V1\SoapController;
use Artisaninweb\SoapWrapper\SoapWrapper;
use \App\Http\Controllers\Api\V1\LocalitaController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Service\LoginService;

//php artisan serve --host=192.168.5.137 --port=80

//./vendor/bin/phpunit  --testsuite Unit
//./vendor/bin/phpunit  --testsuite Unit --filter testConnection
//./vendor/bin/phpunit  ./tests/Unit/UgovTest 
class UgovTest extends TestCase
{
    use WithoutMiddleware;

    public function testConnection()
    {
        $conn = DB::connection('oracle');
        $this->assertNotNull($conn);
        //$this->assertEquals('oracle',$conn->config['driver']);
    }


    /** @test */
    public function testReadUgovTableComune()
    {
        $contr = new LocalitaController();
        $result = $contr->getComuneById('A122');

        $this->assertNotNull($result);
        //A122	01-JAN-00	02-FEB-22	ALASSIO	009001	03-JUL-00		CINECA
        //var_dump($result);
        $this->assertEquals('01-JAN-00',strtoupper($result->data_in->format('d-M-y')));        
        $this->assertEquals('02-FEB-22',strtoupper($result->data_fin->format('d-M-y')));
        
        $this->assertEquals('Alassio',$result->descr);
        $this->assertEquals('009001',$result->istat);
        $this->assertEquals('CINECA',$result->operatore);
        
    }

    public function testReadComuniFromProvincia(){        
        
        $prov = Provincia::find('PU');        

        //per debug
        //$res = $prov->comuni();
        //$p = $res->getQualifiedParentKeyName();
        //$q = $res->toSql();
        // foreach ($prov->comuni as $comune) {
        //     echo $comune->descr;
        // }

        //QUERY equivalente utilizzando query builder 
        // $comuni = DB::connection('oracle')
        //     ->table('PROVINCE')
        //     ->join('COMUNE_PROV', 'PROVINCE.COD', '=', 'COMUNE_PROV.PROVINCIA')
        //     ->join('COMUNE', 'COMUNE.COD', '=', 'COMUNE_PROV.COD')
        //     ->select('PROVINCE.*', 'COMUNE.COD', 'COMUNE.DESCR')
        //     ->where('PROVINCE.COD','=','PU')
        //     ->get();

        $this->assertNotNull($prov);
        $this->assertGreaterThanOrEqual(72,$prov->comuni->count());
     
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testReadOnlyActiveDipartimenti
    public function testReadOnlyActiveDipartimenti(){

        $dipartimenti = Dipartimento::Dipartimenti()->get();

        echo($dipartimenti);

        $this->assertGreaterThanOrEqual(6, $dipartimenti->count());
        $this->assertTrue($dipartimenti->contains(function ($value, $key) {            
            return $value->nome_breve == "Dip.to Scienze della Com., Stu.Um. e Int. (DISCUI)";
        }));    
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testDirettoreDipartimento
    public function testDirettoreDipartimento()
    {
        $dip = Dipartimento::Dipartimenti()->where('cd_dip','1')->first();
        $direttore = $dip->direttoreDipartimento()->first();
        echo($direttore);
        $this->assertEquals('LICIA',$direttore->nome);
        $this->assertEquals('CALIFANO',$direttore->cognome);

    }

    public function testPaginateQueryPage1OnComuni(){
        $param = new \App\FindParameter(['order_by'=>'COD,asc','page'=>'100']);   
        $param->setRulesAttribute = [
            ['field'=>'DATA_FIN',  
            'operator'=>'>=',
            'value'=>Carbon::now() ], 
            ['field'=>'DESCR',  
            'operator'=>'LIKE',
            'value'=>'%P'],            
        ];
        
        $result = \App\Comune::paginateQuery($param);
        $this->assertGreaterThanOrEqual(100, $result->count());
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testPersonale
    public function testPersonale(){
        $pers = Personale::find(39842);
        $this->assertEquals('enrico.oliva@uniurb.it',$pers->email);
        
        $pers = Personale::findByEmail('enrico.oliva@uniurb.it')->first();
        $this->assertEquals('018087',$pers->matricola);
        $this->assertEquals('ND',$pers->cd_ruolo);
        $this->assertEquals(39842,$pers->id_ab);
        
        $this->assertEquals('Personale TA',$pers->ruolo->descr);
        $this->assertTrue($pers->ruolo->isPta());
        
        $this->assertEquals('SEV',$pers->unita->tipo);

        //Dipartimento di Studi Umanistici (DISTUM)
        $dip = Dipartimento::find(21);
        $this->assertGreaterThan(90, $dip->personale->count());

        $this->assertGreaterThan(20, $dip->docenti->count());

        // foreach ($dip->personale as $persona) {
        //     if ($persona->isPta())
        //         echo $persona->email;
        // }

        $service = new LoginService();
        $this->assertEquals('super-admin',$service->findUserRoleAndData('enrico.oliva@uniurb.it')['ruoli'][0]);
        //Dipartimento di Studi Umanistici (DISTUM) docente
        $this->assertEquals('op_docente',$service->findUserRoleAndData('giuseppe.azzara@uniurb.it')['ruoli'][0]);
        $this->assertEquals('viewer',$service->findUserRoleAndData('gino.lelli@uniurb.it')['ruoli'][0]);
                
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testPersonaleResponsOrg
    public function testPersonaleResponsOrg(){
        $pers = PersonaleResponsOrg::find(39842);
        $this->assertEquals(PersonaleResponsOrg::PERS_C,$pers->cd_tipo_posizorg);
        
    }


}