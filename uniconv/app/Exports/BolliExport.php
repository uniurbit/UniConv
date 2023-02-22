<?php

namespace App\Exports;

use App\Convenzione;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Service\UtilService;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class BolliExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithColumnFormatting, WithEvents
{

    use Exportable;

    public function __construct($request, $findparam)
    {
        $this->request = $request;
        $this->findparam = $findparam;
    }
      

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:N1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            'G' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            'I' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            'J' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return UtilService::alldata(new Convenzione, $this->request, $this->findparam);        
    }

    public function map($conv): array
    {      
        return [        
          
            __('global.'.$conv->ambito).' '.($conv->convenzione_type ? __('global.convenzione_type.'.$conv->convenzione_type) : ''),
            $conv->data_stipula,
            $conv->listAziendaDenominazione(),

            $conv->bolli ? ($conv->bolli_atti_prov() ? $conv->bolli_atti_prov()->num_righe : '') : '',
            $conv->bolli ? ($conv->bolli_atti_prov() ? $conv->bolli_atti_prov()->num_bolli : '') : '',
            $conv->bolli ? ($conv->bolli_atti_prov() ? $conv->bolli_atti_prov()->tipobollo()->first()->importo : '') : '',
            $conv->bolli ? ($conv->bolli_atti_prov() ? $conv->bolli_atti_prov()->totale() : '') : '',
         
            $conv->bolli ? ($conv->bolli_allegato() ? $conv->bolli_allegato()->num_bolli : '') : '',
            $conv->bolli ? ($conv->bolli_allegato() ? $conv->bolli_allegato()->tipobollo()->first()->importo : '') : '',
            $conv->bolli ? ($conv->bolli_allegato() ? $conv->bolli_allegato()->totale() : '') : '',
            

            $conv->id,
            $conv->descrizione_titolo,
            __('global.'.$conv->dipartimemto_cd_dip),
            $conv->num_rep,
                        
        ];
    }

    public function headings(): array
    {
        return [            
            'Tipologia',
            'Data contratto',
            'Controparte',

            'Numero righe contratto',
            'Numero bolli virtuali su contratto',
            'Importo unitario bollo virtuale',
            'Importo totale su contratto',
           
            'Numero bolli virtuali su allegati',
            'Importo unitario bollo virtuale su allegati',
            'Importo totale bollo virtuale su allegati',

            'ID UniConv',
            'Titolo',
            'Dipartimento',
            'Numero repertorio'
        ];
    }
}
