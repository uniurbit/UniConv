<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Role;
use App\User;
use App\Permission;
use JWTAuth;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

class UserTest extends TestCase
{
    use WithoutMiddleware;

    //./vendor/bin/phpunit  --testsuite Unit --filter testApiRoles_All
    public function testApiRoles_All(){
        $user = User::where('email','test.admin@uniurb.it')->first();
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        //lista ruoli
        $response = $this->json('GET', 'api/v1/users/roles', [], $headers)
            ->assertStatus(200);  
            
        echo($response->getContent());
    }

     //./vendor/bin/phpunit  --testsuite Unit --filter testApiPermssions_All
     public function testApiPermssions_All(){
        $user = User::where('email','test.admin@uniurb.it')->first();
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        //lista ruoli
        $response = $this->json('GET', 'api/v1/users/permissions', [], $headers)
            ->assertStatus(200);  
            
        echo($response->getContent());
    }

    //./vendor/bin/phpunit  --testsuite Unit --filter testApiRoles
    public function testApiRoles(){
        $user = User::where('email','test.admin@uniurb.it')->first();
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        //lista ruoli
        $response = $this->json('GET', 'api/v1/roles', [], $headers)
            ->assertStatus(200);        
        
        //crea
        $role = factory(Role::class)->make();
        $data = $role->toArray();        
        $data['permissions'] = \App\Permission::all()->take(2);

        $response = $this->json('POST', 'api/v1/roles', $data, $headers)
            ->assertStatus(201)
            ->assertJson([
                'name' => $role->name,
                'permissions' => [ [ 'name' => $data['permissions'][0]['name'] ] ]
            ]);            
        
        //aggiorna
        $data['id'] = $response->original->id;
        $data['permissions']->shift();
        $response = $this->json('PUT', 'api/v1/roles/'.$data['id'], $data, $headers)
            ->assertStatus(200)
            ->assertJson([
                'name' => $role->name,
                'permissions' => [ [ 'name' => $data['permissions'][0]['name'] ] ]
            ]);   

        //echo($response->getContent()); 
        
        //cancella
        $response = $this->json('DELETE', 'api/v1/roles/'.$data['id'], $data, $headers)
            ->assertStatus(200);

        echo($response->getContent());
    }

   //./vendor/bin/phpunit  --testsuite Unit --filter testApiPermissions
    public function testApiPermissions(){
        $user = User::where('email','test.admin@uniurb.it')->first();
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', 'api/v1/permissions', [], $headers)
            ->assertStatus(200)
            ->assertJson(
                [
                    ['name' => 'create convenzioni' ],
                    ['name' => 'update convenzioni' ]
                ] 
           );

        //crea
        $permission = factory(Permission::class)->make();
        $data = $permission->toArray();                
        
        //crea
        $response = $this->json('POST', 'api/v1/permissions', $data, $headers)
            ->assertStatus(201)
            ->assertJson([
                'name' => $permission->name,        
            ]);             
        echo($response->getContent());

        //aggiorna
        $data['id'] = $response->original->id;
        $data['name'] = 'test permission';
        $response = $this->json('PUT', 'api/v1/permissions/'.$data['id'], $data, $headers)
            ->assertStatus(200)
            ->assertJson([
                'name' =>  $data['name'],        
            ]);  
        echo($response->getContent());

        //cancella
        $response = $this->json('DELETE', 'api/v1/permissions/'.$data['id'], $data, $headers)
            ->assertStatus(200);
        echo($response->getContent());
    }

     //./vendor/bin/phpunit  --testsuite Unit --filter testReadEmailFromRespons
     public function testReadEmailFromRespons(){

        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $this->actingAs($user);

        $email =  $user->responsabile()->email; 
        
        $this->assertNotNull($email); 
     }


     //./vendor/bin/phpunit  --testsuite Unit --filter testCreationPermissionForUI
     public function testCreationPermissionForUI(){        
        $user = User::where('email','enrico.oliva@uniurb.it')->first();
        $user->assignRole('super-admin');

        $this->actingAs($user);

        $permssions = $user->getAllPermissions()->pluck('name')->filter(function ($value, $key) {
            return Str::startsWith ($value,'ui');
        });
        
        $this->assertNotNull($permssions); 

        $claim = $user->getJWTCustomClaims();

        $this->assertNotNull($claim['roles']); 
        $this->assertNotNull($claim['permissions']); 
     }

}