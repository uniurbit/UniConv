import { Component, OnInit, OnDestroy, HostListener } from '@angular/core';
import { FormlyFieldConfig, FormlyFormOptions } from '@ngx-formly/core';
import { StepType, CoreSevice } from 'src/app/shared';
import { ApplicationService } from '../application.service';
import { ActivatedRoute, Router, NavigationStart } from '@angular/router';
import { Convenzione, FileAttachment, Owner, ConvenzioneDipartimentale, convenzioneFrom, rinnovoType } from '../convenzione';
import { FormGroup, FormControl, ValidationErrors, FormArray } from '@angular/forms';
import { encode, decode } from 'base64-arraybuffer';
import { AuthService } from 'src/app/core';
import { InfraMessageType } from 'src/app/shared/message/message';
import { takeUntil, startWith, tap, filter, map, distinct, distinctUntilChanged } from 'rxjs/operators';
import { Subject, Observable } from 'rxjs';
import { PDFJSStatic } from 'pdfjs-dist';
import { modelGroupProvider } from '@angular/forms/src/directives/ng_model_group';
import { ChangeDetectorRef } from '@angular/core';
import ControlUtils from 'src/app/shared/dynamic-form/control-utils';
import { ConfirmationDialogService } from 'src/app/shared/confirmation-dialog/confirmation-dialog.service';


const PDFJS: PDFJSStatic = require('pdfjs-dist');

//ng g c application/pages/test-tab -s true  --spec false --flat true

@Component({
  selector: 'app-multistep-schematipo',
  template: `
  <ngx-loading [show]="isLoading" [config]="{ backdropBorderRadius: '4px' }"></ngx-loading>
  <div class="btn-toolbar mb-4" role="toolbar">
             
      <button class="btn btn-outline-primary rounded-lg ml-1"  [disabled]="!form.valid || !form.dirty" (click)="onSubmit()" >              
        <span class="oi oi-arrow-top"></span>  
        <span class="ml-2">Aggiorna</span>              
      </button> 
      <button type="button" class="btn btn-outline-primary rounded-lg ml-1"  (click)="onValidate()" >              
        <span class="oi oi-flash"></span>  
        <span class="ml-2">Valida</span>              
      </button>  
      <div class="btn-group btn-group">   
      <button  class="btn btn-outline-primary rounded-lg ml-1" (click)="onNew()" >              
        <span class="oi oi-document"></span>
        <span class="ml-2">Nuovo</span>
      </button>    
    </div>
  </div>
  
  <form *ngIf='model' [formGroup]="form" >
    <formly-form  [model]="model" [fields]="fieldtabs" [form]="form" [options]="options">      
    </formly-form> 
  </form>
  `,
  styles: []
})

export class MultistepSchematipoComponent implements OnInit, OnDestroy {


  public static DECRETO_DIRETTORIALE = 'DDD';
  public static DELIBERA_CONSIGLIO_DIPARTIMENTO = 'DCD';
  public static DECRETO_RETTORALE = 'DR';
  public static DOC_APP = 'DA';
  public static PROSPETTO = 'PR';
  public static CONV_BOZZA = 'CB';

  private prefix = 'multistep';

  onDestroy$ = new Subject<void>();
  fieldtabs: FormlyFieldConfig[];

  form = new FormGroup({});
  model: ConvenzioneDipartimentale;

  isLoading: boolean;

  options: FormlyFormOptions;

  mapAttachment: Map<string, FileAttachment> = new Map<string, FileAttachment>();

  constructor(private confirmationDialogService: ConfirmationDialogService, private service: ApplicationService, public authService: AuthService, private router: Router,  private cdRef : ChangeDetectorRef) {

    PDFJS.disableWorker = true;
   
    this.model = {
      schematipotipo: 'schematipo',
      transition: 'self_transition',
      user_id: authService.userid,
      id: null,
      descrizione_titolo: '',
      dipartimemto_cd_dip: '',
      nominativo_docente: '',
      emittente: '',
      user: { id: authService.userid, name: authService.username },
      dipartimento: { cd_dip: null, nome_breve: '' },
      stato_avanzamento: null,
      convenzione_type: 'TO',
      tipopagamento: { codice: null, descrizione: '' },
      azienda: { id: null, denominazione: '' },
      unitaorganizzativa_uo: '',
      unitaorganizzativa_affidatario: '',
      attachments: [],    
      aziende:[],  
      convenzione_from: convenzioneFrom.dip,
      rinnovo_type: rinnovoType.non_rinnovabile,
    };

    if (this.getStorageModel()){
      
      let app = JSON.parse(this.getStorageModel());
      this.checkHistory(app);
      this.model = app; 
      this.setStorageModel();
    }else{
      if (this.checkHistory(this.model))
        this.setStorageModel();
    }

    this.options = {
      formState: {
        isLoading: false,
        model: this.model,
      },
    };

    this.fieldtabs = [          
      {
        type: 'tabinfra',
        templateOptions:{
          onSubmit: () => this.onSubmit(),
        },
        fieldGroup: [
          {
            wrappers: ['accordioninfo'],
            fieldGroup: [
              {
                fieldGroupClassName: 'row',
                fieldGroup: [{
                  key: 'schematipotipo',
                  wrappers: ['form-field-horizontal'],
                  className: 'col-md-6',
                  type: 'select',
                  templateOptions: {
                    label: 'Approvazione organi centrali',
                    options: [
                      { label: 'No', value: 'schematipo' },
                      { label: 'Si', value: 'daapprovare' },
                    ],
                  },
                  lifecycle: {
                    onInit: (form, field) => {
                      const tabs = this.fieldtabs.find(f => f.type === 'tabinfra');
                      const tabappr = tabs.fieldGroup[2];
                      field.formControl.valueChanges.subscribe(x => {
                        if (x == 'schematipo') {
                          tabappr.templateOptions.hidden = true;
                        }
                        else {
                          tabappr.templateOptions.hidden = false;
                        }
                      });
                    }
                  }
                }],
              },
            ].concat(
              this.service.getInformazioniDescrittiveFields(this.model).map(x => {
                if (x.key == 'user') {                  
                  setTimeout(()=> {
                    x.templateOptions.disabled = true;
                  }, 0);
                }
                return x;
              })),
            templateOptions: {
              label: 'Informazioni descrittive'
            },
          },
          {
            wrappers: ['accordioninfo'],
            fieldGroup: [          
              {
                fieldGroupClassName: 'row',
                fieldGroup: [
                  {
                    key: 'file_CD_type',
                    type: 'select',
                    defaultValue: 'DCD',
                    className: "col-md-6",
                    templateOptions: {
                      label: 'Tipo documento di approvazione',
                      required: true,
                      options: [
                        { value: 'DCD', label: 'Delibera Consiglio di Dipartimento' },
                        { value: 'DDD', label: 'Decreto del direttore di dipartimento' },
                      ]
                    },
                    expressionProperties: {
                      'templateOptions.disabled': (model: any, formState: any) => {                        
                          return !(formState.model.file_CD == null || formState.model.file_CD == '');
                      },
                    },            
                  },
                  {
                    key: 'file_CD',
                    type: 'fileinput',
                    className: "col-md-6",                    
                    templateOptions: {
                      label: 'Documento di approvazione (formato pdf)',
                      description: 'Allegare in formato pdf la versione della delibera o della disposizione',                      
                      type: 'input',
                      placeholder: 'Scegli documento',
                      accept: 'application/pdf',                      
                      required: true,                                                                  
                      onSelected: (selFile) => {
                        this.onSelectCurrentFile(selFile, this.model['file_CD_type']);
                      },                                            
                    },
                    validators: {                        
                      formatpdf: {
                        expression: (c) => {
                         return /.+\.([pP][dD][fF])/.test(c.value);
                        },
                        message: (error, field: FormlyFieldConfig) =>  `Formato non consentito`,
                      }
                    }
                  },
                ],
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
                    key: 'data_emissione',
                    type: 'datepicker',
                    className: "col-md-8",
                    templateOptions: {
                      label: 'Data',
                      required: true,                               
                    },
                  },
                ]
              },           
              {
                key: 'file_DA',
                type: 'fileinput',
                templateOptions: {
                  label: 'Documento appoggio (formato word)',
                  type: 'input',
                  description: 'Versione editabile (file word) della delibera o della disposizione',
                  placeholder: 'Scegli documento',
                  tooltip: {
                    content: 'Versione editabile (file word) della delibera o della disposizione'
                  },
                  accept: '.doc,.docx ,application/msword',
                  onSelected: (selFile) => {
                    this.onSelectCurrentFile(selFile, MultistepSchematipoComponent.DOC_APP)
                  }
                },                
                validators: {                        
                  formatpdf: {
                    expression: (c) => {
                     return c.value ? /.+\.([dD][oO][cC][xX]?)/.test(c.value) : true;
                    },
                    message: (error, field: FormlyFieldConfig) =>  `Formato non consentito`,
                  }
                }
              },
              {
                key: 'file_PR',
                type: 'fileinput',
                className: "col-md-5",
                templateOptions: {
                  label: 'Prospetto ripartizione costi e proventi',
                  type: 'input',
                  placeholder: 'Scegli documento',
                  accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                  onSelected: (selFile) => {
                    this.onSelectCurrentFile(selFile, MultistepSchematipoComponent.PROSPETTO)
                  }
                },
                validators: {                        
                  formatpdf: {
                    expression: (c) => {
                     return c.value ? /.+\.([xX][lL][sS][xX])$/.test(c.value) : true;
                    },
                    message: (error, field: FormlyFieldConfig) =>  `Formato non consentito`,
                  }
                }
              },
            ],
            templateOptions: {
              label: 'Allegati'
            }
          },
          {
            wrappers: ['accordioninfo'],
            fieldGroup: [
              {
                key: 'unitaorganizzativa_affidatario',
                type: 'select',
                hideExpression: 'formState.model.schematipotipo == "schematipo"',
                templateOptions: {
                  label: 'Ufficio affidatario procedura',
                  required: true,                 
                  options: this.service.getValidationOffices(),
                  valueProp: 'uo',
                  labelProp: 'descr',
                },
              },              
              {
                key: 'respons_v_ie_ru_personale_id_ab',
                type: 'select',
                hideExpression: 'formState.model.schematipotipo == "schematipo"',                
                templateOptions: {
                  label: 'Responsabile ufficio',
                  valueProp: 'id',
                  labelProp: 'descr',   
                  required: true,                    
                },
                hooks: {
                  onInit: (field) => {
                    field.form.get('unitaorganizzativa_affidatario').valueChanges.pipe(
                      takeUntil(this.onDestroy$),    
                      distinctUntilChanged(),                  
                      filter(ev => ev !== null),
                      tap(uo => {                                                                   
                        field.formControl.setValue('');
                        field.templateOptions.options = this.service.getPersonaleUfficio(uo).pipe(
                          map(items => {
                            return items.filter(x => ApplicationService.isResponsabileUfficio(x.cd_tipo_posizorg));
                          }),  
                          tap(items => {
                            if (items[0]){
                              field.formControl.setValue(items[0].id); 
                            }
                          }),                                                  
                        );
                      }),
                    ).subscribe();
                  }
                }               
              },
              {
                key: 'assignments',
                type: 'repeat',          
                hideExpression: 'formState.model.schematipotipo == "schematipo"',                         
                templateOptions: {        
                  required: true,                       
                  label: 'Operatori',                                                                                        
                },   
                validators: {
                  unique: {
                    expression: (c) => {           
                      if (c.value)  {                              
                        var valueArr = c.value.map(function(item){ return item.v_ie_ru_personale_id_ab }).filter(x => x != null );
                        var isDuplicate = valueArr.some(function(item, idx){ 
                            return valueArr.indexOf(item) != idx 
                        });              
                        return !isDuplicate;
                      }
                      return true;
                    },
                    message: (error, field: FormlyFieldConfig) => `Nome ripetuto`,
                  },                                
                  atleastone: {
                    expression: (c) => {
                      if (c.value) {
                        if (c.value.length < 1)
                          return false;              
                      }else {
                        return false;
                      }
                      return true;
                    },
                    message: (error, field: FormlyFieldConfig) => `Inserire almeno un operatore`,
                  },                                        
                },               
                hooks: {
                  onInit: (field) => {
                    field.form.get('unitaorganizzativa_affidatario').valueChanges.pipe(
                      takeUntil(this.onDestroy$),    
                      distinctUntilChanged(),                                        
                      filter(ev => ev !== null),
                      tap(uo => {  
                          this.model.unitaorganizzativa_affidatario = uo;
                          field.templateOptions.removeAll();                                                         
                      })).subscribe();
                    }
                  },          
                fieldArray: {                  
                  fieldGroupClassName: 'row',
                  fieldGroup: [
                  {
                    key: 'v_ie_ru_personale_id_ab',
                    type: 'select',
                    className: "col-md-8",
                    templateOptions: {
                      label: 'Operatore attività',
                      valueProp: 'id',
                      labelProp: 'descr',  
                      required: true,                     
                    },                    
                    lifecycle: {
                      onInit: (form, field, model) => {                                              
                        field.templateOptions.options = this.service.getPersonaleUfficio(this.model.unitaorganizzativa_affidatario).pipe(                    
                        );                      
                      },
                    },
                  },
                  ],   
                },
              },
              {
                key: 'description',
                type: 'textarea',
                hideExpression: 'formState.model.schematipotipo == "schematipo"',    
                templateOptions: {
                  label: 'Note',                  
                  rows: 5,
                },
                expressionProperties: {
                  'templateOptions.disabled': '!model.respons_v_ie_ru_personale_id_ab',
                },
              }
            ],
            templateOptions: {
              label: 'Approvazione',
              hidden: true,
            },              
          }
        ]
      }];
  }

  checkHistory(model){
    const entity = history.state ? history.state.entity : null;
    if (entity){
      if (model.aziende.length > 0)
      {
        model.aziende = model.aziende.filter(x=>x !== (undefined || null || '') && x.id);
      }
      this.pushToArray(model.aziende,entity);
      return true;
    }   
    return false;
  }

  pushToArray(arr, obj) {
    const index = arr.findIndex((e) => e.id === obj.id);

    if (index === -1) {
        arr.push(obj);
    } else {
        arr[index] = obj;
    }
  }

  getStorageModel(){
    if (this.prefix){
      return sessionStorage.getItem(this.prefix+'_model');
    }     
    return null;
  }

  setStorageModel(){
    if (this.prefix){
      sessionStorage.setItem(this.prefix+'_model',JSON.stringify(this.model));
    } 
  }


  public onValidate() {
    ControlUtils.validate(this.fieldtabs[0]);
  } 


  render_page(pageData) {

    let render_options = {
      //replaces all occurrences of whitespace with standard spaces (0x20). The default value is `false`.
      normalizeWhitespace: false,
      //do not attempt to combine same line TextItem's. The default value is `false`.
      disableCombineTextItems: false
    }

    return pageData.getTextContent(render_options)
      .then(function (textContent) {
        let lastY, text = '';
        for (let item of textContent.items) {
          if (lastY == item.transform[5] || !lastY) {
            text += item.str;
          }
          else {
            text += '\n' + item.str;
          }
          lastY = item.transform[5];
        }
        return text;
      });
  }

  async parsePdf(data){
    let text = '';
    await PDFJS.getDocument({ data: data }).then(async (doc) => {
      let counter: number = 1;
      counter = counter > doc.numPages ? doc.numPages : counter;

      for (var i = 1; i <= counter; i++) {
        let pageText = await doc.getPage(i).then(pageData => this.render_page(pageData));
        text = `${text}\n\n${pageText}`;      
      }    
            
      let number = text.match(/[d|D]elibera n.?\s?([A-Za-z0-9\/]*)\s*\n/);
      if (number && number[1]){
        this.form.get('docnumber').setValue(number[1]);
      
      }
      let data_emissione = text.match(/[r|R]iunione del giorno\s([0-9]{2}\/[0-9]{2}\/[0-9]{4})\s?/);
      if (data_emissione && data_emissione[1]){
        let converted = data_emissione[1].replace(/\//g,'-');
        this.form.get('data_emissione').setValue(converted);
      }      
      this.isLoading = false;
    });
  }

  onSelectCurrentFile(currentSelFile: File, typeattachemnt: string) {

    if (currentSelFile == null) {
      //caso di cancellazione
      this.mapAttachment.delete(typeattachemnt);
      return;
    }
    
    this.isLoading = true;
    let currentAttachment: FileAttachment = {
      model_type: 'convenzione',
      filename: currentSelFile.name,
      attachmenttype_codice: typeattachemnt,
    } 
    
    const reader = new FileReader();   

    reader.onload = async (e: any) => {
      this.isLoading = true;
      currentAttachment.filevalue = encode(e.target.result);
      
      if (currentSelFile.name.search('pdf')>0){
        try {
          await this.parsePdf(e.target.result);     
        } catch (error) {
          this.isLoading = false;
        }
      }

      if (!currentAttachment.filevalue) {
        this.isLoading = false;
        return;
      }

      this.mapAttachment.set(currentAttachment.attachmenttype_codice, currentAttachment);
      this.isLoading = false;
    }
    reader.readAsArrayBuffer(currentSelFile);

  }

  onNew(){
    this.confirmationDialogService.confirm('Conferma', 'Vuoi procedere con l\'operazione?' )
    .then((confirmed) => {
      if (confirmed) {
        this.form.markAsUntouched();
        sessionStorage.removeItem(this.prefix+'_model');
        this.model = {
          schematipotipo: 'schematipo',
          transition: 'self_transition',
          user_id: this.authService.userid,
          id: null,
          descrizione_titolo: '',
          dipartimemto_cd_dip: '',
          nominativo_docente: '',
          emittente: '',
          user: { id: this.authService.userid, name: this.authService.username },
          dipartimento: { cd_dip: null, nome_breve: '' },
          stato_avanzamento: null,
          convenzione_type: 'TO',
          tipopagamento: { codice: null, descrizione: '' },
          azienda: { id: null, denominazione: '' },
          unitaorganizzativa_uo: '',
          unitaorganizzativa_affidatario: '',
          attachments: [],    
          aziende:[],  
          convenzione_from: convenzioneFrom.dip,
          rinnovo_type: rinnovoType.non_rinnovabile,
        };
      }
    });
  
  }

  ngOnInit() {
  }

  ngOnDestroy(): void {
    this.onDestroy$.next();
    this.onDestroy$.complete();
    if (this.form.touched){
      this.setStorageModel();
    }
  
  }

  onSubmit() {
    if (this.form.valid) {
      this.isLoading = true;
      var tosubmit: Convenzione = { ...this.model, ...this.form.value };

      var file = this.mapAttachment.get(MultistepSchematipoComponent.DELIBERA_CONSIGLIO_DIPARTIMENTO);
      if (file == null){
        file = this.mapAttachment.get(MultistepSchematipoComponent.DECRETO_DIRETTORIALE);
      }
      file.docnumber = this.model['docnumber'];
      file.emission_date = this.model['data_emissione'];

      //aggiungo tutti gli allegati      
      tosubmit.attachments = [];
      tosubmit.attachments.push(...Array.from<FileAttachment>(this.mapAttachment.values()));     

      this.service.createSchemaTipo(tosubmit, true).subscribe(
        result => {
          //this.options.resetModel(result);
          this.form.markAsUntouched();
          sessionStorage.removeItem(this.prefix+'_model');
          this.isLoading = false;
          this.router.navigate(['home/dashboard/dashboard1']);  
          //this.router.navigate(['home/convenzioni/' + result.id]);
        },
        error => {
          this.isLoading = false;
          console.log(error);
        }

      );
    }
  }

  onAziendaRicerca(){
    this.router.navigate(['home/aziendeloc']);     
  }

}
