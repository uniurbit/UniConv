import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { FormGroup, FormArray } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Page } from 'src/app/shared/lookup/page';
import { PermissionService } from '../../permission.service';
import { BaseResearchComponent } from 'src/app/shared/base-component/base-research.component';
import { TipoPagamentoService } from '../../tipopagamento.service';



@Component({
  selector: 'app-tipopagamenti', 
  templateUrl: '../../../shared/base-component/base-research.component.html',
})

//ng g c submission/components/permissions -s true --spec false -t true
export class TipoPagamentiComponent extends BaseResearchComponent {
  
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
            key: 'codice',
            type: 'string',
            templateOptions: {
              label: 'Codice',
              required: true,
              column: { cellTemplate: 'valuecolumn'}
            }
          },
          {
            key: 'descrizione',
            type: 'string',
            templateOptions: {
              label: 'Descrizione',
              required: true,
              column: { cellTemplate: 'valuecolumn'}
            }
          }
        ];

 
  resultMetadata = [
    {
      key: 'data',
      type: 'datatablelookup',
      wrappers: ['accordion'],      
      templateOptions: {
        label: 'Tipo pagamenti',   
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

  constructor(protected service: TipoPagamentoService, router: Router, route: ActivatedRoute,)  {    
    super(router,route);    
    this.routeAbsolutePath = 'home/tipopagamenti'     
  }
 

}
