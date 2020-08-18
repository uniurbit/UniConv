<?php

namespace App\Exports;

use App\Convenzione;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Service\UtilService;

class ConvenzioniExport implements FromCollection
{

    use Exportable;

    public function __construct($request, $findparam)
    {
        $this->request = $request;
        $this->findparam = $findparam;
    }
      

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return UtilService::alldata(new Convenzione, $this->request, $this->findparam);        
    }
}
