import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { FormGroup, FormArray } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Page } from 'src/app/shared/lookup/page';
import { PermissionService } from '../../permission.service';
import { BaseResearchComponent } from 'src/app/shared';
import { UserTaskService } from '../../usertask.service';

@Component({
  selector: 'app-tasks',
  templateUrl: '../../../shared/base-component/base-research.component.html',
})

//ng g c submission/components/permissions -s true --spec false -t true


export class TasksComponent extends BaseResearchComponent {
  
  isLoading = false;
  fieldsRow: FormlyFieldConfig[] = [
          {
            key: 'id',
            type: 'number',
            hideExpression: false,
            templateOptions: {
              label: 'Id',
              disabled: true,
              column: { width: 10, cellTemplate: 'valuecolumn'}
            }
          },
          {
            key: 'unitaorganizzativa_uo',
            type: 'string',
            templateOptions: {
              label: 'Ufficio',
              required: true,
              column: { cellTemplate: 'valuecolumn'}
            }
          },
          {
            key: 'subject',
            type: 'string',
            templateOptions: {
              label: 'Oggetto',
              required: true,
              column: { cellTemplate: 'valuecolumn'}
            }
          },
          {
            key: 'state',
            type: 'string',
            templateOptions: {
              label: 'Stato',
              required: true,
              column: { cellTemplate: 'valuecolumn'}
            }
          },
          {
            key: 'workflow_transition',
            type:'select',
            templateOptions: {
              label: 'Azione',
              options: [
                { value:'approvato', label:'Sottoscrizione'},
                { value:'store_validazione', label:'Approvazione'},
                { value:'repertorio', label:'Apposizione bollo e repertoriazione'},
                { value:'firma_da_direttore2', label:'Firma da UniUrb'},
                { value:'firma_da_controparte2', label:'Firma della controparte'},            
                { value:'emissione', label:'Richiesta di emissione'},
                { value:'registrazionepagamento', label:'In pagamento'},
              ]
            }
            
          },
          {
            key: 'model_type',
            type: 'select',
            templateOptions: {
              label: 'Tipo collegato',
              options: [
                { label: 'Convenzione', value: 'App\\Convenzione' },
                { label: 'Scadenza', value: 'App\\Scadenza' },
              ],
              required: true,
              column: { cellTemplate: 'valuecolumn'}
            }
          },
          {
            key: 'created_at',
            type: 'date',
            className: "col-md-6",
            templateOptions: {
              label: 'Data creazione',
              required: true,
            },        
          },  

        ];


  resultMetadata = [
    {
      key: 'data',
      type: 'datatablelookup',
      wrappers: ['accordion'],      
      templateOptions: {
        label: 'Attività',   
        columnMode: 'force',
        scrollbarH: false,        
        page: new Page(25),
        hidetoolbar: true,      
        onDblclickRow: (event) => this.onDblclickRow(event),
        onSetPage: (pageInfo) => this.onSetPage(pageInfo),      
      },
      fieldArray: {
        fieldGroupClassName: 'row',   
        fieldGroup: this.fieldsRow,
      }
    }
  ];

  

  constructor(protected service: UserTaskService, protected router: Router, protected route: ActivatedRoute,)  {    
    super(router,route)
    this.routeAbsolutePath = 'home/tasks';    
  }

}
