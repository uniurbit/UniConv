import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig, Field } from '@ngx-formly/core';
import { BaseEntityComponent } from 'src/app/shared';
import { ApplicationService } from '../application.service';
import { ActivatedRoute, Router } from '@angular/router';
import { encode, decode } from 'base64-arraybuffer';
import { Location } from '@angular/common';
import ControlUtils from 'src/app/shared/dynamic-form/control-utils';
import { PDFJSStatic } from "pdfjs-dist";
import { ConfirmationDialogService } from 'src/app/shared/confirmation-dialog/confirmation-dialog.service';

const PDFJS: PDFJSStatic = require('pdfjs-dist');

@Component({
  selector: 'app-bollorepertoriazione',
  template: `
  <div class="container-fluid">
  <ngx-loading [show]="isLoading" [config]="{ backdropBorderRadius: '14px' }"></ngx-loading>
  <div class="btn-toolbar mb-4" role="toolbar">
  <div class="btn-group btn-group">        
    <button class="btn btn-outline-primary rounded-lg" [disabled]="!form.valid || !form.dirty" (click)="onSubmit()" >              
      <span class="oi oi-arrow-top"></span>  
      <span class="ml-2">Aggiorna</span>              
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
  <button class="btn btn-primary mt-3" type="button" [disabled]="!form.valid" (click)="onSubmit()">Salva e repertoria</button>
  </div>
  `,
  styles: []
})

export class BolloRepertoriazioneComponent extends BaseEntityComponent {

  public static STATE = 'firmato';
  public static WORKFLOW_ACTION: string = 'repertorio'; 
  public static ABSULTE_PATH: string = 'home/bollorepertoriazione';

  public numPages: number;
  public numLines: number;


  get workflowAction(): string {
    return BolloRepertoriazioneComponent.WORKFLOW_ACTION;
  }

  static decodeConvenzione = (that) => {
    return {
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
            that.updateStipula(data.stipula_format);
            return data.descrizione_titolo;
          }
          return '';
        },
        isLoading: false,
        rules: [{ value: BolloRepertoriazioneComponent.STATE, field: "current_place", operator: "=" }],
      },
      expressionProperties: {
        'templateOptions.disabled': 'formState.disabled_covenzione_id',
      },
    }
  };

  static comboStipulaFormat = {
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
      disabled: true,
      required: true,
    }
  };

  static comboBolloVirtuale = {
    fieldGroupClassName: 'row',
    fieldGroup: [
      {
        key: 'bollo_virtuale',
        type: 'select',
        defaultValue: false,
        className: 'col-md-2',
        templateOptions: {
          label: 'Bollo virtuale',
          options: [
            { label: 'Si', value: true },
            { label: 'No', value: false },
          ],
        },
      }],
  };

  static sceltaBolli = (that) => {
    return {
      fieldGroupClassName: 'row',
      fieldGroup: [
        {
          key: 'bolli',
          type: 'repeat',
          className: 'col-md-8',
          templateOptions: {
            min: 1,
          },
          fieldArray: {
            fieldGroupClassName: 'row',
            fieldGroup: [{
              key: 'num_bolli',
              type: 'number',
              className: 'col-md-4',
              templateOptions: {
                required: true,
                label: 'Numero bolli',
                description: 'Calcolare un bollo ogni 100 righe di convenzione',
              },
              expressionProperties: {
                'templateOptions.description': (model: any, formState: any) => {
                  return (model.tipobolli_codice == 'BOLLO_ATTI') ? 'Calcolare un bollo ogni 100 righe di convenzione' : '';
                },
              }
            },
            {
              key: 'tipobolli_codice',
              type: 'select',
              className: "col-md-6",
              defaultValue: 'BOLLO_ATTI',
              templateOptions: {
                options: [
                  { label: 'Atti e provvedimenti', value: 'BOLLO_ATTI' },
                  { label: 'Allegati tecnici', value: 'BOLLO_TEC_ALLEGATO' },
                ],
                label: 'Applicazione',
                required: true,
              },
            },

            ],
          },
          hideExpression: (model, formstate) => {
            return (formstate.model.bollo_virtuale == false);
          },
          validators: {
            unique: {
              expression: (c) => {
                if (c.value) {
                  var valueArr = c.value.map(function (item) { return item.tipobolli_codice }).filter(x => x != null);
                  var isDuplicate = valueArr.some(function (item, idx) {
                    return valueArr.indexOf(item) != idx
                  });
                  return !isDuplicate;
                }
                return true;
              },
              message: (error, field: FormlyFieldConfig) => `Tipo bollo ripetuto`,
            },
            atleasttype: {
              expression: (c) => {
                const f = c.value.find(x => x.tipobolli_codice == 'BOLLO_ATTI');
                if (f) {
                  return true;
                }
                return false;
              },
              message: (error, field: FormlyFieldConfig) => `Richiesto il bollo 'Atti e provvedimenti'`,
            }
          },
        },
        {
          type: 'template',
          className: 'col-md-4 mt-4 pt-3',
          templateOptions: {
            template: '',
          },
          hideExpression: (model, formstate) => {
            return !(that.model.bollo_virtuale && that.numPages != null && that.numPages != undefined);
          },
          expressionProperties: {
            'templateOptions.template': () => `           
          <h6 class="panel-title">
           Numero di pagine ${that.numPages} e numero righe calcolate ${that.numLines}
          </h6>
        `
          }
        },
      ]
    }
  }

  fields: FormlyFieldConfig[] = [
    {
      wrappers: ['riquadro'],
      templateOptions: {
        title: 'Repertoriazione'
      },
      fieldGroup: [
        //decodifica convenzione
        BolloRepertoriazioneComponent.decodeConvenzione(this),
        //stipula format
        BolloRepertoriazioneComponent.comboStipulaFormat,
        //bollo virtuale
        BolloRepertoriazioneComponent.comboBolloVirtuale,
        //bolli 
        BolloRepertoriazioneComponent.sceltaBolli(this),
        //allegato 1 per repertoriare        
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
                  defaultValue: 'DOC_BOLLATO_FIRMATO',
                  templateOptions: {
                    //todo chiedere lato server 
                    options: [
                      { codice: 'DOC_BOLLATO_FIRMATO', descrizione: 'Convenzione firmata e bollata' },
                    ],
                    valueProp: 'codice',
                    labelProp: 'descrizione',
                    label: 'Tipo documento',
                  },
                  //RES - CRITICA - 2) Utente [E]          
                },
                {
                  key: 'filename',
                  type: 'fileinput',
                  className: "col-md-7",
                  templateOptions: {
                    required: true,
                    label: 'Scegli il documento',
                    type: 'input',
                    placeholder: 'Scegli file documento',
                    accept: 'application/pdf,application/pkcs7-mime', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,                
                    onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
                  },
                },
              ],
            },
          ],
        },
        //allegati opzionali
        {
         
          fieldGroup: [
            {
              template: '<h5 class="mt-3">Allegati opzionali</h5>',
            },
            {
              key: 'optional_attachments',
              type: 'repeat',
              templateOptions: {
              // translate: true,
                // label: 'Allegati',
                min: 0,
                max: 5,
                btnHidden: false,
                btnRemoveHidden: false,
              },
              fieldArray: {
                fieldGroup: [
                  {
                    fieldGroupClassName: 'row',
                    fieldGroup: [
                      // attachmenttype_codice
                      {
                        key: 'attachmenttype_codice',
                        type: 'input',
                        defaultValue: 'ALLEGATO',
                        templateOptions: {
                          type: 'hidden',
                        }
                      },
                      // filename
                      {
                        // NB è stato richiesto in fase di validazione di poter inserire dei
                        // riferimenti ad degli allegati ma senza includere il file
                        key: 'filename',
                        type: 'fileinput',
                        className: 'col-md-5',
                        validation: {
                          show: true
                        },
                        templateOptions: {
                          translate: true,
                          label: 'Allegato',
                          type: 'input',
                          placeholder: 'Carica il documento . . . ',
                          description: 'N.B. Il documento da caricare deve essere in formato PDF',
                          accept: 'application/pdf',
                          maxLength: 255,
                          required: true,
                          onSelected: (selFile, field) => { this.onSelectOptionalCurrentFile(selFile, field); }
                        },
                      },
                    ],
                  },
                  {
                    fieldGroupClassName: 'row',
                    fieldGroup: [
                      {
                        key: 'filevalue',
                        type: 'input',
                        templateOptions: {
                          type: 'hidden'
                        },
                      },
                      {
                        key: 'id',
                        type: 'input',
                        templateOptions: {
                          type: 'hidden'
                        },
                      },
                    ],
                  },
                ],
              },
            },
          ]
        }
      ]
    },
  ];

  onSelectOptionalCurrentFile(currentSelFile, field: FormlyFieldConfig) {
    const currentAttachment = field.formControl.parent.value;
    if (currentSelFile == null) {
      // caso di cancellazione
      currentAttachment.filevalue = null;
      return;
    }

    this.isLoading = true;
    currentAttachment.model_type = 'convenzione';

    const reader = new FileReader();

    reader.onload = async (e: any) => {
      this.isLoading = true;
      // currentAttachment.filevalue = encode(e.target.result);
      field.formControl.parent.get('filevalue').setValue(encode(e.target.result));
      if (currentSelFile.name.search('pdf') > 0) {
        try {
          field.formControl.markAsDirty();
        } catch (error) {
          console.log(error);
          this.isLoading = false;
        }
      }

      if (!currentAttachment.filevalue) {
        this.isLoading = false;
        return;
      }
      this.isLoading = false;
    };
    reader.readAsArrayBuffer(currentSelFile);
  }

  onSelectCurrentFile(currentSelFile, field: FormlyFieldConfig) {

    let currentAttachment = field.parent.model; //.formControl.parent.value;
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
      currentAttachment.filevalue = encode(e.target.result);

      if (currentAttachment.filename.endsWith('.pdf')) {
        this.numLines = await this.lineNumber(e.target.result);
      } else {
        this.numLines = null;
        this.numPages = null;
      }     

      if (!currentAttachment.filevalue) {
        this.isLoading = false;
        return;
      }
      this.isLoading = false;
    }
    reader.readAsArrayBuffer(currentSelFile);
  }

  async lineNumber(data): Promise<number> {
    let text = '';
    return await PDFJS.getDocument({ data: data }).then(async (doc) => {
      let counter: number = 100;

      this.numPages = doc.numPages;

      counter = counter > doc.numPages ? doc.numPages : counter;

      let linecount: number = 0;
      for (var i = 1; i <= counter; i++) {
        let pageText = await doc.getPage(i).then(pageData => ControlUtils.render_page(pageData)) as string;

        linecount += pageText.split('\n').filter(x => {
          if (x.trim().length == 0)
            return false;

          return true;
        }).length;

      }

      return linecount;

    });
  }

  bolliCount(num_lines: number): number {
    if (num_lines > 0) {
      let arround = 0;
      if (num_lines % 100 > 0)
        arround = 1;

      return Math.floor(num_lines / 100) + arround;
    }

    return 0;
  }


  constructor(protected service: ApplicationService, protected route: ActivatedRoute, protected router: Router, protected location: Location,
    protected confirmationDialogService: ConfirmationDialogService) {
    super(route, router, location)
    this.isLoading = false;
  }

  ngOnInit() {
    this.route.params.subscribe(params => {
      if (params['id']) {
        this.model.convenzione_id = params['id'];
        this.isLoading = true;
        //leggere la minimal della convenzione        
        this.service.getMinimal(this.model.convenzione_id).subscribe(
          result => {
            if (result) {
              setTimeout(
                () => {
                  this.fields[0].fieldGroup.find(x => x.key == 'convenzione').templateOptions.init(result);
                });
              this.isLoading = false;
            }
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
      tosubmit.attachment1 = { ...this.model.attachment1, ...this.form.value.attachment1 };
      tosubmit.transition = this.workflowAction;
      this.service.bolloRepertoriazioneStep(tosubmit, true).subscribe(
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

  updateStipula(value) {
    if (value) {
      this.form.get('stipula_format').setValue(value);
    }
  }
}
