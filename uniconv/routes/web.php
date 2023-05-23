<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

    Route::get('/loginSaml', function(){
    
        if(\Auth::guest())
        {
            return \Saml2::login("/");
        }
    })->name('loginSaml');


    Route::group([
        'prefix' => config('saml2_settings.routesPrefix'),
        'middleware' => config('saml2_settings.routesMiddleware'),
    ], function () {
        Route::get('metadata', function(Request $request){ 
            $url = URL::route('saml2_metadata', env('IDP_ENV_ID', 'local'));
            return redirect($url);
        });
     
        Route::post('/acs', array(
            'as' => 'saml_acs',
            'uses' => 'Saml2AuthController@acs',
        ));
    });