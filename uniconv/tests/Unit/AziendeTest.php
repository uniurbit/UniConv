<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Role;
use App\User;
use App\Permission;
use App\AziendaLoc;
use App\Convenzione;
use JWTAuth;
use Faker\Generator as Faker;

class AziendaLocTest extends TestCase
{
    use WithoutMiddleware;

    public static function getArrayAziendaLoc(){
        return [
            'nome' => 'nomnereferente',
            'cognome' => 'cognomereferente',
            'denominazione' => 'azienda uniconv',
            'contatto_email' => 'enrico.oliva@uniurb.it',
            'pec_email' => 'enrico.oliva@uniurb.it',
            'cod_fisc' => '1234567891011',
            //'indirizzo1' => 'test', 
            'stato' => 'Italiano', 
            'comune' => 'Urbino', 
            'cap' => '61029', 
            'phone_number' => '0722448811', 
            'cell_phone' => '331331331',                       
        ];
    }

    public static function getOrCreateAziendaLoc($descr){

        $res = AziendaLoc::where('denominazione',$descr)->first();
        if (!$res){        
            $data = AziendaLocTest::getArrayAziendaLoc();
            $data['denominazione'] = $descr;
            $az = new AziendaLoc;
            $az->fill($data);                         
            $res = $az->save();        
            return $az;
        }
        return $res;
    }


    //./vendor/bin/phpunit  --testsuite Unit --filter testAziendaLoc
    public function testAziendaLoc(){      
                       
        $user = User::where('email','test.admin@uniurb.it')->first();      
        //$this->actingAs($user);
      
        $az = new AziendaLoc;        
        
        $data = AziendaLocTest::getArrayAziendaLoc();
        $az->fill($data);                         
        $res = $az->save();
                            
        $result = AziendaLoc::find($az->id)->toArray();
        $this->assertEquals($data['nome'], $result['nome']);    
        $this->assertEquals($data['cognome'], $result['cognome']);    
        $this->assertEquals($data['denominazione'], $result['denominazione']);    
        $this->assertEquals($data['cod_fisc'], $result['cod_fisc']);    

        $az->delete();
    }

     //./vendor/bin/phpunit  --testsuite Unit --filter testApiAziendaLoc
     public function testApiAziendaLoc(){
        $user = User::where('email','test.admin@uniurb.it')->first();     
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', 'api/v1/aziendeloc/0', [], $headers)
            ->assertStatus(200);
        
        $response = $this->json('POST', 'api/v1/aziendeloc/query', [
            'rules'=> [                                                
            ]
        ], $headers)
            ->assertStatus(200);      
    }

}