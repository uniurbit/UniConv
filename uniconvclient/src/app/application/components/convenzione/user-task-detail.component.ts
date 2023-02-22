import { Component, OnInit, Input } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { FormlyFieldConfig, FormlyFormOptions } from '@ngx-formly/core';
import { UserTaskService } from '../../usertask.service';

@Component({
  selector: 'app-user-task-detail',
  template: `
  <ngx-loading [show]="isLoading" [config]="{ backdropBorderRadius: '0px' }"></ngx-loading>
  <div class="btn-toolbar mb-4" role="toolbar">
  <div class="btn-group">    
    <button class="btn btn-outline-primary rounded-lg" [disabled]="!form.valid || !form.dirty" (click)="onSubmit()" >              
        <span class="oi oi-arrow-top"></span>  
        <span class="ml-2">{{ 'btn_salva' | translate }}</span>        
    </button>
    <button class="btn btn-outline-primary rounded-lg ml-1" [disabled]="!form.dirty">
        <span class="oi oi-reload iconic" title="reload" aria-hidden="true" ></span>
        <span class="ml-2">Ricarica</span>
    </button>   
  </div>
</div>
<form *ngIf='model' [formGroup]="form">
  <formly-form  [model]="model" [fields]="fields" [form]="form" [options]="options">  
  </formly-form> 
</form>      
  `,
  styles: []
})
export class UserTaskDetailComponent implements OnInit {

  isLoading: boolean = false;
  @Input()
  model: any;
  options: FormlyFormOptions = {
    formState: {
      isLoading: false,
    },
  };
  form = new FormGroup({});
  fields: FormlyFieldConfig[] = [
    {
      className: 'section-label',
      template: '<h5>Dettaglio attività</h5>',
    },
    {
      fieldGroupClassName: 'row',
      fieldGroup:
        [
          {
            className: 'col-md-6',
            type: 'input',
            key: 'workflow_place',
            templateOptions: {
              label: 'Tipo',
              disabled: true,
            },
          },
          {
            className: 'col-md-6',
            type: 'select',
            key: 'transition',
            defaultValue: 'self_transition',
            templateOptions: {
              label: 'Modifica lo stato',
              placeholder: '',
              options: []
            },
            hooks: {
              onInit: (fieldInit) => {
                fieldInit.templateOptions.options = this.service.getNextPossibleActionsFromTask(this.model.id);
              },
            },
          },
        ],
    },
    {
      fieldGroup:
        [
          {            
            type: 'input',
            key: 'email',
            templateOptions: {
              label: "Assegnata",
              disabled: true,
            },
          },
          {           
            type: 'input',
            key: 'subject',
            templateOptions: {
              label: 'Oggetto',
              disabled: true,
            },
          },
          {           
            type: 'textarea',
            key: 'description',
            templateOptions: {
              label: 'Descrizione',
              rows: 2,
            },
          },
        ]
    }
  ];

  constructor(private service: UserTaskService) { }

  ngOnInit() {
  }

  onSubmit() {
    if (this.form.valid) {
      this.isLoading = true;
      var tosubmit = { ...this.model, ...this.form.value };
      this.service.update(tosubmit, tosubmit.id).subscribe(
        result => {       
          this.options.resetModel(result);
          this.isLoading = false;
        },
        error => {
          this.isLoading = false;          
          console.log(error)
        });
    }
  }

}
