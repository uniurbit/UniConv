<?php

namespace App\Exports;

use App\AziendaLoc;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Service\UtilService;


class AziendeExport implements FromCollection, WithMapping
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
        return UtilService::alldata(new AziendaLoc, $this->request, $this->findparam);        
    }


    public function map($az): array
    {      
        return [
            $az->id,
            $az->nome,
            $az->cognome,
            $az->denominazione,            
            $az->pec_email,
            $az->cod_fisc,
            $az->part_iva,
            $az->indirizzo1,  
            $az->comune,
            $az->provincia,
            $az->cap         
        ];
    }
}
