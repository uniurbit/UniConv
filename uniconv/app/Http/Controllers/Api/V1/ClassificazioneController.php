<?php

namespace App\Http\Controllers\Api\V1;

use App\Classificazione;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClassificazioneController extends Controller
{
    public function index()
    {
        return Classificazione::all();
    }
 
    public function show($id)
    {
        return Classificazione::find($id);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'codice'=>'required|unique:classificazioni|max:20',
            'descrizione' =>'required',
            ]
        );

        $tp = Classificazione::create($request->all());

        return response()->json($tp, 201);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'codice'=>'required|max:20',
            'descrizione' =>'required',
            ]
        );
        
        $ent = Classificazione::findOrFail($id);
        $ent->update($request->all());

        return $tp;
    }

    public function delete(Request $request, $id)
    {
        //se è utilizzato non si può cancellare
        $ent = Classificazione::findOrFail($id);
        $ent->forceDelete();

        return $ent;
    }
    
    public function query(Request $request){ 
        
        $queryBuilder = new QueryBuilder(new Classificazione, $request);
                
        return $queryBuilder->build()->paginate();       

        //costruzione della query
    }
}