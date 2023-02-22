import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { FormGroup, FormArray } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Page } from 'src/app/shared/lookup/page';
import { PermissionService } from '../../permission.service';
import { BaseResearchComponent } from 'src/app/shared';
import { UserTaskService } from '../../usertask.service';
import { TranslateService } from '@ngx-translate/core';
import { MyTranslatePipe } from 'src/app/shared/pipe/custom.translatepipe';
import { TaskComponent } from './task.component';

@Component({
  selector: 'app-tasks',
  templateUrl: '../../../shared/base-component/base-research.component.html',
})

//ng g c submission/components/permissions -s true --spec false -t true


export class TasksComponent extends BaseResearchComponent {
  translate: MyTranslatePipe = null;
  isLoading = false;

  @ViewChild('stateattivita') stateattivita: TemplateRef<any>;
  
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
          // {
          //   key: 'unitaorganizzativa_uo',
          //   type: 'string',
          //   templateOptions: {
          //     label: 'Ufficio',
          //     required: true,
          //     column: { cellTemplate: 'valuecolumn'}
          //   }
          // },
          {
            key: 'unitaorganizzativa_uo',
            type: 'select',
            templateOptions: {
              label: 'Ufficio',
              required: true,
              options: this.service.getOffices('tutti'),
              valueProp: 'uo',
              labelProp: 'descr',
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
                { value:'emissione', label:'Emissione documento di debito'},
                { value:'registrazionepagamento', label:'In pagamento'},
                { value:'richiestaemissione', label:'Richiesta emissione del documento di debito'},
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
            key: 'model_id',
            type: 'number',
            hideExpression: false,
            templateOptions: {
              label: 'Codice collegato',
              disabled: true,
              column: { width: 10, cellTemplate: 'valuecolumn'}
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


  resultMetadata: FormlyFieldConfig[];

  

  constructor(protected service: UserTaskService, protected router: Router, protected route: ActivatedRoute, translateService: TranslateService)  {    
    super(router,route)
    this.routeAbsolutePath = 'home/tasks';    
    this.prefix = 'usertasks';
    this.translate = new MyTranslatePipe(translateService);
    
    this.initRule();
  }

  ngOnInit(): void {
    let page = new Page(25);
    let result = null;

    if (this.getStorageResult()){
      result = JSON.parse(this.getStorageResult());
      this.init = true;
      page.totalElements = result.total; // data.to;
      page.pageNumber = result.current_page - 1;
      page.size = result.per_page;
    }
    this.resultMetadata =[
      {
        key: 'data',
        type: 'datatablelookup',
        wrappers: ['accordion'],      
        templateOptions: {
          label: 'Attività',   
          columnMode: 'force',
          scrollbarH: true,     
          page: new Page(25),
          hidetoolbar: true,      
          onDblclickRow: (event) => this.onDblclickRow(event),
          onSetPage: (pageInfo) => this.onSetPageWithInit(pageInfo),      
          columns: [
            { name: '#', prop: 'id',  with: 60, maxWidth: 60, cellTemplate: this.apri },
            { name: 'Ufficio', prop: 'unitaorganizzativa_uo',  pipe: this.translate, width: 100, maxWidth: 120},
            { name: 'Oggetto', prop: 'subject', with: 100, maxWidth: 150, },
            { name: 'Stato', prop: 'state', cellTemplate: this.stateattivita, width: 120, maxWidth: 120 },
            { name: 'Azione', prop: 'workflow_transition',  pipe: this.translate, with: 100},
            { name: 'Descrizione', prop: 'modelwith.descrizione',  width: 400, maxWidth: 700},
            { name: 'Tipo collegato', prop:'model_type',  width: 120 },
            { name: 'Codice collegato', prop: 'model_id', width: 100, maxWidth: 100 },
            { name: 'Data creazione', prop: 'created_at', type: 'date', with: 100, maxWidth: 150 },
          ]  
        },
        fieldArray: {
          //fieldGroupClassName: 'row',   
          fieldGroup: [] //this.fieldsRow,
        }
      }
    ];

    if (result) {
      this.setResult(result);
    }
  }

  rowSelection(row) {
    this.setStorageResult();      
    if (row.workflow_place) {
      if (row.state == 'aperto' || row.workflow_transition == 'emissione'){
        const path = TaskComponent.pathEsecuzioneTask(row.workflow_transition,row.workflow_place);
        if (path != null){
          this.router.navigate([path, row.model_id]);
        }
      }
    }
  }

  onDblclickRow(event) {
    this.setStorageResult();    
    if (event.type === 'dblclick') {
      if (event.row.id) {
        this.router.navigate([this.routeAbsolutePath, event.row.id]);
      }
    }
  }

  isDisabledApri(row){
    return !(row.workflow_place && (row.state == 'aperto' || row.workflow_transition == 'emissione'))
  }

}
