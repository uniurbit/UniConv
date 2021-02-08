<?php

namespace App\Exports;

use App\Scadenza;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Service\UtilService;

class ScadenzeExport implements FromCollection, WithMapping
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
        return UtilService::alldata(new Scadenza, $this->request, $this->findparam);        
    }

    public function map($scad): array
    {      
        return [
            $scad->id,
            $scad->data_tranche,
            $scad->dovuto_tranche,
            $scad->convenzione->descrizione_titolo,
            $scad->convenzione->listAziendaDenominazione(),
            $scad->state
        ];
    }
}
