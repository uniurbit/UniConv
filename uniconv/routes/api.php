<?php

use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


// App v1 API
Route::group([
    'middleware' => ['api', 'api.version:1'],  
    'prefix'     => 'v1',
  ], function ($router) {
    require base_path('routes/app_api.v1.php');
});
  
Route::get('/loginSaml', function(Request $request){
    
    if(\Auth::guest())
    {       
        //login($returnTo = null, array $parameters = array(), $forceAuthn = false, $isPassive = false, $stay = false, $setNameIdPolicy = true)
        //return  \Saml2::login('http://localhost:4200/',array(),false,false,true);   
        if (\App::environment('local')) {
            if (\Request::ip() == "192.168.5.135" || \Request::ip() == "192.168.5.137" || \Request::ip() == "127.0.0.1" ) {                
                $redirect = $request->query('redirect');
                return  \Saml2::login($redirect ? $redirect : 'home');                  
            } else 
                return  abort(404);
        }
        $redirect = $request->query('redirect');
        return  \Saml2::login($redirect ? $redirect : 'home');                            
    }
});

   
   
Route::group([
    'middleware' => ['api','cors'],
    'prefix' => 'auth',
    'namespace'=>'Api'
], function ($router) {   
    Route::post('refreshtoken', 'AuthController@refresh');
    Route::get('logout', 'AuthController@logout');   
    //Route::post('me', 'AuthController@me');         
});

Route::group([
    'middleware' => ['cors','auth:api','log','role:super-admin'],
    'prefix' => 'auth',
    'namespace'=>'Api'
], function ($router) {
    Route::post('cambiautente', 'AuthController@cambiautente'); 
});
