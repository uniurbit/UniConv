
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
import { MyTranslatePipe } from '../shared/pipe/custom.translatepipe';


const httpOptions = {
  headers: new HttpHeaders({
    'Content-Type': 'application/json'
  })
};

@Injectable()
export class AziendaLocService extends BaseService {

  
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
        key: 'nome',
        type: 'input',
        templateOptions: {
          label: 'Nome',
          required: true,
          column: { cellTemplate: 'valuecolumn' }
        }
      },
      {
        key: 'cognome',
        type: 'string',
        templateOptions: {
          label: 'Cognome',
          required: true,
          column: { cellTemplate: 'valuecolumn' }
        }
      },
      {
        key: 'denominazione',
        type: 'string',
        templateOptions: {
          label: 'Denominazione',
          required: true,
          column: { cellTemplate: 'valuecolumn' }
        }
      },
      {
        key: 'pec_email',
        type: 'string',
        templateOptions: {                    
          label: 'Email', 
          required: true,
          column: { cellTemplate: 'valuecolumn' }
        }
      },
    ];

  }

  constructor(protected http: HttpClient, public messageService: MessageService, public confirmationDialogService: ConfirmationDialogService) {
     super(http,messageService,confirmationDialogService);
     //aziende locali al sistam uniconv
     this.basePath = 'aziendeloc';     
  }

}
