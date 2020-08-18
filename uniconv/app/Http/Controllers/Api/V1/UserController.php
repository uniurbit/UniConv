<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\Http\Controllers\Controller;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use App\Service\LogActivityService;

class UserController extends Controller
{

    public function __construct() {
       // $this->middleware(['isAdmin']); //isAdmin middleware lets only users with a //specific permission permission to access these resources
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        //LogActivityService::addToLog('user index');

        $id = $request->get('userId');
        $query = $request->all();

        if ($id == null){            
            return User::all(); 
        }
        $user = User::with(['roles','permissions'])->where('id', $id)->first();
        return $user;		        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //LogActivityService::addToLog('user create');
        $roles = Role::get();
        return view('users.create', ['roles'=>$roles]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //LogActivityService::addToLog('user store');
         //Validate name, email and password fields
         $this->validate($request, [
            'name'=>'required|max:120',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:6|confirmed'
        ]);

        $user = User::create($request->only('email', 'name', 'password')); //Retrieving only the email and password data

        $roles = $request['roles']; //Retrieving the roles field
        //Checking if a role was selected
        if (isset($roles)) {

            foreach ($roles as $role) {
            $role_r = Role::where('id', '=', $role)->firstOrFail();            
            $user->assignRole($role_r); //Assigning role to user
            }
        }                
        return $user;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::with(['roles','permissions'])->where('id', $id)->first();
        return $user;
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //LogActivityService::addToLog('user update');
        $user = User::findOrFail($id); //Get role specified by id

        //Validate name, email and password fields  
        $this->validate($request, [
            'name'=>'required|max:120',
            'email'=>'required|email|unique:users,email,'.$id,
            'roles' => 'required'
            //'password'=>'required|min:6|confirmed'
        ]);
        $input = $request->only(['name', 'email', 'password', 'v_ie_ru_personale_id_ab','blocked_date']); //Retreive the name, email and password fields        
        $user->fill($input)->save();

        if($request->roles <> ''){
            $user->syncRoles(
                collect($request->roles)
                ->map(function ($item) {
                    return $item['name'];
                })                               
            );           
        }            
        
        if($request->permissions <> ''){
            $user->syncPermissions(
                collect($request->permissions)
                ->map(function ($item) {
                    return $item['name'];
                })                               
            );           
        }          


        return $user;
        
        // redirect()->route('users.index')
        //     ->with('flash_message',
        //      'User successfully edited.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //LogActivityService::addToLog('user delete');
         //Find a user with a given id and delete
         $user = User::findOrFail($id); 
         $user->delete();
 
        return response()->json(null, 204);
    }

    public function query(Request $request){

        //LogActivityService::addToLog('user query');

        $app = $request->json();
        $parameters = $request->json()->all();
        $parameters['includes'] = 'roles'; 
        $findparam =new \App\FindParameter($parameters);      
   
        $queryBuilder = new QueryBuilder(new User, $request, $findparam);
                        
        return $queryBuilder->build()->paginate();     

        //costruzione della query

    }

    public function roles(Request $request)
    {
        return \App\Role::get(['id','name']);
    }

    public function permissions(Request $request)
    {
        return \App\Permission::where('guard_name','api')->get(['id','name', 'guard_name']);
    }

}
