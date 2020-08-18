
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable, of, throwError } from 'rxjs';
import { map, catchError, tap } from 'rxjs/operators';
import { ControlBase, TextboxControl, DropdownControl, DateControl, MessageService, ServiceQuery, ServiceEntity } from '../shared';
import { ArrayControl } from '../shared/dynamic-form/control-array';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { AppConstants } from '../app-constants';
import { Cacheable } from 'ngx-cacheable';
import { BaseService } from '../shared/base-service/base.service';
import { ConfirmationDialogService } from '../shared/confirmation-dialog/confirmation-dialog.service';


const httpOptions = {
  headers: new HttpHeaders({
    'Content-Type': 'application/json'
  })
};

@Injectable()
export class ScadenzaService extends BaseService {

  getMetadata(): FormlyFieldConfig[] {
    return [
      {
        key: 'id',
        type: 'number',
        hide: true,            
        templateOptions: {
          label: 'Id',
          disabled: true,
          column: { width: 10, cellTemplate: 'valuecolumn'}
        }
      },
      {
        key: 'data_tranche',
        type: 'string',
        templateOptions: {
          label: 'Tranche prevista',
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      },
      {
        key: 'dovuto_tranche',
        type: 'string',
        templateOptions: {
          label: 'Importo',
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      },
      {
        key: 'state',
        type: 'select',
        templateOptions: {
          label: 'Stato',         
          options: [
            { label: 'Attiva', value: 'attivo' },
            { label: 'In emissione', value: 'inemissione' },
            { label: 'In pagamento ', value: 'inpagamento' },
            { label: 'Pagata ', value: 'pagato' },
          ],
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      }
    ];

  }

  constructor(protected http: HttpClient, public messageService: MessageService,public confirmationDialogService: ConfirmationDialogService) {
     super(http,messageService,confirmationDialogService);
     this.basePath = 'scadenze';     
  }

}
