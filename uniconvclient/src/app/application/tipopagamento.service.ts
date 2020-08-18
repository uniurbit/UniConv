
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { ControlBase, TextboxControl, DropdownControl, DateControl, MessageService, ServiceQuery, ServiceEntity } from '../shared';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { BaseService } from '../shared/base-service/base.service';
import { ConfirmationDialogService } from '../shared/confirmation-dialog/confirmation-dialog.service';


const httpOptions = {
  headers: new HttpHeaders({
    'Content-Type': 'application/json'
  })
};

@Injectable()
export class TipoPagamentoService extends BaseService {

  getMetadata(): FormlyFieldConfig[] {
    return [
      {
        key: 'id',
        type: 'number',
        hideExpression: false,
        templateOptions: {
          label: 'Id',
          disabled: true,
          column: { width: 10, cellTemplate: 'valuecolumn' }
        }
      },
      {
        key: 'name',
        type: 'select',
        templateOptions: {
          label: 'Ruolo',
          required: true,
          column: { cellTemplate: 'valuecolumn' }
        }
      },
      {
        key: 'guard_name',
        type: 'string',
        templateOptions: {
          label: 'Nome',
          required: true,
          column: { cellTemplate: 'valuecolumn' }
        }
      },
    ];

  }

  constructor(protected http: HttpClient, public messageService: MessageService, public confirmationDialogService: ConfirmationDialogService) {
     super(http,messageService,confirmationDialogService);
     this.basePath = 'tipopagamenti';     
  }

}
