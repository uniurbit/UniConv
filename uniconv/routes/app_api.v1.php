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

//auth:api https://github.com/tymondesigns/jwt-auth/blob/develop/docs/quick-start.md

//aggiornare la documentazione
//php artisan api:update

Route::group(['middleware' => ['cors','auth:api','log','role:super-admin','check'],  'namespace'=>'Api\V1'], function () {

    Route::get('mappingruoli', 'MappingRuoloController@index');
    Route::get('mappingruoli/{id}', 'MappingRuoloController@show');
    Route::post('mappingruoli/query', 'MappingRuoloController@query'); 
    Route::post('mappingruoli', 'MappingRuoloController@store');
    Route::put('mappingruoli/{id}', 'MappingRuoloController@update');
    Route::delete('mappingruoli/{id}', 'MappingRuoloController@delete');

    Route::get('users/roles','UserController@roles');
    Route::get('users/permissions','UserController@permissions');
    Route::resource('users', 'UserController');
    Route::post('users/query', 'UserController@query'); 

    Route::get('roles', 'RoleController@index');
    Route::get('roles/{id}', 'RoleController@show');
    Route::post('roles/query', 'RoleController@query'); 
    Route::post('roles', 'RoleController@store');
    Route::put('roles/{id}', 'RoleController@update');
    Route::delete('roles/{id}', 'RoleController@delete');

    Route::get('permissions', 'PermissionController@index');
    Route::get('permissions/{id}', 'PermissionController@show');
    Route::post('permissions/query', 'PermissionController@query'); 
    Route::post('permissions', 'PermissionController@store');
    Route::put('permissions/{id}', 'PermissionController@update');
    Route::delete('permissions/{id}', 'PermissionController@delete');
    
});


Route::group(['middleware' => ['cors','auth:api','log','check'], 'namespace'=>'Api\V1'],function(){
    //convenzioni
    Route::get('convenzioni/generapdf/{id}','ConvenzioneController@generatePDF');
    Route::post('convenzioni/generapdf','ConvenzioneController@generatePostPDF');
    Route::post('convenzioni/pdf','ConvenzioneController@uploadPDF');
    Route::post('convenzioni/query','ConvenzioneController@query');
    Route::post('convenzioni/export','ConvenzioneController@export');
    Route::get('convenzioni/pagamenti/','ConvenzioneController@pagamenti');
    Route::get('convenzioni/attachmenttypes/','ConvenzioneController@attachemnttypes');
    Route::get('convenzioni/classificazioni/','ConvenzioneController@classificazioni');    
    Route::get('convenzioni/uffici/{id}','ConvenzioneController@uffici');    
    Route::get('convenzioni/personaleufficio/{id}','ConvenzioneController@personaleUfficio');    
    Route::get('convenzioni/{id}/actions','ConvenzioneController@nextPossibleActions');    
    Route::get('convenzioni/{id}/aziende','ConvenzioneController@getAziende');    
    Route::get('convenzioni/getminimal/{id}','ConvenzioneController@getminimal');  
    Route::get('convenzioni/gettitulusdocumenturl/{id}','ConvenzioneController@getTitulusDocumentURL');      
    Route::post('convenzioni/annullaconvenzione','ConvenzioneController@annullaConvenzione');


    Route::post('convenzioni/createschematipo','ConvenzioneController@createSchemaTipo');
    Route::post('convenzioni/validationstep','ConvenzioneController@updateValidationStep');
    Route::post('convenzioni/sottoscrizionestep','ConvenzioneController@updateSottoscrizioneStep');
    Route::post('convenzioni/complsottoscrizionestep','ConvenzioneController@updateComplSottoscrizioneStep');  
    Route::post('convenzioni/bollorepertoriazionestep','ConvenzioneController@updateBolloRepertoriazioneStep');    
    Route::post('convenzioni/richiestaemissionestep','ConvenzioneController@updateRichiestaEmissioneStep');    
    Route::post('convenzioni/inviorichiestapagamentostep','ConvenzioneController@updateInvioRichiestaPagamentoStep');    
    Route::post('convenzioni/emissionestep','ConvenzioneController@updateEmissioneStep');    
    Route::post('convenzioni/pagamentostep','ConvenzioneController@updatePagamentoStep');

    Route::post('convenzioni/registrazionesottoscrizione','ConvenzioneController@registrazioneSottoscrizione');
    Route::post('convenzioni/cancellazionesottoscrizione','ConvenzioneController@cancellazioneSottoscrizione');
    Route::post('convenzioni/registrazionecomplsottoscrizione','ConvenzioneController@registrazioneComplSottoscrizione');
    Route::post('convenzioni/registrazionebollorepertoriazione','ConvenzioneController@registrazioneBolloRepertoriazione');
        
    
    
    Route::resource('convenzioni','ConvenzioneController');
    
    Route::post('convenzioni/uploadFile','AttachmentController@uploadfile');
    Route::delete('convenzioni/uploadFile/{id}','AttachmentController@deletefile');

    Route::get('attachments/download/{id}','AttachmentController@download');
    
    //notifiche 
    Route::get('notifications','NotificationController@index');    
    Route::get('notifications/{id}','NotificationController@show');    
    Route::post('notifications/query','NotificationController@query');

    //usertasks
    Route::get('usertask/users/{id}/tasks','UserTaskController@filterByUser');    
    Route::get('usertask/users/{id}/office/tasks','UserTaskController@filterByUfficio');
    Route::get('usertask/tasks','UserTaskController@index');
    Route::get('usertask/{id}/actions','UserTaskController@nextPossibleActions');
    Route::get('usertask/create','UserTaskController@create');
    Route::get('usertask/convenzioni/{convId}/tasks','UserTaskController@filterByConvenzione');
    Route::post('usertask/query','UserTaskController@query');
    Route::put('usertask/{id}','UserTaskController@update');
    Route::post('usertask','UserTaskController@store');
    Route::get('usertask/{id}','UserTaskController@show');

    //PersonaInterna
    Route::post('personeinterne/query','PersonaInternaController@query');

    //StrutturaInterna
    Route::post('struttureinterne/query','StrutturaInternaController@query');
    Route::get('struttureinterne/{id}','StrutturaInternaController@getminimal');

    //StrutturaEsterna
    Route::post('struttureesterne/query','StrutturaEsternaController@query');
    Route::get('struttureesterne/{id}','StrutturaEsternaController@getminimal');

    //Documenti
    Route::post('documenti/query','DocumentoController@query');
    Route::get('documenti/{id}','DocumentoController@getminimal');

    //Documenti repertoriati
    Route::post('repertori/query','RepertorioController@query');
    Route::get('repertori/{id}','RepertorioController@getminimal');


    //aziende
    Route::post('aziende/query','AziendaController@query');
    Route::get('aziende/{id}','AziendaController@show');
    Route::get('aziende/indirizzo/{id}','AziendaController@getIndirizzoResidenza');

    //unità organizzativa
    Route::post('unitaorganizzative/query','UnitaOrganizzativaController@query');
    Route::get('unitaorganizzative/{id}','UnitaOrganizzativaController@getminimal');

    //logattivita
    Route::post('logattivita/query','LogActivityController@query');

    //Route::resource('dipartimenti','DipartimentoController');
    Route::get('dipartimenti/','DipartimentoController@index');
    Route::get('dipartimenti/user/','DipartimentoController@getDipartimentiByUser');    
    Route::get('dipartimenti/{id}','DipartimentoController@show');
    Route::post('dipartimenti/query','DipartimentoController@query');
    Route::get('dipartimenti/docenti/{codice}','DipartimentoController@getDocentiByDipartimento');
    Route::get('dipartimenti/direttore/{codice}','DipartimentoController@getDirettoreByDipartimento');
    

    Route::get('comuni/{codice}','LocalitaController@getComuneById');
    //comuni?prov=pu restituisce la lista di comuni filtrati per provincia
    Route::get('comuni','LocalitaController@getComuni');
    
    Route::get('provincie','LocalitaController@getProvincie');
    Route::get('provincie/{codice}','LocalitaController@getProvinciaById');



    Route::get('tipopagamenti', 'TipoPagamentoController@index');
    Route::get('tipopagamenti/{id}', 'TipoPagamentoController@show');
    Route::post('tipopagamenti/query', 'TipoPagamentoController@query'); 
    Route::post('tipopagamenti', 'TipoPagamentoController@store');
    Route::put('tipopagamenti/{id}', 'TipoPagamentoController@update');
    Route::delete('tipopagamenti/{id}', 'TipoPagamentoController@delete');

    Route::get('classificazioni', 'ClassificazioneController@index');
    Route::get('classificazioni/{id}', 'ClassificazioneController@show');
    Route::post('classificazioni/query', 'ClassificazioneController@query'); 
    Route::post('classificazioni', 'ClassificazioneController@store');
    Route::put('classificazioni/{id}', 'ClassificazioneController@update');
    Route::delete('classificazioni/{id}', 'ClassificazioneController@delete');

    Route::get('aziendeloc', 'AziendaLocController@index');
    Route::get('aziendeloc/{id}', 'AziendaLocController@show');
    Route::post('aziendeloc/query', 'AziendaLocController@query'); 
    Route::post('aziendeloc', 'AziendaLocController@store');
    Route::put('aziendeloc/{id}', 'AziendaLocController@update');
    Route::delete('aziendeloc/{id}', 'AziendaLocController@delete');
    
    Route::get('scadenze', 'ScadenzaController@index');
    Route::get('scadenze/{id}', 'ScadenzaController@show');
    Route::post('scadenze/query', 'ScadenzaController@query'); 
    Route::post('scadenze', 'ScadenzaController@store');
    Route::put('scadenze/{id}', 'ScadenzaController@update');
    Route::delete('scadenze/{id}', 'ScadenzaController@delete');
    Route::get('scadenze/{id}/actions','ScadenzaController@nextPossibleActions'); 

    Route::get('mappinguffici', 'MappingUfficioController@index');
    Route::get('mappinguffici/{id}', 'MappingUfficioController@show');
    Route::post('mappinguffici/query', 'MappingUfficioController@query'); 
    Route::post('mappinguffici', 'MappingUfficioController@store');
    Route::put('mappinguffici/{id}', 'MappingUfficioController@update');
    Route::delete('mappinguffici/{id}', 'MappingUfficioController@delete');

});
