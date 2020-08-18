<?php

namespace App\Http\Controllers\Api\V1;

use App\AziendaLoc;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AziendaLocController extends Controller
{
    public function index()
    {
        return AziendaLoc::all();
    }
 
    public function show($id)
    {
        return AziendaLoc::find($id);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nome'=>'required',
            'cognome'=>'required',
            'denominazione' =>'required|unique:aziende',
            ]
        );

        $az = AziendaLoc::create($request->all());

        return response()->json($az, 201);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'id'=>'required',            
            ]
        );
        
        $az = AziendaLoc::findOrFail($id);
        $az->update($request->all());

        return $az;
    }

    public function delete(Request $request, $id)
    {
        //se è utilizzato non si può cancellare
        $az = AziendaLoc::findOrFail($id);
        $az->delete();

        return $az;
    }
    
    public function query(Request $request){ 
        
        $queryBuilder = new QueryBuilder(new AziendaLoc, $request);
                
        return $queryBuilder->build()->paginate();       

        //costruzione della query
    }
}