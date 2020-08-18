<?php

namespace App\Http\Controllers\Api\V1;

use App\TipoPagamento;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TipoPagamentoController extends Controller
{
    public function index()
    {
        return TipoPagamento::all();
    }
 
    public function show($id)
    {
        return TipoPagamento::find($id);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'codice'=>'required|unique:tipopagamenti|max:6',
            'descrizione' =>'required',
            ]
        );

        $tp = TipoPagamento::create($request->all());

        return response()->json($tp, 201);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'codice'=>'required|max:6',
            'descrizione' =>'required',
            ]
        );
        
        $tp = TipoPagamento::findOrFail($id);
        $tp->update($request->all());

        return $tp;
    }

    public function delete(Request $request, $id)
    {
        //se è utilizzato non si può cancellare
        $tp = TipoPagamento::findOrFail($id);
        $tp->delete();

        return $tp;
    }
    
    public function query(Request $request){ 
        
        $queryBuilder = new QueryBuilder(new TipoPagamento, $request);
                
        return $queryBuilder->build()->paginate();       

        //costruzione della query
    }
}