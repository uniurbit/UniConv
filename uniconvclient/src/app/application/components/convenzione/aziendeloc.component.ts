import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Page } from 'src/app/shared/lookup/page';
import { BaseResearchComponent } from 'src/app/shared/base-component/base-research.component';
import { AziendaLocService } from '../../aziendaloc.service';
import { MyTranslatePipe } from 'src/app/shared/pipe/custom.translatepipe';
import { TranslateService, LangChangeEvent } from '@ngx-translate/core';

@Component({
  selector: 'app-aziendeloc', 
  templateUrl: '../../../shared/base-component/base-research.component.html',
})

//ng g c submission/components/permissions -s true --spec false -t true
export class AziendeLocComponent extends BaseResearchComponent {
       
  resultMetadata: FormlyFieldConfig[];    
  fieldsRow = [         
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
        translate: true,
        label: this.translateService.instant('AZIENDALOC.PEC'),               
        required: true,
        column: { cellTemplate: 'valuecolumn'  }
      }
    },
  ];

  constructor(protected service: AziendaLocService, router: Router, route: ActivatedRoute, protected translateService: TranslateService)  {    
    super(router,route);    
    this.routeAbsolutePath = 'home/aziendeloc'                    
    this.prefix = 'aziendeloc';
    this.initRule();      
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
          label: 'Aziende',   
          columnMode: 'force',
          scrollbarH: false,        
          page: new Page(25),
          hidetoolbar: true,      
          onDblclickRow: (event) => this.onDblclickRow(event),
          onSetPage: (pageInfo) => this.onSetPageWithInit(pageInfo),      
          columns: [
            { name: 'Nome', prop: 'nome' },          
            { name: 'Cognome', prop: 'cognome' },
            { name: 'Denominazione', prop: 'denominazione'},
            { name: this.translateService.instant('AZIENDALOC.PEC'), prop: 'pec_email' },          
          ]
        },
      }      
    ];

    if (result){
      this.setResult(result);
    }
    
  }

}
