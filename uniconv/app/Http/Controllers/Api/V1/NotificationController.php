<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Validator;
use App\Convenzione;
use Storage;
use Auth;
use Illuminate\Http\Request;

//php artisan make:controller NotificationController
class NotificationController extends Controller
{

    
    /**
     * Restituisce le notifiche non lette associate all'utente ha fatto login
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        
        return Auth::user()->unreadNotifications()->get();     
    }    
    
    /**
     * Restituisce la notifica identificata con id 
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //->markAsRead();
        return Auth::user()->notifications()->find($id);
    }
    
    public function markAsRead($id){
        $notification = Auth::user()->notifications()->find($id);
        return $notification->markAsRead();        
    }

    public function query(Request $request){        
        $page = $request->input('page',null);
        $perPage = $request->input('limit',15);
        
        return Auth::user()->unreadNotifications()->paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);
    }


}