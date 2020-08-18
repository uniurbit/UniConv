<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Role;
use App\User;
use App\Permission;
use App\UserTask;
use App\Convenzione;
use JWTAuth;
use Faker\Generator as Faker;

class UserTaskTest extends TestCase
{
    use WithoutMiddleware;

    public static function getArrayUserTask($id){
        return [
            'unitaorganizzativa_uo' => '005363',
            'owner_user_id' => $id,
            'subject' => 'Validazione UserTask',
            'respons_v_ie_ru_personale_id_ab' => 1804,
            'assignments' => [            
                ['v_ie_ru_personale_id_ab'=> 1644],
            ],
            'workflow_place' =>  Convenzione::INAPPROVAZIONE, 
            'workflow_transition' => Convenzione::STORE_VALIDAZIONE,                   
            'state' => 'aperto',
        ];
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testUserTask
    public function testUserTask(){      
                       
        $user = User::where('email','test.admin@uniurb.it')->first();      
        //$this->actingAs($user);
      
        $usertask = new UserTask;
        $conv  = Convenzione::where('id','>',0)->first();
        
        $data = UserTaskTest::getArrayUserTask($user->id);
        $usertask->fill($data);          
        $usertask->model()->associate($conv);            
        $res = $usertask->save();
                    
        //array_push($data['assignments'],['v_ie_ru_personale_id_ab' => $data['respons_v_ie_ru_personale_id_ab'], 'cd_tipo_posizorg' => 'RESP_UFF']);            
        $usertask->assignments()->createMany($data['assignments']);
    
        $task = UserTask::with(['assignments.personale'])->where('subject','Validazione UserTask')->first();
        
        $result = $task->toArray();
        $this->assertEquals($data['subject'], $result['subject']);
        $this->assertGreaterThanOrEqual(1, count($result['assignments']));        
        $this->assertGreaterThanOrEqual(1, count($result['assignments'][0]['personale']));        

        $usertask->delete();
    }
}