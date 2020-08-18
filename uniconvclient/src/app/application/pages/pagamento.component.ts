import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig, Field } from '@ngx-formly/core';
import { BaseEntityComponent } from 'src/app/shared';
import { ApplicationService } from '../application.service';
import { ActivatedRoute, Router } from '@angular/router';
import { encode, decode } from 'base64-arraybuffer';
import ControlUtils from 'src/app/shared/dynamic-form/control-utils';
import { FileDetector } from 'protractor';
import { FormlyFieldConfigCache } from '@ngx-formly/core/lib/components/formly.field.config';
import { takeUntil, startWith, tap, filter, map, distinct } from 'rxjs/operators';
import { ScadenzaService } from '../scadenza.service';
import {Location} from '@angular/common';

@Component({
  selector: 'app-pagamento',
  template: `
  <div class="container-fluid">
  <ngx-loading [show]="isLoading" [config]="{ backdropBorderRadius: '14px' }"></ngx-loading>
  <div class="btn-toolbar mb-4" role="toolbar">
  <div class="btn-group btn-group">        
    <button class="btn btn-outline-primary rounded-lg"  [disabled]="!form.valid || !form.dirty" (click)="onSubmit()" >              
      <span class="oi oi-arrow-top"></span>  
      <span class="ml-2">Aggiorna</span>              
    </button> 
    <button class="btn btn-outline-primary rounded-lg ml-1"  (click)="onValidate()" >              
    <span class="oi oi-flash"></span>  
    <span class="ml-2">Valida</span>              
  </button> 
  </div>
  </div>
  <h4 *ngIf="title">{{title}}</h4>

  <form [formGroup]="form">
      <formly-form [model]="model" [fields]="fields" [form]="form" [options]="options">
      </formly-form>
  </form>
  <button class="btn btn-primary mt-3" type="button" [disabled]="!form.valid" (click)="onSubmit()">Salva</button>
  </div>
  `,
  styles: []
})

export class PagamentoComponent extends BaseEntityComponent {
  
  public STATE = 'inpagamento';
  public static WORKFLOW_ACTION: string = 'registrazionepagamento'; //TRASITION
  public static ABSULTE_PATH: string = 'home/pagamento';

  get workflowAction(): string{
    return PagamentoComponent.WORKFLOW_ACTION;
  }


  fields: FormlyFieldConfig[] = [
    {
      className: 'section-label',
      template: '<h5></h5>',
    },
    {
      key: 'id',
      type: 'external',
      className: "col-md-12",
      templateOptions: {
        label: 'Scadenza',
        type: 'string',            
        entityName: 'scadenza',
        entityLabel: 'Scadenza',
        entityPath: 'home/scadenze',
        codeProp: 'id',
        descriptionProp: 'dovuto_tranche',
        descriptionFunc: (data) => {
            if (data && data.dovuto_tranche){
              this.model.prelievo = data.prelievo;
              return data.dovuto_tranche +' - ' + 'Convenzione n. '+data.convenzione.id+' - '+data.convenzione.descrizione_titolo;
            }
            return '';
        },
        copymodel: false,
        isLoading: false,          
        disabled: true,
      },
    },     
    {
      fieldGroupClassName: 'row',
      fieldGroup: [    
        {
          key: 'data_ordincasso',
          type: 'datepicker',
          className: "col-md-5",
          templateOptions: {
            required: true,
            label: 'Data ordinativo incasso',          
          },        
        },
        {
          key: 'num_ordincasso',
          type: 'input',
          className: "col-md-5",
          templateOptions: {
            required: true,
            label: 'Numero ordinativo incasso',                    
          },        
        },    
    ]}, 
    {
      key: 'prelievo',
      type: 'select',      
      //defaultValue: 'PRE_NO',
      templateOptions: {
        options: [
          { label: 'Nessun prelievo', value: 'PRE_NO' },
          { label: 'Prelievo applicabile', value: 'PRE_SI' },          
        ],
        label: 'Prelievo',
        required: true,
      },
    },
    {
      key: 'note',
      type: 'textarea',
      templateOptions: {      
        label: 'Note',        
        rows: 5,     
      },        
    }, 
  ]

  
  constructor(protected service: ApplicationService, protected scadService: ScadenzaService, protected route: ActivatedRoute, protected router: Router, protected location: Location) {
    super(route, router, location)
    this.isLoading = false;
  }

  ngOnInit() {
    
    this.route.params.subscribe(params => {
      if (params['id']) {                  
        //leggere la minimal della convenzione        
          this.isLoading=true;
          this.model = { convenzione: {}};
          //leggere la minimal della convenzione        
          this.scadService.getById(params['id']).subscribe(
            result => {
              if (result){            
                  setTimeout(() => {
                    this.model = {...this.model, ...result};   
                    this.options.formState.model = this.model;         
                  },0)
              }
              this.isLoading=false;
            }
          );
        }        
      });    
  }

  onSubmit() {
    if (this.form.valid) {
      this.isLoading = true;
      var tosubmit = { id: this.model.id, ...this.form.value };      
      this.service.pagamentoStep(tosubmit,true).subscribe(
        result => {          
          this.isLoading = false;          
          this.router.navigate(['home/dashboard/dashboard1']);                
        },
        error => {
          this.isLoading = false;
          //this.service.messageService.error(error);          
        });
    }
  }
}
