import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig, Field } from '@ngx-formly/core';
import { BaseEntityComponent } from 'src/app/shared';
import { ActivatedRoute, Router } from '@angular/router';
import { encode, decode } from 'base64-arraybuffer';
import {Location} from '@angular/common';
import ControlUtils from 'src/app/shared/dynamic-form/control-utils';
import { PDFJSStatic } from "pdfjs-dist";
import { ConfirmationDialogService } from 'src/app/shared/confirmation-dialog/confirmation-dialog.service';
import { ApplicationService } from 'src/app/application/application.service';
import { BolloRepertoriazioneComponent } from '../../bollorepertoriazione.component';

@Component({
  selector: 'app-registrazione-bollo-repertoriazione',
  templateUrl: './registrazione-bollo-repertoriazione.component.html',
  styles: []
})

export class RegistrazioneBolloRepertoriazioneComponent extends BaseEntityComponent {

  public static STATE = 'firmato';
  public static WORKFLOW_ACTION: string = 'repertorio'; //TRASITION
  public static ABSULTE_PATH: string = 'home/registrabollorepertoriazione';

  fields: FormlyFieldConfig[] = [    
    {
      className: 'section-label',
      template: '<h5></h5>',
    },
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
                options:  [
                  { codice: 'DOC_BOLLATO_FIRMATO', descrizione: 'Convenzione firmata già repertoriata' },                  
                ],
                valueProp: 'codice',
                labelProp: 'descrizione',
                label: 'Tipo documento',               
              },                       
            },
            {
              key: 'doc',
              type: 'externalobject',
              className: "col-md-7",
              templateOptions: {
                label: 'Numero di repertorio',
                required: true,      
                type: 'string',
                entityName: 'repertorio',
                entityLabel: 'Documenti',
                codeProp: 'numero',
                descriptionProp: 'oggetto',
                isLoading: false,
                //rules: [{ value: "arrivo", field: "doc_tipo", operator: "=" }],
              },           
            }, 
          ],
        },
      ],  
    },
  ];

  constructor(protected service: ApplicationService, protected route: ActivatedRoute, protected router: Router, protected location: Location,
    protected confirmationDialogService: ConfirmationDialogService) {
    super(route, router, location)
    this.isLoading = false;
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
              setTimeout(
                ()=> {
                    this.fields.find(x=> x.key == 'convenzione').templateOptions.init(result);                                            
                });
            this.isLoading=false;
            }
          }
        );
        this.options.formState.disabled_covenzione_id = true;
      };
    });
  }

  updateStipula(value){
    if (value){
      this.form.get('stipula_format').setValue(value);
    }
  }

  
  onSubmit() {
    if (this.form.valid) {
      this.isLoading = true;
      var tosubmit = { ...this.model, ...this.form.value };
      tosubmit.attachment1 = {...this.model.attachment1, ...this.form.value.attachment1};
      tosubmit.attachment1.doc = {...this.model.attachment1.doc, ...this.form.value.attachment1.doc }
      
      tosubmit.transition = RegistrazioneBolloRepertoriazioneComponent.STATE;
      this.service.registrazioneBolloRepertoriazione(tosubmit,true).subscribe(
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
