import { Component, OnInit, Injector, OnChanges, SimpleChanges } from '@angular/core';
import { FormlyFieldConfig, Field } from '@ngx-formly/core';
import { BaseEntityComponent } from 'src/app/shared';
import { ApplicationService } from '../../../application.service';
import { ActivatedRoute, Router } from '@angular/router';
import { encode, decode } from 'base64-arraybuffer';
import { takeUntil, startWith, tap } from 'rxjs/operators';
import {Location} from '@angular/common';
import { ConfirmationDialogService } from 'src/app/shared/confirmation-dialog/confirmation-dialog.service';

@Component({
  selector: 'app-registrazione-sottoscrizione',
  templateUrl: './registrazione-sottoscrizione.component.html',
  styles: []
})
export class RegistrazioneSottoscrizioneComponent extends BaseEntityComponent {  

 //azioni possibili 

  //stato di partenza 'approvato'
  //firma_da_controparte1 --> stipula azienda o ente --> ricevuta lettera con convenzione firmata dalla dall'azienda o ente
  //firma_da_direttore1 --> stipula uniurb --> ricevuta la convenzione firmata dal dipartimento

  labelButton = "Salva";

  public static STATE = 'approvato';
  public static WORKFLOW_ACTIONS: string[] = ['firma_da_controparte1', 'firma_da_direttore1']; //TRASITION
  public static ABSULTE_PATH: string = 'home/registrazionesottoscrizione';

  fields: FormlyFieldConfig[] = [
    {
      className: 'section-label',
      template: '<h5></h5>',
    },
    {
      key: 'convenzione_id',
      type: 'external',
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
        rules: [{ value: "approvato", field: "current_place", operator: "=" }],
      },
      expressionProperties: {
        'templateOptions.disabled': 'formState.disabled_covenzione_id',
      },
    },
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
      }      
    },
    {
      key: 'stipula_type',
      type: 'select',
      defaultValue: 'uniurb',
      templateOptions: {
        options: [
          { codice: 'uniurb', descrizione: 'Stipula UniUrb' },
          { codice: 'controparte', descrizione: 'Stipula Azienda o Ente' },
        ],
        valueProp: 'codice',
        labelProp: 'descrizione',
        label: 'Iter di stipula',
        required: true,
      },
    },

    //cartacea uniurb
      // lettera trasmissione da titulus
      // lettera trasmissione 
      // nessun documento 
    {      
      hideExpression: (model, formstate) => {
        return !(formstate.model.stipula_format == 'cartaceo' && formstate.model.stipula_type == 'uniurb');
      },
      key: 'cartaceo_uniurb',
      fieldGroup: [  
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
                  defaultValue: 'LTU_FIRM_UNIURB_PROT',
                  templateOptions: {
                    options: [
                      { stipula_type: 'uniurb', codice: 'LTU_FIRM_UNIURB_PROT', descrizione: 'Lettera di trasmissione inviata e protocollata' },
                      { stipula_type: 'uniurb', codice: 'NESSUN_DOC', descrizione: 'Nessun documento di accompagnamento' }
                    ],
                    valueProp: 'codice',
                    labelProp: 'descrizione',
                    label: 'Tipo documento',
                    required: true,
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
                    accept: 'application/pdf', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                    required: true,
                    onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
                  },
                  hideExpression: (model, formState) => {
                    return (formState.model.cartaceo_uniurb.attachment1.attachmenttype_codice !== 'LTU_FIRM_UNIURB');
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
                    subpattern: /^[0-9]+-[a-zA-Z]+-\d{7}$/,
                    entityName: 'documento',
                    entityLabel: 'Documenti',
                    codeProp: 'num_prot',
                    descriptionProp: 'oggetto',
                    isLoading: false,  
                    rules: [{value: "partenza", field: "doc_tipo", operator: "="}],                       
                  },      
                  hideExpression: (model, formState) => {
                    return (formState.model.cartaceo_uniurb.attachment1.attachmenttype_codice !== 'LTU_FIRM_UNIURB_PROT');
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
                  hideExpression:(model: any, formState: any) => {               
                    return (formState.model.cartaceo_uniurb.attachment1.attachmenttype_codice !== 'NESSUN_DOC');
                  },                            
                },
              ],
            },      
          ],
        },
        {
          fieldGroupClassName: 'row',
          key: 'attachment2',
          fieldGroup: [
            {
              key: 'attachmenttype_codice',
              type: 'select',
              className: "col-md-5",
              defaultValue: 'CONV_FIRM_UNIURB',
              templateOptions: {
                //todo chiedere lato server 
                options: [{ stipula_type: 'uniurb', codice: 'CONV_FIRM_UNIURB', descrizione: 'Convenzione firmata dal direttore o rettore' }],
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
                accept: 'application/pdf', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,            
                onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
              },

            },

          ],
        },
      ],
    }, 

    //digitale uniurb
      //lettera trasmissione ricevuta via email via pec 
      //lettera trasmissione
      //nessun document
    {      
      hideExpression: (model, formstate) => {
        return !(formstate.model.stipula_format == 'digitale' && formstate.model.stipula_type == 'uniurb');
      },
      key: 'digitale_uniurb',
      fieldGroup: [  
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
                  defaultValue: 'LTU_FIRM_UNIURB_PROT',
                  templateOptions: {
                    options: [
                      { stipula_type: 'uniurb', codice: 'LTU_FIRM_UNIURB_PROT', descrizione: 'Lettera di trasmissione inviata via PEC e protocollata' },
                      { stipula_type: 'uniurb', codice: 'LTU_FIRM_UNIURB', descrizione: "Lettera di trasmissione inviata" },
                      { stipula_type: 'uniurb', codice: 'NESSUN_DOC', descrizione: 'Nessun documento di accompagnamento' }
                    ],
                    valueProp: 'codice',
                    labelProp: 'descrizione',
                    label: 'Tipo documento',
                    required: true,
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
                    accept: 'application/pdf', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                    required: true,
                    onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
                  },
                  hideExpression: (model, formState) => {
                    return (formState.model.digitale_uniurb.attachment1.attachmenttype_codice !== 'LTU_FIRM_UNIURB');
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
                    subpattern: /^[0-9]+-[a-zA-Z]+-\d{7}$/,
                    entityName: 'documento',
                    entityLabel: 'Documenti',
                    codeProp: 'num_prot',
                    descriptionProp: 'oggetto',
                    isLoading: false,  
                    rules: [{value: "partenza", field: "doc_tipo", operator: "="}],                       
                  },      
                  hideExpression: (model, formState) => {
                    return (formState.model.digitale_uniurb.attachment1.attachmenttype_codice !== 'LTU_FIRM_UNIURB_PROT');
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
                  hideExpression:(model: any, formState: any) => {               
                    return (formState.model.digitale_uniurb.attachment1.attachmenttype_codice !== 'NESSUN_DOC');
                  },                            
                },
              ],
            },      
          ],
        },
        {
          fieldGroupClassName: 'row',
          key: 'attachment2',
          fieldGroup: [
            {
              key: 'attachmenttype_codice',
              type: 'select',
              className: "col-md-5",
              defaultValue: 'CONV_FIRM_CONTR',
              templateOptions: {
                options: [{ stipula_type: 'uniurb', codice: 'CONV_FIRM_UNIURB', descrizione: 'Convenzione firmata dal direttore o rettore' }],
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
                accept: 'application/pdf', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,            
                onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
              },

            },

          ],
        },
      ],
    }, 


    //cartaceo controparte
    {      
      hideExpression: (model, formstate) => {
        return !(formstate.model.stipula_format == 'cartaceo' && formstate.model.stipula_type == 'controparte');
      },
      key: 'cartaceo_controparte',
      fieldGroup: [  
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
                  defaultValue: 'LTE_FIRM_CONTR_PROT',
                  templateOptions: {
                    options: [
                      { stipula_type: 'controparte', codice: 'LTE_FIRM_CONTR_PROT', descrizione: 'Lettera ricevuta e protocollata' },
                      { stipula_type: 'controparte', codice: 'NESSUN_DOC', descrizione: 'Nessun documento di accompagnamento' }
                    ],
                    valueProp: 'codice',
                    labelProp: 'descrizione',
                    label: 'Tipo documento',
                    required: true,
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
                    accept: 'application/pdf', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                    required: true,
                    onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
                  },
                  hideExpression: (model, formState) => {
                    return (formState.model.cartaceo_controparte.attachment1.attachmenttype_codice !== 'LTE_FIRM_UNIURB');
                  },
                },
                {
                  key: 'doc',
                  type: 'externalobject',
                  className: "col-md-7",
                  templateOptions: {
                    label: 'Numero di protocollo',   
                    type: 'string',
                    subpattern: /^[0-9]+-[a-zA-Z]+-\d{7}$/,
                    entityName: 'documento',
                    entityLabel: 'Documenti',
                    codeProp: 'num_prot',
                    descriptionProp: 'oggetto',
                    isLoading: false,  
                    rules: [{value: "arrivo", field: "doc_tipo", operator: "="}],                       
                  },      
                  hideExpression: (model, formState) => {
                    return (formState.model.cartaceo_controparte.attachment1.attachmenttype_codice !== 'LTE_FIRM_CONTR_PROT');
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
                  hideExpression:(model: any, formState: any) => {               
                    return (formState.model.cartaceo_controparte.attachment1.attachmenttype_codice !== 'NESSUN_DOC');
                  },                            
                },
              ],
            },      
          ],
        },
        {
          fieldGroupClassName: 'row',
          key: 'attachment2',
          fieldGroup: [
            {
              key: 'attachmenttype_codice',
              type: 'select',
              className: "col-md-5",
              defaultValue: 'CONV_FIRM_CONTR',
              templateOptions: {
                options: [{ stipula_type: 'ditta', codice: 'CONV_FIRM_CONTR', descrizione: 'Convenzione firmata dalla controparte' }],
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
                accept: 'application/pdf', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,            
                onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
              },

            },

          ],
        },
      ],
    },  


    //digitale controparte 
    {      
      hideExpression: (model, formstate) => {
        return !(formstate.model.stipula_format == 'digitale' && formstate.model.stipula_type == 'controparte');
      },
      key: 'digitale_controparte',
      fieldGroup: [  
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
                  defaultValue: 'LTE_FIRM_CONTR_PROT',
                  templateOptions: {
                    options: [
                      { stipula_type: 'controparte', codice: 'LTE_FIRM_CONTR_PROT', descrizione: 'Lettera ricevuta via PEC già protocollata' },
                      { stipula_type: 'controparte', codice: 'NESSUN_DOC', descrizione: 'Nessun documento di accompagnamento' }
                    ],
                    valueProp: 'codice',
                    labelProp: 'descrizione',
                    label: 'Tipo documento',
                    required: true,
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
                    accept: 'application/pdf', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                    required: true,
                    onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
                  },
                  hideExpression: (model, formState) => {
                    return (formState.model.digitale_controparte.attachment1.attachmenttype_codice !== 'LTE_FIRM_CONTR');
                  },
                },
                {
                  key: 'doc',
                  type: 'externalobject',
                  className: "col-md-7",
                  templateOptions: {
                    required: true,
                    label: 'Numero di protocollo',
                    type: 'string',
                    subpattern: /^[0-9]+-[a-zA-Z]+-\d{7}$/,
                    entityName: 'documento',
                    entityLabel: 'Documenti',
                    codeProp: 'num_prot',
                    descriptionProp: 'oggetto',
                    isLoading: false,  
                    rules: [{value: "arrivo", field: "doc_tipo", operator: "="}],                       
                  },      
                  hideExpression: (model, formState) => {
                    return (formState.model.digitale_controparte.attachment1.attachmenttype_codice !== 'LTE_FIRM_CONTR_PROT');
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
                  hideExpression:(model: any, formState: any) => {               
                    return (formState.model.digitale_controparte.attachment1.attachmenttype_codice !== 'NESSUN_DOC');
                  },                            
                },
              ],
            },      
          ],
        },
        {
          fieldGroupClassName: 'row',
          key: 'attachment2',
          fieldGroup: [
            {
              key: 'attachmenttype_codice',
              type: 'select',
              className: "col-md-5",
              defaultValue: 'CONV_FIRM_CONTR',
              templateOptions: {
                options: [{ stipula_type: 'ditta', codice: 'CONV_FIRM_CONTR', descrizione: 'Convenzione firmata dalla controparte' }],
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
                accept: 'application/pdf', //.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,            
                onSelected: (selFile, field) => { this.onSelectCurrentFile(selFile, field); }
              },

            },

          ],
        },
      ],
    },  
  ]

  onSelectCurrentFile(currentSelFile, field: FormlyFieldConfig) {


    let currentAttachment = field.parent.model; //field.formControl.parent.value;
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

      if (!currentAttachment.filevalue) {
        this.isLoading = false;
        return;
      }
      this.isLoading = false;
    }
    reader.readAsArrayBuffer(currentSelFile);


  }

  constructor(protected service: ApplicationService, protected route: ActivatedRoute, protected router: Router, 
      private injector: Injector, 
      protected location: Location,
      protected confirmationDialogService: ConfirmationDialogService) {

    super(route, router, location)
    this.isLoading = false;
  }

  ngOnInit() {
    this.route.params.subscribe(params => {
      if (params['id']) {
        this.model.convenzione_id = params['id'];                   
        this.options.formState.disabled_covenzione_id = true;
      };
    });
  }



  checkLabelButton() {     
      this.labelButton = "Registra";     
  }


  onSubmit() {
    if (this.form.valid) {
      this.isLoading = true;
      
      var tosubmit = this.mergeDeep(this.model,this.form.value);

      this.service.registrazioneSottoscrizione(tosubmit, true).subscribe(
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
        });
    }
  }

    /**
     * Simple is object check.
     * @param item
     * @returns {boolean}
     */
    isObject(item) {
      return (item && typeof item === 'object' && !Array.isArray(item) && item !== null);
    }
    
    /**
     * Deep merge two objects.
     * @param target
     * @param source
     */
    mergeDeep(target, source) {
      if (this.isObject(target) && this.isObject(source)) {
        Object.keys(source).forEach(key => {
          if (this.isObject(source[key])) {
            if (!target[key]) Object.assign(target, { [key]: {} });
            this.mergeDeep(target[key], source[key]);
          } else {
            Object.assign(target, { [key]: source[key] });
          }
        });
      }
      return target;
    }

}



