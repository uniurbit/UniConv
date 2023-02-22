import { Component, OnInit, Injector, Input } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { Subject } from 'rxjs';
import { FormGroup } from '@angular/forms';
import { FileAttachment } from '../../convenzione';
import { encode, decode } from 'base64-arraybuffer';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { ApplicationService } from '../../application.service';
import { map, catchError, tap, startWith, takeUntil, publishReplay, refCount, filter } from 'rxjs/operators';
import { StartEditingCellParams } from 'ag-grid-community';

@Component({
  selector: 'app-uploadfile',
  template: `
  <ngx-loading [show]="isLoading" [config]="{ backdropBorderRadius: '0px' }"></ngx-loading>
  <div class="modal-header">
  <h4 class="modal-title" id="modal-basic-title">Caricamento file</h4>
  <button type="button" class="close" aria-label="Close" (click)="activeModal.dismiss()">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
<form [formGroup]="form">
  <formly-form [model]="modelfile" [fields]="fields" [form]="form">

  </formly-form>
</form>

</div>
<div class="modal-footer">
  <button type="button" class="btn btn-primary col-md-2"  [disabled]="!form.valid"  (click)="addfile()">Carica</button>
  <button type="button" class="btn btn-outline-dark"   (click)="activeModal.dismiss()">Annulla</button>
</div>
  `,
  styles: []
})

export class UploadfileComponent implements OnInit {

  @Input()
  model_id: number;
  
  form = new FormGroup({});
  modelfile = { filename: '', attachmenttype_codice: ''};
  currentSelFile: File;
  nrecord: string;
  emission_date: Date;
  isLoading: boolean;


  public static codeWithDateNumber: Array<string> = ['DDD', 'DCD', 'DR', 'DRU', 'DSA', 'DCA','ALLEGATO_BOLLI'];
  public static withProtocol: Array<string> = ['LTE_FIRM_CONTR_PROT', 'LTU_FIRM_ENTRAMBI_PROT', 'LTE_FIRM_ENTRAMBI_PROT'];


  public static fileToBeUploaded = UploadfileComponent.codeWithDateNumber.concat(UploadfileComponent.withProtocol).concat(['PR','DA']);

  fields: FormlyFieldConfig[] = [{
    fieldGroup: [
        {
          key: 'attachmenttype_codice',
          type: 'select',          
          templateOptions: {
            options: this.service.getAttachemntTypes().pipe(
              map(x => 
                x.filter(element => UploadfileComponent.fileToBeUploaded.includes(element.codice))
              )                           
            ), 
            valueProp: 'codice',
            labelProp: 'descrizione_compl',
            label: 'Tipologia allegato',
            required: true,
          }
        },
        {
          key: 'filename',
          type: 'fileinput',          
          templateOptions: {
            label: 'Scegli documento',
            type: 'input',
            placeholder: 'Scegli file documento',
            accept: '.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf,.p7m,application/pkcs7-mime,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            required: true,
            onSelected: (selFile) => { this.onSelectCurrentFile(selFile) }
          },
          hideExpression: (model, formState, field) => {
            if (model)
              return UploadfileComponent.withProtocol.includes(model.attachmenttype_codice); 
            return true;
          },
          expressionProperties: {
            'templateOptions.required': (model, formState, field) => {
              if (model)
                return !['DSA', 'DCA'].includes(model.attachmenttype_codice); 
              return true;
            }
          }
        },
        {
          key: 'num_prot',
          type: 'external',          
          templateOptions: {
            label: 'Numero di protocollo',
            required: true,      
            type: 'string',
            entityName: 'documento',
            entityLabel: 'Documenti',
            codeProp: 'num_prot',
            descriptionProp: 'oggetto',
            descriptionFunc: (data) => {
              if (data && data.oggetto){
                this.nrecord = data.nrecord;
                this.emission_date = data.data_prot;
                return data.oggetto
              }
              return '';
            },
            isLoading: false,  
            //rules: [{value: "arrivo", field: "doc_tipo", operator: "="}],                       
          },      
          hideExpression: (model, formState) => {
            if (model)
              return !UploadfileComponent.withProtocol.includes(model.attachmenttype_codice); 
            return true;
          },
        },       
        {
          fieldGroupClassName: 'row',           
          fieldGroup: [
            {
              key: 'docnumber',
              type: 'input',
              className: "col-md-4",
              templateOptions: {
                label: 'Numero',
                required: true,                               
              },
            },
            {
              key: 'emission_date',
              type: 'datepicker',
              className: "col-md-8",
              templateOptions: {
                label: 'Data',
                required: true,                               
              },
            },
          ],  
          hideExpression: (model: any, formState: any) => {
            if (model)
              return !UploadfileComponent.codeWithDateNumber.includes(model.attachmenttype_codice); 
            return true;
         },           
        },
      
      ]
  }];


  constructor(public activeModal: NgbActiveModal, private service: ApplicationService) {  
  }  


  close(){    
    this.activeModal.close(null);
  }

  ngOnInit() {
  }

  onSelectCurrentFile(selFile) {
    this.currentSelFile = selFile;
  }
  
  addfile() {
    this.isLoading = true;
    const currentAttachment: FileAttachment = {
      model_id: this.model_id,
      model_type: 'App\\Convenzione',
      filename: this.currentSelFile ? this.currentSelFile.name : null,      
      attachmenttype_codice: this.form.get('attachmenttype_codice').value,      
    }
    const mergeCurrentAttachment = {...currentAttachment, ...this.form.value }; 
    console.log(mergeCurrentAttachment);
    //carica il file se non c'è numero di protocollo ed esiste il filename
    if (!mergeCurrentAttachment.num_prot && mergeCurrentAttachment.filename) {
      const reader = new FileReader();
      reader.onload = (e: any) => {
        mergeCurrentAttachment.filevalue = encode(e.target.result);
        this.callUpdate(mergeCurrentAttachment);
      }
      reader.readAsArrayBuffer(this.currentSelFile);       
    }else{
      mergeCurrentAttachment.filename = null;      
      if (mergeCurrentAttachment.num_prot){
        mergeCurrentAttachment.nrecord = this.nrecord;
        mergeCurrentAttachment.emission_date = this.emission_date;
      }
      this.callUpdate(mergeCurrentAttachment);
    }
  }

  private callUpdate(currentFile: FileAttachment) {
    this.isLoading = true;
    this.service.uploadFile(currentFile).subscribe((data) => {               
      this.isLoading = false;
      this.activeModal.close(data);      
    });
  }


}
