import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { FormGroup, FormArray } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Page } from 'src/app/shared/lookup/page';
import { PermissionService } from '../../permission.service';
import { BaseResearchComponent } from 'src/app/shared/base-component/base-research.component';
import { ScadenzaService } from '../../scadenza.service';
import { MycurrencyPipe } from 'src/app/shared/pipe/custom.currencypipe';
import { TranslateService } from '@ngx-translate/core';
import { MyTranslatePipe } from 'src/app/shared/pipe/custom.translatepipe';
import { MyFlattenPipe } from 'src/app/shared/pipe/custom.flattenpipe';
import { ApplicationService } from '../../application.service';

@Component({
  selector: 'app-scadenze', 
  templateUrl: '../../../shared/base-component/base-research.component.html',
})

//ng g c application/components/classificazioni -s true --spec false -t true
export class ScadenzeComponent extends BaseResearchComponent {
  
  isLoading = false;
  currency = new MycurrencyPipe();
  translate: MyTranslatePipe;

  fieldsRow: FormlyFieldConfig[] = [
          {
            key: 'id',
            type: 'number',                              
            templateOptions: {
              label: 'Codice',
              disabled: true,              
              column: { width: 50, cellTemplate: 'valuecolumn'}
            }
          },
          {
            key: 'data_tranche',
            type: 'date',
            templateOptions: {
              label: 'Tranche prevista',
              required: true,          
            }
          },
          {
            key: 'dovuto_tranche',
            type: 'number',
            templateOptions: {
              label: 'Importo',
            }
          },
          {
            key: 'convenzione.id',
            type: 'external',
            templateOptions: {
              label: 'Convenzione',
              type: 'string',
              required: true,
              entityName: 'application',
              entityLabel: 'Convenzione',
              codeProp: 'id',
              descriptionProp: 'descrizione_titolo',
              isLoading: false,               
            }   
          },
          {
            key: 'convenzione.descrizione_titolo',
            type: 'string',
            templateOptions: {
              label: 'Titolo convenzione',
              required: true,              
            }
          },
          {
            key: 'convenzione.dipartimemto_cd_dip',
            type: 'select',
            templateOptions: {
              options: this.appService.getDipartimenti(),
              valueProp: 'cd_dip',
              labelProp: 'nome_breve',
              label: 'Dipartimento'
            },
          },
          {
            key: 'aziende.denominazione',
            type: 'string',
            templateOptions: {
              label: 'Denominazione azienda',
              required: true,              
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
            }
          }

        ];

  resultMetadata: FormlyFieldConfig[];
  flatten = new MyFlattenPipe('');

  constructor(protected service: ScadenzaService, protected appService: ApplicationService, router: Router, route: ActivatedRoute, translateService: TranslateService)  {    
    super(router,route);    
    this.routeAbsolutePath = 'home/scadenze'     
    this.translate = new MyTranslatePipe(translateService);
    this.prefix = 'scadenze';
    this.enabledExport = true;
    
    this.initRule();

    if (this.rules == null){
    }
    
  }

  ngOnInit() {
    let page = new Page(25);
    let result = null;

    if (this.getStorageResult()){
      result = JSON.parse(this.getStorageResult());
      this.init = true;
      page.totalElements = result.total; // data.to;
      page.pageNumber = result.current_page - 1;
      page.size = result.per_page;
    }

    this.resultMetadata = [
      {
        key: 'data',
        type: 'datatablelookup',
        wrappers: ['accordion'],      
        templateOptions: {
          label: 'Scadenze',   
          columnMode: 'force',
          scrollbarH: false,        
          page: new Page(25),
          rowHeight: 50,  
          hidetoolbar: true,      
          onDblclickRow: (event) => this.onDblclickRow(event),
          onSetPage: (pageInfo) => this.onSetPageWithInit(pageInfo),    
          columns: [
            { name: '', prop: 'id',  with: 60, maxWidth: 60, cellTemplate: this.apri },
            { name: '#', prop: 'id', width: 50, maxWidth: 80},
            { name: 'Tranche prevista', prop: 'data_tranche', with: 100, maxWidth: 150, type: 'date'},
            { name: 'Importo', prop: 'dovuto_tranche', pipe: this.currency, width: 100, maxWidth: 100 },
            { name: 'Titolo convenzione', prop: 'convenzione.descrizione_titolo', with: 400},
            { name: 'Azienda o ente', prop:'aziende', pipe: this.flatten, minWidth: 200 },
            { name: 'Dipartimento', prop: 'convenzione.dipartimemto_cd_dip', pipe: this.translate, width: 135, maxWidth: 135 },
            { name: 'Stato', prop: 'state', pipe: this.translate, with: 100, maxWidth: 150 },
          ]  
        },
        fieldArray: {
          fieldGroup: []
        }
      }
    ];

    if (result) {
      this.setResult(result);
    }
  }

  onDblclickRow(event) {    
    if (event.type === 'dblclick') {
      this.router.navigate(['home/scadenzeview', event.row.id]);
    }
  }

}
