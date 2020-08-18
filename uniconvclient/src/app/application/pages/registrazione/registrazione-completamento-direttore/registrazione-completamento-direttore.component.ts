import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig, Field } from '@ngx-formly/core';
import { BaseEntityComponent } from 'src/app/shared';

import { ActivatedRoute, Router } from '@angular/router';
import { encode, decode } from 'base64-arraybuffer';
import { takeUntil, startWith, tap, distinctUntilChanged } from 'rxjs/operators';
import {Location} from '@angular/common';
import { ConfirmationDialogService } from 'src/app/shared/confirmation-dialog/confirmation-dialog.service';
import { ApplicationService } from 'src/app/application/application.service';

@Component({
  selector: 'app-registrazione-completamento-direttore',
  templateUrl: './registrazione-completamento-direttore.component.html',
  styles: []
})
//ng g c application/pages/registrazione/registrazioneCompletamentoDirettore  -s true --spec false 
export class RegistrazioneCompletamentoDirettoreComponent extends BaseEntityComponent {

  public STATE = 'da_firmare_direttore2';
  public static WORKFLOW_ACTION: string = 'firma_da_direttore2'; //TRASITION
  public static ABSULTE_PATH: string = 'home/registrazionefirmadirettore';

  fields: FormlyFieldConfig[] = [
    {
      className: 'section-label',
      template: '<h5></h5>',
    },
    //decodifica convenzione
    {
      key: 'convenzione',
      type: 'externalobject',      
      templateOptions: {
        label: 'Convenzione',
        type: 'string',
        required: true,
        entityName: 'application',
        entityLabel: 'Convenzione',
        entityPath: 'home/convenzioni',
        codeProp: 'id',
        descriptionProp: 'descrizione_titolo',
        isLoading: false,    
        rules: [{value: this.STATE, field: "current_place", operator: "="}],
      },  
      expressionProperties: {
        'templateOptions.disabled': 'formState.disabled_covenzione_id',
      },    
    },   
    //stipula format
    {
      key: 'stipula_format',
      type: 'select',
      defaultValue: 'cartaceo',
      templateOptions: {        
        options: [
          { codice: 'cartaceo', descrizione: 'Stipula cartacea' },
          { codice: 'digitale', descrizione: 'Stipula digitale' },
        ],
        valueProp: 'codice',
        labelProp: 'descrizione',
        label: 'Formato di stipula',
        required: true,
      },
      expressionProperties: {
        'templateOptions.disabled': 'formState.disabled_covenzione_id',
      },    
    },
    //allegato 1 lettera in uscita
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
              defaultValue: 'LTU_FIRM_ENTRAMBI_PROT',
              templateOptions: {
                //todo chiedere lato server 
                options: [],
                valueProp: 'codice',
                labelProp: 'descrizione',
                label: 'Tipo documento',
                required: true,
              },
              hooks: {
                onInit: (field) => {
                  field.form.parent.get('stipula_format').valueChanges.pipe(
                    takeUntil(this.onDestroy$),
                    distinctUntilChanged(),
                    startWith(field.form.parent.get('stipula_format').value),
                    tap(type => {
                      if (type == 'digitale') {
                        field.templateOptions.options = [
                          { codice: 'LTU_FIRM_ENTRAMBI_PROT', descrizione: 'Lettera di trasmissione via PEC già protocollata' },                              
                          { codice: 'NESSUN_DOC', descrizione: 'Nessun documento di accompagnamento' }
                        ];
                      } else {
                        field.templateOptions.options = [                              
                          { codice: 'LTU_FIRM_ENTRAMBI', descrizione: 'Lettera di trasmissione già protocollata' },
                          { codice: 'NESSUN_DOC', descrizione: 'Nessun documento di accompagnamento' }
                        ];
                      }
                      field.formControl.setValue(field.templateOptions.options[0].codice);
                    }),
                  ).subscribe();
                }
              }
            },
            {
              key: 'filename',
              type: 'fileinput',
              className: "col-md-5",
              templateOptions: {
                label: 'Scegli il documento',
                type: 'input',
                placeholder: 'Scegli file documento',
                accept: 'application/pdf', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                required: true,
                onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
              },
              hideExpression: (model, formState) => {
                return (formState.model.attachment1.attachmenttype_codice == 'NESSUN_DOC');
              },
            }, 
            {
              key: 'doc',
              type: 'externalobject',
              className: "col-md-7",
              templateOptions: {
                label: 'Numero di protocollo',
                required: true,      
                type: 'string',
                entityName: 'documento',
                entityLabel: 'Documenti',
                codeProp: 'num_prot',
                descriptionProp: 'oggetto',
                isLoading: false,
                rules: [{ value: "arrivo", field: "doc_tipo", operator: "=" }],
              },
              hideExpression: (model, formState) => {
                return (formState.model.attachment1.attachmenttype_codice !== 'LTU_FIRM_ENTRAMBI_PROT');
              },
            }, 
            {
              key: 'data_sottoscrizione',
              type: 'datepicker',
              className: "col-md-5",
              templateOptions: {
                label: 'Data',
                required: true,
                //required: true,                               
              },
              hideExpression: (model: any, formState: any) => {
                return (formState.model.attachment1.attachmenttype_codice !== 'NESSUN_DOC');
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
    //allegato 2 convenzione firmata da entrambi
    {
      fieldGroupClassName: 'row',
      key: 'attachment2',
      fieldGroup: [
        {
          key: 'attachmenttype_codice',
          type: 'select',
          className: "col-md-5",
          defaultValue: 'CONV_FIRM_ENTRAMBI',
          templateOptions: {                
            //required: true,  
            options: [{ stipula_type: 'ditta', codice: 'CONV_FIRM_ENTRAMBI', descrizione: 'Convenzione firmata da entrambi' }],
            valueProp: 'codice',
            labelProp: 'descrizione',
            label: 'Tipo documento',
          },
          expressionProperties: {
            'templateOptions.required': (model: any, formState: any) => formState.model.stipula_format == 'digitale',
          },  
        },
        {
          key: 'filename',
          type: 'fileinput',
          className: "col-md-5",
          templateOptions: {
            //required: true,  
            label: 'Scegli il documento',
            type: 'input',
            placeholder: 'Scegli file documento',
            accept: 'application/pdf', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,            
            onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
          },
          expressionProperties: {
            'templateOptions.required': (model: any, formState: any) => formState.model.stipula_format == 'digitale',
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
    //data inizio e fine
    {
      fieldGroupClassName: 'row',
      fieldGroup: [
        {
          key: 'data_inizio_conv',
          type: 'datepicker',
          className: "col-md-6",
          templateOptions: {
            required: true,  
            label: 'Data inizio convenzione',
          }
        },
        {
          key: 'data_fine_conv',
          type: 'datepicker',
          className: "col-md-6",        
          templateOptions: {
            required: true,  
            label: 'Data fine convenzione',
          }      
        }            
      ]       
    },
    //scadenze
    {
      key: 'scadenze',
      type: 'repeat',
      templateOptions: {
        label: 'Scadenziario',
      },    
      fieldArray: {
        fieldGroupClassName: 'row',
        fieldGroup:  [
          {
            key: 'data_tranche',
            type: 'datepicker',
            className: "col-md-5",                
            templateOptions: {                  
              required: true,                    
              label: 'Tranche prevista',
            },
          },
          {
            key: 'dovuto_tranche',
            type: 'maskcurrency',
            className: "col-md-5",
            templateOptions: {
              required: true,  
              label: 'Importo',                  
            },  
          },

        ],                
      } 
    }

  ];


  constructor(protected service: ApplicationService, protected route: ActivatedRoute, protected router: Router, protected location: Location,
    protected confirmationDialogService: ConfirmationDialogService) {
    super(route, router, location)
    this.isLoading = false;
  }

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

  ngOnInit() {
    
    this.route.params.subscribe(params => {
      if (params['id']) {
        this.model.convenzione_id = params['id'];         
        this.isLoading=true;
        //leggere la minimal della convenzione        
        this.service.getMinimal(this.model.convenzione_id).subscribe(
          result => {
            if (result){             
              setTimeout(()=> {
                this.fields.find(x=> x.key == 'convenzione').templateOptions.init(result);                                            
                this.form.get('stipula_format').setValue(result.stipula_format);           
              });                                      
            }
            this.isLoading=false;
          }
        );

        this.options.formState.disabled_covenzione_id = true;
      }
    });


  }

  onSubmit() {
    if (this.form.valid) {
      this.isLoading = true;
      var tosubmit = { ...this.model, ...this.form.value };
      
      if (!tosubmit.convenzione_id){
        tosubmit.convenzione_id = this.model.convenzione.id;
      }

      tosubmit.transition = RegistrazioneCompletamentoDirettoreComponent.WORKFLOW_ACTION;      
      this.service.registrazioneComplSottoscrizione(tosubmit,true).subscribe(
        result => {          
          this.confirmationDialogService.confirm("Finestra messaggi", result.message, "Chiudi", null, 'lg').then(
            () =>this.router.navigate(['home/convdetails', this.model.convenzione_id]),
            () => this.router.navigate(['home/dashboard/dashboard1']))
          .catch(() => {           
          });
          this.isLoading = false;                                 
        },
        error => {
          this.isLoading = false;
          //this.service.messageService.error(error);          
        });
    }
  }
}
