<?php

namespace App\Exports;

use App\Convenzione;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Service\UtilService;

class ConvenzioniExport implements FromCollection, WithMapping, WithHeadings
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

    public function map($conv): array
    {      
        return [        
            $conv->id,
            $conv->descrizione_titolo,
            $conv->schematipotipo == 'schematipo' ? 'no' : 'si',
            __('global.'.$conv->dipartimemto_cd_dip),
            $conv->resp_scientifico,
            __('global.'.$conv->ambito),
            $conv->durata,        
            //$conv->prestazioni,
            $conv->convenzione_type ? __('global.convenzione_type.'.$conv->convenzione_type) : '',
            $conv->tipopagamento ? $conv->tipopagamento->descrizione : '',
            $conv->corrispettivo,                    
            __('global.rinnovo_type.'.$conv->rinnovo_type),   
            //$conv->importo,
            
            __('global.'.$conv->current_place),
            $conv->unitaorganizzativa_uo, //cd_csa dell'utente che crea la convenzione
           
            $conv->stipula_type ? __('global.stipula_type.'.$conv->stipula_type) : '',
            $conv->data_sottoscrizione,
            
            $conv->titolario_classificazione,
            $conv->oggetto_fascicolo,

            //$conv->nrecord,
            $conv->numero,
            $conv->num_rep,
       
            $conv->data_inizio_conv,
            $conv->data_fine_conv,
            $conv->listAziendaDenominazione(),
            is_null($conv->bollo_virtuale) ? '' : $conv->bollo_virtuale == true ? 'si' : 'no',
            $conv->bolli ? ($conv->bolli_atti_prov() ? $conv->bolli_atti_prov()->num_bolli : '') : '',
            $conv->bolli ? ($conv->bolli_allegato() ? $conv->bolli_allegato()->num_bolli : '') : '',
            //$conv->convenzione_from,            
            
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Titolo',
            'Approvazione organi centrali',
            'Dipartimento',
            'Resp. Scientifico',
            'Ambito',
            'Durata in mesi',
            'Tipo convenzione',
            'Modalità di pagamento',
            'Corrispettivo',   
            'Tipo rinnovo',         

            'Stato',
            'Unità organizzativa',
            
            'Iter di stipula',

            'Data sottoscrizione',

            'Classificazione',
            'Oggetto del fascicolo',
            'Numero fascicolo',

            'Numero repertorio',

         

            'Data inizio',
            'Data fine',

            'Aziende o enti',

            'Bollo virtuale',
            'Numero bolli virt. atti e provvedimenti',
            'Numero bolli virt. allegati tecnici'

        ];
    }
}
