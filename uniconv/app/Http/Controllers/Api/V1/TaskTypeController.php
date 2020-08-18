<?php

namespace App\Http\Controllers\Api\V1;

use App\TaskType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaskTypeController extends Controller
{
    public function index()
    {
        return TaskType::all();
    }
 
    public function show($id)
    {
        return TaskType::find($id);
    }

    public function store(Request $request)
    {
        return TaskType::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $permission = TaskType::findOrFail($id);
        $permission->update($request->all());

        return $permission;
    }

    public function delete(Request $request, $id)
    {
        $permission = TaskType::findOrFail($id);
        $permission->delete();

        return $permission;
    }
    
    public function query(Request $request){

        $queryBuilder = new QueryBuilder(new TaskType, $request);
                
        return $queryBuilder->build()->paginate();       

        //costruzione della query

    }
}