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
import { Location } from '@angular/common';
import { ConfirmationDialogService } from 'src/app/shared/confirmation-dialog/confirmation-dialog.service';

@Component({
  selector: 'app-inviorichiestapagamento',
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
  <button class="btn btn-primary mt-3" type="button" [disabled]="!form.valid" (click)="onSubmit()">Salva e invia</button>
  </div>
  `,
  styles: []
})

export class InvioRichiestaPagamentoComponent extends BaseEntityComponent {
  
  public STATE = 'attivo';
  public static WORKFLOW_ACTION: string = 'richiestapagamento'; //TRASITION
  public static ABSULTE_PATH: string = 'home/inviorichiestapagamento';

  get workflowAction(): string{
    return InvioRichiestaPagamentoComponent.WORKFLOW_ACTION;
  }


  fields: FormlyFieldConfig[] = [
    {
      className: 'section-label',
      template: '<h5></h5>',
    },
    {
      fieldGroupClassName: 'row',
      fieldGroup: [
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
                if (data && data.convenzione){
                  this.updateEmail(data.convenzione.id); 
                  return data.dovuto_tranche +' - ' + 'Convenzione n. '+data.convenzione.id+' - '+data.convenzione.descrizione_titolo;
                }
                return '';
            },
            rules: [{value: this.STATE, field: "state", operator: "="}],
            copymodel: true,
            isLoading: false,          
          },
          expressionProperties: {
            'templateOptions.disabled': (model: any, formState: any) => {                        
                return formState.disabled_id;
            },
          },              
        },
      ]
    },
    {     
      key: 'attachment1',
      fieldGroup: [        
        {
          fieldGroupClassName: 'row',
          fieldGroup: [
            {
              key: 'attachmenttype_codice',
              type: 'select',
              className: "col-md-5",
              defaultValue: 'RICHIESTA_PAGAMENTO',
              templateOptions: {                
                options: [                  
                  { codice: 'RICHIESTA_PAGAMENTO', descrizione: 'Richiesta pagamento' },                  
                ],
                valueProp: 'codice',
                labelProp: 'descrizione',
                label: 'Tipo documento',
                required: true,
              }
            },
            {
              key: 'filename',
              type: 'fileinput',
              className: "col-md-7",              
              templateOptions: {
                label: 'Scegli il documento',
                type: 'input',
                placeholder: 'Scegli file documento',
                accept: 'application/pdf', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                required: true,
                onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
              },      
            },
            {
              key: 'filevalue',
              type: 'input',
              templateOptions: {
                type: 'hidden'        
              },
            },          
          ],
        },
      ],
    },
    {
      key: 'email',
      type: 'input',          
      templateOptions: {
        translate: true,
        label: 'AZIENDALOC.PEC', 
        disabled: true,
        required: true,   
        description: 'Contatti delle aziende o enti associate alla convenzione',                            
      },          
    },         

  ]

  onSelectCurrentFile(currentSelFile, field: FormlyFieldConfig){
    let currentAttachment = field.formControl.parent.value;
    if (currentSelFile == null) {
      //caso di cancellazione
      currentAttachment.filevalue = null;
      return;
    }
  
    this.isLoading = true;
    currentAttachment.model_type = 'convenzione';
    
    const reader = new FileReader();   

    reader.onload = async (e: any) => {
      this.isLoading = true;
      field.formControl.parent.get('filevalue').setValue(encode(e.target.result));
      
      if (!currentAttachment.filevalue) {
        this.isLoading = false;
        return;
      }    
      this.isLoading = false;
    }
    reader.readAsArrayBuffer(currentSelFile);
  }
  
  constructor(protected service: ApplicationService, protected scadService: ScadenzaService, protected route: ActivatedRoute, protected router: Router, 
    protected location: Location, protected confirmationDialogService: ConfirmationDialogService) {
    super(route, router, location)
    this.isLoading = false;
  }

  ngOnInit() {
    
    this.route.params.subscribe(params => {
      this.options.formState.disabled_id = false;                
      if (params['id']) {  
        this.options.formState.disabled_id = true;                
        //leggere la minimal della convenzione        
        this.model = this.service.getRichiestaEmissioneData();
        if (this.model){
          this.options.formState.model = this.model;
          this.options.formState.disabled_covenzione_id = true;          
        }else{
          this.isLoading=true;
          this.model = { convenzione: {}};
          //leggere la minimal della convenzione        
          this.scadService.getById(params['id']).subscribe(
            result => {
              if (result){            
                  this.model = result;   
                  this.options.formState.model = this.model;                      
              }
              this.isLoading=false;
            }
          );
        }

     

      };
    });
  }

  updateEmail(convenzione_id){
    if (convenzione_id){
      this.service.getAziende(convenzione_id).subscribe(
        result => { 
          setTimeout(() => {                        
            if (result && result[0]){
              const emails = (result.map(it => it.pec_email)).join(', ');
              if (this.form.get('email'))
                this.form.get('email').setValue(emails);
            }else 
              this.form.get('email').setValue('email non associata');
          }, 0);              
        }
      );
    }
  }


  onSubmit() {
    if (this.form.valid) {
      this.isLoading = true;
      var tosubmit = { ...this.model, ...this.form.value };
      
      this.service.invioRichiestaPagamentoStep(tosubmit,true).subscribe(
        result => {          
          
          this.confirmationDialogService.confirm("Finestra messaggi", result.message, "Chiudi", null, 'lg').then(
            () => this.router.navigate(['home/dashboard/dashboard1']),
            () => this.router.navigate(['home/dashboard/dashboard1']))
          .catch(() => {           
          });

          this.isLoading = false;         
        },
        error => {
          this.isLoading = false;       
        });
    }
  }
}
