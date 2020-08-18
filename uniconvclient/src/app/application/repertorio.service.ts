
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable, of, throwError } from 'rxjs';
import { map, catchError, tap } from 'rxjs/operators';
import { ControlBase, TextboxControl, DropdownControl, DateControl, MessageService, ServiceQuery, ServiceEntity, IQueryMetadata } from '../shared';
import { ArrayControl } from '../shared/dynamic-form/control-array';
import { FormlyFieldConfig, FormlyTemplateOptions } from '@ngx-formly/core';
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
export class RepertorioService extends BaseService implements IQueryMetadata{
  getQueryMetadata(): FormlyFieldConfig[] {
    return [
      {
        key: 'doc_repertorionumero',//'/doc/@num_prot',
        type: 'string',
        templateOptions: {
          type: 'index',
          label: 'Numero di repertorio',   
          required: true, 
          column: { width: 30, cellTemplate: 'valuecolumn'}
        }
      },      
      {
        key: 'doc_anno',
        type: 'string',
        templateOptions: {
          type: 'index',
          label: 'Anno',
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      },    
      {
        key: 'doc_oggetto',
        type: 'string',
        templateOptions: {
          label: 'Oggetto',
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      },        
      {
        key: 'doc_classifcod',
        type: 'select',
        templateOptions: {
          label: 'Classificazione',
          options: [
               { label: 'III/13 - Progetti e finanziamenti', value: '03/13' },
               { label: 'III/14 - Accordi per la didattica e per la ricerca', value: '03/14' },
               { label: 'III/19 - Attività per conto terzi', value: '03/19' },
            ],
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      },        
    ];
  }

  getMetadata(): FormlyFieldConfig[] {
    return [
      {
        key: 'oggetto',
        type: 'string',
        templateOptions: {
          label: 'Oggetto',
          required: true,
          column: { cellTemplate: 'valuecolumn' }
        }
      },
      
      {
        key: 'repertorio.numero',
        type: 'string',
        templateOptions: {
          label: 'Numero di repertorio',
          required: true,
          column: { cellTemplate: 'valuecolumn' }
        }
      },
      {
        key: 'data_prot',
        type: 'string',
        templateOptions: {
          label: 'Data registrazione',
          required: true,
          column: { cellTemplate: 'valuecolumn' }
        }
      },      
      {
        key: 'repertorio.value',
        type: 'string',
        hideExpression: false,
        templateOptions: {
          label: 'Repertorio',
          disabled: true,
          column: { width: 10, cellTemplate: 'valuecolumn' }
        }
      },
    ];

  }

  constructor(protected http: HttpClient, public messageService: MessageService, public confirmationDialogService: ConfirmationDialogService) {
     super(http,messageService,confirmationDialogService);
     this.basePath = 'repertori';     
  }

}
