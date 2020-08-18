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
  
  isLoading = false;
    
 
  fieldsRow = null;
  resultMetadata = null;  
    

  constructor(protected service: AziendaLocService, router: Router, route: ActivatedRoute, protected translateService: TranslateService)  {    
    super(router,route);    
    this.routeAbsolutePath = 'home/aziendeloc'                    

    this.fieldsRow = [         
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
          onSetPage: (pageInfo) => this.onSetPage(pageInfo),      
          columns: [
            { name: 'Nome', prop: 'nome' },          
            { name: 'Cognome', prop: 'cognome' },
            { name: 'Denominazione', prop: 'denominazione'},
            { name: this.translateService.instant('AZIENDALOC.PEC'), prop: 'pec_email' },          
          ]
        },
      }
    ];  
  }
 

}
