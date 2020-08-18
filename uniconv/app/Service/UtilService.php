<?php

namespace App\Service;

use App\FindParameter;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\QueryBuilder;

class UtilService {

    public static function alldata($modelinstance, Request $request, $findparam){

        $findparam->limit = 1000;
        $findparam->page = null;
        
        $paginator = UtilService::query($modelinstance, $request, $findparam);
        $collection = collect($paginator->items());
       
        $page = 1;
        $total = $paginator->total();

        while($collection->count() < $total) {            
            $page = $page+1;

            $findparam->page = $page;
            
            $p = UtilService::query($modelinstance, $request, $findparam);   
            $collection = $collection->concat($p->items());
        }

        return $collection;
    }

    private static function query($modelinstance, Request $request, $findparam){        
        return (new QueryBuilder($modelinstance, $request, $findparam))->build()->paginate();        
    }
    
}