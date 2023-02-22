import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { FormlyFieldConfig, Field } from '@ngx-formly/core';
import { BaseEntityComponent } from 'src/app/shared';
import { ApplicationService } from '../application.service';
import { ActivatedRoute, Router } from '@angular/router';
import { encode, decode } from 'base64-arraybuffer';
import { takeUntil, startWith, tap, distinctUntilChanged } from 'rxjs/operators';
import { Location } from '@angular/common';
import { ConfirmationDialogService } from 'src/app/shared/confirmation-dialog/confirmation-dialog.service';
import { NgbStringAdapter } from 'src/app/NgbStringAdapter';

@Component({
  selector: 'app-firmadirettore',
  template: `
  <div class="container-fluid">
  <ngx-loading [show]="isLoading" [config]="{ backdropBorderRadius: '14px' }"></ngx-loading>
  <div class="btn-toolbar mb-4" role="toolbar">
  <div class="btn-group btn-group">        
    <button class="btn btn-outline-primary rounded-lg" [disabled]="!form.valid || !form.dirty" (click)="onSubmit()" >              
      <span class="oi oi-arrow-top"></span>  
      <span class="ml-2">{{ 'btn_salva' | translate }}</span>              
    </button> 
    <button class="btn btn-outline-primary rounded-lg ml-1" (click)="onValidate()" >              
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
  <button class="btn btn-primary mt-3" type="button" [disabled]="!form.valid" (click)="onSubmit()">{{ 'btn_salva' | translate }}</button>
  </div>
  `,
  styles: []
})

export class FirmaDirettoreComponent extends BaseEntityComponent {

  public STATE = 'da_firmare_direttore2';
  public static WORKFLOW_ACTION: string = 'firma_da_direttore2'; //TRASITION
  public static ABSULTE_PATH: string = 'home/firmadirettore';

  adapter = new NgbStringAdapter();

  get workflowAction(): string {
    return FirmaDirettoreComponent.WORKFLOW_ACTION;
  }


  fields: FormlyFieldConfig[] = [
    {
      wrappers: ['riquadro'],
      templateOptions: {
        title: 'Completamento sottoscrizione'
      },
      fieldGroup: [
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
            descriptionFunc: (data) => {
              if (data && data.descrizione_titolo) {
                this.updateEmails(data);
                this.updateStipula(data.stipula_format);
                return data.descrizione_titolo;
              }
              return '';
            },
            isLoading: false,
            rules: [{ value: this.STATE, field: "current_place", operator: "=" }],
          },
          expressionProperties: {
            'templateOptions.disabled': 'formState.disabled_covenzione_id',
          },
        },
        //stipula format 
        {
          key: 'stipula_format',
          type: 'select',
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
        //allegato 1
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
                  defaultValue: 'LTU_FIRM_ENTRAMBI',
                  templateOptions: {
                    options: [
                    ],
                    label: 'Tipo documento',
                    required: true,
                  },
                  hooks: {
                    onInit: (field) => {
                      field.form.parent.get('stipula_format').valueChanges.pipe(
                        takeUntil(this.onDestroy$),
                        distinctUntilChanged(),
                        tap(type => {
                          if (type == 'digitale') {
                            field.templateOptions.options = [
                              { value: 'LTU_FIRM_ENTRAMBI_PROT', label: 'Lettera di trasmissione via PEC' },
                              { value: 'LTU_FIRM_ENTRAMBI', label: 'Lettera di trasmissione' },
                              { value: 'NESSUN_DOC', label: 'Nessun documento di accompagnamento' }
                            ];
                          } else {
                            field.templateOptions.options = [
                              { value: 'LTU_FIRM_ENTRAMBI', label: 'Lettera di trasmissione' },
                              { value: 'NESSUN_DOC', label: 'Nessun documento di accompagnamento' }
                            ];
                          }
                          field.formControl.setValue(field.templateOptions.options[0].value);
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
                  key: 'data_sottoscrizione',
                  type: 'datepicker',
                  className: "col-md-5",
                  templateOptions: {
                    label: 'Data',
                    required: true,                               
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
        //allegato 2
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
                options: [{ stipula_type: 'ditta', codice: 'CONV_FIRM_ENTRAMBI', descrizione: 'Convenzione firmata da entrambi' }],
                valueProp: 'codice',
                labelProp: 'descrizione',
                label: 'Tipo documento',
              },
            },
            {
              key: 'filename',
              type: 'fileinput',
              className: "col-md-5",
              templateOptions: {
                label: 'Scegli il documento',
                type: 'input',
                placeholder: 'Scegli file documento',
                accept: 'application/pdf,.p7m,application/pkcs7-mime', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,            
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
        //email
        {
          hideExpression: (model, formstate) => {
            return !(formstate.model.stipula_format === 'digitale' && formstate.model.attachment1.attachmenttype_codice === 'LTU_FIRM_ENTRAMBI_PROT');
          },
          fieldGroup: [
            {
              key: 'email',
              type: 'input',
              templateOptions: {
                translate: true,
                label: 'AZIENDALOC.PEC',
                disabled: true,
                description: 'Contatti delle aziende o enti associate alla convenzione',                           
              },
            },
          ],
        },
      ]
    },
    {
      wrappers: ['riquadro'],
      templateOptions: {
        title: 'Data di stipula'
      },
      fieldGroup: [
        //date di stipula
        {
          fieldGroupClassName: 'row',
          fieldGroup: [
            {
              key: 'data_stipula',
              type: 'datepicker',
              className: "col-md-6",
              templateOptions: {
                required: true,
                label: 'Data di stipula convenzione',
              },
            },          
          ]
        }
      ]
    },
    {
      wrappers: ['riquadro'],
      templateOptions: {
        title: 'Date inizio e fine convenzione'
      },
      fieldGroup: [
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
              },
              hooks: {
                onInit: (field) => {
                  const form = field.formControl;
                  field.formControl.valueChanges.pipe(
                    takeUntil(this.onDestroy$),
                    tap(val => {
                      if (field.formControl.valid) {
                        let al_giorno = field.parent.fieldGroup.find(x => x.key == 'data_fine_conv');
                        al_giorno.templateOptions.datepickerOptions.minDate = this.adapter.fromModel(val);
                        this.cdr.detectChanges();
                      }
                    }),
                  ).subscribe();
                },
              },
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
      ]
    },
    //scadenziario
    {
      wrappers: ['riquadro'],
      templateOptions: {
        title: 'Scadenziario'
      },
      fieldGroup: [
        {
          key: 'scadenze',
          type: 'repeat',
          templateOptions: {
            label: '',
          },
          fieldArray: {
            fieldGroupClassName: 'row',
            fieldGroup: [
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
      ]
    },
  ]

  onSelectCurrentFile(currentSelFile, field: FormlyFieldConfig) {


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

  constructor(protected service: ApplicationService, protected route: ActivatedRoute, protected router: Router, protected location: Location,
    protected confirmationDialogService: ConfirmationDialogService, private cdr: ChangeDetectorRef) {
    super(route, router, location)
    this.isLoading = false;
  }

  updateStipula(value) {
    if (value) {
      this.form.get('stipula_format').setValue(value);
    }
  }

  updateEmails(convenzione) {
    if (convenzione.aziende) {
      const emails = (convenzione.aziende.map(it => it.pec_email)).join(', ');
      this.model.email = emails;
    }
    else
      this.model.email = 'email non associata';
  }

  ngOnInit() {

    this.route.params.subscribe(params => {
      if (params['id']) {
        this.isLoading = true;
        this.model.convenzione_id = params['id'];
        //leggere la minimal della convenzione        
        this.service.getMinimal(this.model.convenzione_id).subscribe(
          result => {
            if (result) {  
              setTimeout(() => {
                this.fields[0].fieldGroup.find(x => x.key == 'convenzione').templateOptions.init(result);          
              });
            }
            this.isLoading = false;
          }
        );

        this.options.formState.disabled_covenzione_id = true;
      };
    });
  }

  onSubmit() {
    if (this.form.valid) {
      this.isLoading = true;
      var tosubmit = { ...this.model, ...this.form.value };

      if (!tosubmit.convenzione_id) {
        tosubmit.convenzione_id = this.model.convenzione.id;
      }

      tosubmit.transition = this.workflowAction;
      this.service.complSottoscrizioneStep(tosubmit, true).subscribe(
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
          //this.service.messageService.error(error);          
        });
    }
  }
}
