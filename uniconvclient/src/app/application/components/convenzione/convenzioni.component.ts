import { Component, OnInit, Injector, ViewChild, TemplateRef } from '@angular/core';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { FormGroup } from '@angular/forms';
import { ServiceQuery, BaseResearchComponent } from '../../../shared';
import ControlUtils from '../../../shared/dynamic-form/control-utils';
import { ActivatedRoute, Router } from '@angular/router';
import { SSL_OP_DONT_INSERT_EMPTY_FRAGMENTS } from 'constants';
import { Page } from '../../../shared/lookup/page';
import { encode, decode } from 'base64-arraybuffer';
import { MycurrencyPipe } from 'src/app/shared/pipe/custom.currencypipe';
import { TranslatePipe, TranslateService } from '@ngx-translate/core';
import { MyTranslatePipe } from 'src/app/shared/pipe/custom.translatepipe';
import { ApplicationService } from '../../application.service';
import { MyFlattenPipe } from 'src/app/shared/pipe/custom.flattenpipe';



@Component({
  selector: 'app-convenzionis',
  templateUrl: '../../../shared/base-component/base-research.component.html',
  styles: []
})

//ng g c submission/components/submission/submissions -s true --spec false -t true --flat true

export class ConvenzioniComponent extends BaseResearchComponent {

  @ViewChild('detailRow', { static: false }) detailRow: TemplateRef<any>;
  
  researchMetadata: FormlyFieldConfig[];

  fieldsRow: FormlyFieldConfig[] = [
    {
      key: 'id',
      type: 'input',
      hideExpression: true,
      templateOptions: {
        label: 'Codice',
        disabled: true
      },
    },
    {
      key: 'user_id',
      type: 'external',
      wrappers: [],
      templateOptions: {
        label: 'Codice utente',
        type: 'string',
        entityName: 'user',
        entityLabel: 'Utenti',
        codeProp: 'id',
        descriptionProp: 'name',
      },
      modelOptions: {
        updateOn: 'blur',
      }, 
    },


    {
      key: 'descrizione_titolo',
      type: 'input',
      className: "col-12",
      templateOptions: {
        label: 'Descrizione Titolo',
        required: true,
      },
    },
    {
      key: 'dipartimemto_cd_dip',
      type: 'select',
      className: "col-md-6",
      templateOptions: {
        options: this.service.getDipartimenti(),
        valueProp: 'cd_dip',
        labelProp: 'nome_breve',
        label: 'Dipartimento',
        required: true
      },
    },
    {
      key: 'resp_scientifico',
      type: 'input',
      className: "col-md-6",
      templateOptions: {
        label: 'Responsabile scientifico',
        required: true, 
              
      }
    },
    {
      key: 'aziende.id',
      type: 'external',
      className: "col-md-6",
      templateOptions: {
        label: 'Azienda',
        type: 'string',
        entityName: 'aziendaLoc',
        entityLabel: 'Aziende',
        codeProp: 'id',
        descriptionProp: 'denominazione',
      },
    },
    {
      key: 'aziende.denominazione',
      type: 'input',
      className: "col-md-6",
      templateOptions: {
        label: 'Denominazione azienda o ente',
        required: true
      }
    },
    {
      key: 'convenzione_type',
      type: 'select',
      className: "col-md-4",
      defaultValue: 'TO',
      templateOptions: {
        options: [
          { label: 'Titolo oneroso', value: 'TO' },
          { label: 'Titolo gratuito', value: 'TG' },
        ],
        label: 'Tipo convenzione',
        required: true,
      },
    },
    {
      key: 'ambito',
      type: 'select',
      className: "col-md-4",
      templateOptions: {
        options: [
          { label: 'Istituzionale', value: 'istituzionale' },
          { label: 'Commerciale', value: 'commerciale' },
        ],
        label: 'Ambito',
        required: true,
      },
    },
    {
      key: 'tipopagamenti_codice',
      type: 'select',
      className: "col-md-4",
      templateOptions: {
        options: this.service.getPagamenti(),
        valueProp: 'codice',
        labelProp: 'descrizione',
        label: 'Modalità di pagamento',
        required: true
      }
    }, 
    {
      key: 'corrispettivo',
      type: 'input',
      className: "col-md-6",
      templateOptions: {
        label: 'Corrispettivo IVA esclusa se applicabile',
        required: true,
      },
    },
    {
      key: 'data_inizio_conv',
      type: 'date',
      className: "col-md-6",
      templateOptions: {
        label: 'Data inizio',
        required: true,
      },        
    },  
    {
      key: 'data_fine_conv',
      type: 'date',
      className: "col-md-6",
      templateOptions: {
        label: 'Data fine',
        required: true,
      },       
    },  
    {
      key: 'bollo_virtuale',
      type: 'select',
      className: "col-md-4",
      templateOptions: {
        options: [
          { label: 'Sì', value: true },
          { label: 'No', value: false },
        ],
        label: 'Bollo virtuale',
        required: true,
      },
    },    
    {
      key: 'current_place',
      type: 'select',
      className: "col-md-6",
      templateOptions: {
        options: [
          { value: 'proposta', label: 'Proposta' }, 
          { value: 'approvato', label: 'Approvata' }, 
          { value: 'inapprovazione', label: 'In approvazione' },                        
          { value: 'da_firmare_direttore', label: 'Stipula controparte' }, //Da controfirmare UniUrb
          { value: 'da_firmare_controparte2', label: 'Stipula UniUrb' },  //Da controfirmare controparte
          { value: 'firmato', label: 'Firmata' },  
          { value: 'repertoriato', label: 'Repertoriata' },            
        ],
        label: 'Stato',
        required: true,
      },
    }
  ];

  currency = new MycurrencyPipe();
  flatten = new MyFlattenPipe('');
  translate: MyTranslatePipe;
  
  resultMetadata: FormlyFieldConfig[];
  @ViewChild('tooltip', { static: true }) tooltipCellTemplate: TemplateRef<any>;

  constructor(protected service: ApplicationService, router: Router, route: ActivatedRoute, private translateService: TranslateService) {
    super(router, route);
    this.enableNew= false;
    this.enabledExport = true;
    this.isLoading = false;

    this.routeAbsolutePath = 'home/convdetails';
    this.translate = new MyTranslatePipe(translateService);
    this.prefix = 'convenzioni';

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

    this.resultMetadata =  [
      {
          key: 'data',
          type: 'datatablelookup',
          wrappers: ['accordion'],      
          templateOptions: {
            label: 'Convenzioni',   
            columnMode: 'force',
            headerHeight: 50,
            footerHeight: 50,
            rowHeight: 50,            
            scrollbarH: true,             
            hidetoolbar: true, 
            detailRow: this.detailRow,
            selected: [],                        
            page: new Page(25),       
            onDblclickRow: (event) => this.onDblclickRow(event),
            onSetPage: (pageInfo) => this.onSetPageWithInit(pageInfo),               
            columns: [
              { name: '', prop: 'id',  with: 60, maxWidth: 60, cellTemplate: this.apri },
              { name: '#', prop: 'id', width: 60, maxWidth: 70},
              { name: 'Descrizione Titolo', prop: 'descrizione_titolo', minWidth: 400, maxWidth: 450},
              { name: 'Azienda o ente', prop:'aziende', pipe: this.flatten, minWidth: 300 },
              { name: 'Dipartimento', prop: 'dipartimemto_cd_dip', cellTemplate: this.tooltipCellTemplate, 
                pipe: this.translate, width: 135, maxWidth: 135 },
              { name: 'Responsabile scientifico', prop: 'resp_scientifico', width: 150},
              { name: 'Tipo convenzione', prop: 'convenzione_type', pipe: this.translate },
              { name: 'Ambito', prop: 'ambito', pipe: this.translate },
              { name: 'Modalità di pagamento', prop: 'tipopagamento.descrizione', width: 200 },   
              { 
                name: 'Corrispettivo',  prop: 'corrispettivo', 
                cellClass: "text-right", width:'200', pipe: this.currency              
              },
              { name: 'Data inizio', prop: 'data_inizio_conv', type: 'date' },      
              { name: 'Data fine', prop: 'data_fine_conv', type: 'date' },                    
              { name: 'Stato', prop: 'current_place',  pipe: this.translate },                    
            ]                                
          },
          fieldArray: {
            fieldGroup: []
          }
        }];

      if (result) {
        this.setResult(result);
      }


  }

  
}
