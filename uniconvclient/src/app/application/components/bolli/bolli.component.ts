import { Component, OnInit, ViewChild } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { TranslateService } from '@ngx-translate/core';
import { BaseResearchComponent } from 'src/app/shared';
import { Page } from 'src/app/shared/lookup/page';
import { MycurrencyPipe } from 'src/app/shared/pipe/custom.currencypipe';
import { MyFlattenPipe } from 'src/app/shared/pipe/custom.flattenpipe';
import { MyTranslatePipe } from 'src/app/shared/pipe/custom.translatepipe';
import { ApplicationService } from '../../application.service';
import { BolloService } from '../../bollo.service';

@Component({
  selector: 'app-bolli',
  templateUrl: '../../../shared/base-component/base-research.component.html',
  styles: []
})

//ng g c application/components/bolli -s true --spec false -t true
export class BolliComponent extends BaseResearchComponent {
 
  currency = new MycurrencyPipe();
  flatten = new MyFlattenPipe('');
  translate: MyTranslatePipe;
  
  resultMetadata: FormlyFieldConfig[];  



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
      key: 'descrizione_titolo',
      type: 'input',
      className: "col-12",
      templateOptions: {
        label: 'Descrizione Titolo',
        required: true,
      },
    },
    {
      key: 'anno',
      type: 'select',
      templateOptions: {
        label: 'Anno di stipula',
        valueProp: 'value',
        labelProp: 'label',
        options: [
          {value: '2019', label: '2019'},
          {value: '2020', label: '2020'},
          {value: '2021', label: '2021'},
          {value: '2022', label: '2022'},          
        ]
      }
    },
    {
      key: 'dipartimemto_cd_dip',
      type: 'select',
      className: "col-md-6",
      templateOptions: {
        options: this.appservice.getDipartimenti(),
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
      key: 'bollo_virtuale',
      type: 'select',
      className: "col-md-4",
      defaultValue: true,    
      templateOptions: {
        disabled: true,
        options: [
          { label: 'Sì', value: true },       
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
  
  constructor(protected service: BolloService, protected appservice: ApplicationService, router: Router, route: ActivatedRoute, private translateService: TranslateService)  {    
    super(router,route);    
    this.routeAbsolutePath = 'home/convdetails'    
    
    this.enableNew= false;
    this.enabledExport = true;
    this.isLoading = false;
    
    this.translate = new MyTranslatePipe(translateService);
    this.prefix = 'bolli';

    this.initRule();

    if (this.rules == null){
      this.rules = [
        {field: "bollo_virtuale", operator: "=", value: true, fixcondition: true},
        {field: "anno", operator: "=", value: null, fixcondition: true},
      ];
    }

  }
 
  protected getRules(model) {
    if (model.rules) {
      let rulestmp = JSON.parse(JSON.stringify(model.rules)) as (Array<any>);
      const ruleAnno = rulestmp.find(x => x.field == 'anno');   
      if (ruleAnno) {
        rulestmp.splice(rulestmp.indexOf(ruleAnno), 1);   
        rulestmp  = rulestmp.concat([        
          { field: "data_stipula", operator: "<=", value: '31-12-'+ruleAnno.value, type: "date" },
          { field: "data_stipula",  operator: ">=", value: '1-1-'+ruleAnno.value, type: "date" },            
        ]);
      }
      return rulestmp;
    }
    return model.rules;
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
            selected: [],                        
            page: new Page(25),       
            onDblclickRow: (event) => this.onDblclickRow(event),
            onSetPage: (pageInfo) => this.onSetPageWithInit(pageInfo),               
            columns: [
              { name: '#', prop: 'id', width: 60, maxWidth: 70},
              { name: 'Descrizione Titolo', prop: 'descrizione_titolo', minWidth: 400, maxWidth: 450},
              { name: 'Azienda o ente', prop:'aziende', pipe: this.flatten, minWidth: 300 },
              { name: 'Dipartimento', prop: 'dipartimemto_cd_dip', pipe: this.translate, width: 135, maxWidth: 135 },
              { name: 'Responsabile scientifico', prop: 'resp_scientifico', width: 150},
              { name: 'Tipo convenzione', prop: 'convenzione_type', pipe: this.translate },
              { name: 'Ambito', prop: 'ambito', pipe: this.translate },
              { name: 'Modalità di pagamento', prop: 'tipopagamento.descrizione', width: 200 },   
              { 
                name: 'Corrispettivo',  prop: 'corrispettivo', 
                cellClass: "text-right", width:'200', pipe: this.currency              
              },
              { name: 'Data stipula', prop: 'data_stipula', type: 'date' },  
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
