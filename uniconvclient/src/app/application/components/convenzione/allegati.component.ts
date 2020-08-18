import { Component, OnInit } from '@angular/core';
import { Subject } from 'rxjs';
import { FormGroup, FormArray } from '@angular/forms';
import { FormlyFieldConfig, FormlyFormOptions } from '@ngx-formly/core';
import { ApplicationService } from '../../application.service';
import { FileAttachment } from '../../convenzione';
import { encode, decode } from 'base64-arraybuffer';

@Component({
  selector: 'app-allegati',
  template: `
  <ngx-loading [show]="isLoading" [config]="{ backdropBorderRadius: '0px' }"></ngx-loading>

  <h4>Allegati</h4>
  <form *ngIf='model' [formGroup]="form" >
    <formly-form  [model]="model" [fields]="fields" [form]="form" [options]="options">  
    </formly-form> 
  </form>

  <br>
  <br>
  <br>
  <br>

  <p>Form value: {{ form.value | json }}</p>
  <p>Model value: {{ model | json }}</p>
  `,
  styles: []
})

//ng g c application/components/convenzione/allegati -s true --spec false -t true --flat true

export class AllegatiComponent implements OnInit {

  onDestroy$ = new Subject<void>();
  form = new FormGroup({});
  model: any;
  currentSelFile: File;
  isLoading: boolean;
 

  fields: FormlyFieldConfig[] = [
    {
      className: 'section-label',
      template: '<h5>Dati convenzione</h5>',
    },
    {
      key: 'convenzione',
      type: 'externalobject',
      templateOptions: {
        label: 'Convenzione',
        type: 'string',
        entityName: 'application',
        entityLabel: 'Convenzione',
        entityPath: 'home/convenzioni',
        codeProp: 'id',
        descriptionProp: 'descrizione_titolo',
        isLoading: false,
        required: true,
      },
    },
    {
      fieldGroupClassName: 'row',
      templateOptions:{ label: ''},
      fieldGroup: [
        {
          key: 'filename',
          type: 'fileinput',
          className: "col-md-5",
          templateOptions: {
            label: 'Scegli documento',
            type: 'input',
            placeholder: 'Scegli file tipo pdf',
            accept: 'application/pdf',
            required: true,
            onSelected: (selFile) => { this.onSelectCurrentFile(selFile) }
          },
        },
        {
          key: 'attachmenttype_codice',
          type: 'select',
          className: "col-md-5",
          templateOptions: {
            options: this.service.getAttachemntTypes(), //TODO tipi di allegati
            valueProp: 'codice',
            labelProp: 'descrizione',
            label: 'Tipologia allegato',
            required: true,
          }
        },
        {
          type: 'button',
          className: "col-md-2 d-flex align-items-start mt-4 pt-2",          
          templateOptions: {
            text: 'Carica',            
            btnType: 'primary',            
            onClick: ($event, model) => this.add(),
          },
          expressionProperties: {
            'templateOptions.disabled':(model: any, formState: any) => {
              return !this.form.valid
            },
          },
        },
      ],
     
    },  

    {
      key: 'attachments',
      type: 'repeat',
      wrappers: ['accordion'],     
      templateOptions: {
        btnHidden: true,
        label: 'Gestione allegati',
      },   
      hideExpression: (model: any, formState: any) => {
         return this.model.attachments.length == 0
      },   
      fieldArray: {
        fieldGroupClassName: 'row',
        fieldGroup: [
          {
            className: 'col-md-4',
            type: 'input',
            key: 'filename',
            templateOptions: {
              label: "Nome dell'allegato",
              disabled: true,
              required: true,
            },
          },
          {
            type: 'input',
            key: 'attachmenttype.descrizione',
            className: 'col-md-4',
            templateOptions: {
              label: 'Tipologia',
            },
          },
        ],      
      }
    }
  ]

  options: FormlyFormOptions = {
    formState: {
      isLoading: false,
    },
  };

  attachemntsField: FormlyFieldConfig;

  constructor(private service: ApplicationService) { }

  ngOnInit() {
    this.model = {
      convenzione: { id: null, convenzione: null },
      attachments: []
    }
    this.attachemntsField = this.fields.find((el) => el.key == 'attachments')
  }

  onSelectCurrentFile(selFile) {

    this.currentSelFile = selFile;
  }

  add() {
    let currentAttachment: FileAttachment = {
      model_id: this.model.convenzione.id,
      model_type: 'convenzione',
      filename: this.currentSelFile.name,
      attachmenttype_codice: this.model.attachmenttype_codice,
    }
    const reader = new FileReader();
    reader.onload = (e: any) => {
      currentAttachment.filevalue = encode(e.target.result);
      this.callUpdate(currentAttachment);
    }
    reader.readAsArrayBuffer(this.currentSelFile); 
  }

  private callUpdate(currentFile: FileAttachment) {
    this.isLoading = true;
    this.service.uploadFile(currentFile).subscribe((data) => {

      if (data){
        let model = {
          convenzione: this.model.convenzione,
          attachments: this.model.attachments.concat(data)
        }
        this.options.resetModel(model);
      }
      this.isLoading = false;
    });
  }

}
