import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig, FormlyFormOptions } from '@ngx-formly/core';
import { FormGroup, FormArray } from '@angular/forms';
import { PermissionService } from '../../permission.service';
import { ActivatedRoute, Router } from '@angular/router';
import { BaseEntityComponent } from 'src/app/shared';
import { UserTaskService } from '../../usertask.service';
import { of } from 'rxjs/internal/observable/of';
import { takeUntil, startWith, filter, tap, map, distinct } from 'rxjs/operators';
import { Observable, Subject } from 'rxjs';
import {Location, getLocaleDateTimeFormat, DatePipe} from '@angular/common';
import { ApplicationService } from '../../application.service';
import { SottoscrizioneComponent } from '../../pages/sottoscrizione.component';
import { FirmaControparteComponent } from '../../pages/firmacontroparte.component';
import { FirmaDirettoreComponent } from '../../pages/firmadirettore.component';
import { BolloRepertoriazioneComponent } from '../../pages/bollorepertoriazione.component';
import { EmissioneComponent } from '../../pages/emissione.component';
import { PagamentoComponent } from '../../pages/pagamento.component';
import { ConvvalidationComponent } from '../../pages/convvalidation.component';
import { RichiestaEmissioneComponent } from '../../pages/richiestaemissione.component';


@Component({
  selector: 'app-task',
  templateUrl: '../../../shared/base-component/base-entity.component.html'
})

//ng g c submission/components/user -s true --spec false -t true

export class TaskComponent extends BaseEntityComponent {

  //associazione tra task e pagine esecuzione attività
  public static pathEsecuzioneTask(workflow_transition, workflow_place){                                        
    if (workflow_transition == ConvvalidationComponent.WORKFLOW_ACTION){
        return ConvvalidationComponent.ABSULTE_PATH;
    }

    if (!workflow_transition  && workflow_place == SottoscrizioneComponent.STATE){
        return SottoscrizioneComponent.ABSULTE_PATH;
    }

    if (SottoscrizioneComponent.WORKFLOW_ACTIONS.includes(workflow_transition)){
      return SottoscrizioneComponent.ABSULTE_PATH;
    }

    if (workflow_transition == FirmaControparteComponent.WORKFLOW_ACTION){
        return FirmaControparteComponent.ABSULTE_PATH;
    }

    if (workflow_transition == FirmaDirettoreComponent.WORKFLOW_ACTION){
        return FirmaDirettoreComponent.ABSULTE_PATH;
    }

    if (workflow_transition == BolloRepertoriazioneComponent.WORKFLOW_ACTION){
        return BolloRepertoriazioneComponent.ABSULTE_PATH;
    }

    if (workflow_transition == RichiestaEmissioneComponent.WORKFLOW_ACTION){
      return RichiestaEmissioneComponent.ABSULTE_PATH;
    }

    if (workflow_transition == EmissioneComponent.WORKFLOW_ACTION){
        return EmissioneComponent.ABSULTE_PATH;
    }

    if (workflow_transition == PagamentoComponent.WORKFLOW_ACTION){
        return PagamentoComponent.ABSULTE_PATH;
    }          

    return null;
  }



  protected actions: {[key: string]:string} = {
    'approvato': 'Sottoscrizione',
    'store_validazione': 'Approvazione',
    'repertorio': 'Apposizione bollo e repertoriazione',
    'firma_da_direttore2': 'Firma da UniUrb',
    'firma_da_controparte2': 'Firma della controparte',

    'emissione': 'Richiesta di emissione',
    'registrazionepagamento': 'In pagamento',

  }
  

  subject = new Subject<any>();
 
  isLoading = true;
  fields: FormlyFieldConfig[] = [
    {
      className: 'section-label',
      template: '<h5>Dettaglio attività</h5>',
    },
    {
      className: 'section-label',
      template: '<div class="mb-2">Finestra per la gestione di una attivià non per la sua esecuzione</div>',
    }, 
    {
      fieldGroupClassName: 'display-flex',      
      fieldGroup: [
        {
          type: 'button',                  
          templateOptions: {        
            text: 'Esegui attività',            
            btnType: 'btn btn-primary btn-sm border-0 rounded-0',       
            title: 'Esegui attività',
            onClick: ($event, model) => this.open(),
          },
          hideExpression: (model: any, formState: any) => {
            return !model.id && model.state !== 'aperto'
          },   
          expressionProperties: {
            'templateOptions.disabled': (model: any, formState: any) => {                        
              return model.state !== 'aperto';
            },
          }                                  
        },
      ]
    },
    
    {
      fieldGroupClassName: 'row',
      fieldGroup:
        [
          {
            className: 'col-md-2',
            type: 'number',
            key: 'id',
            templateOptions: {
              label: 'Id',
              disabled: true,
            },
            hideExpression: (model: any, formState: any) => {
              return !model.id;
           },   
          },
       
          {
            className: 'col-md-5',
            type: 'select',
            key: 'transition',
            defaultValue: 'self_transition',
            templateOptions: {
              label: 'Prossima azione',
              options: this.subject.asObservable(),
            }, 
            hideExpression: (model: any, formState: any) => {
              return !model.id;
            },                         
          },
          {            
            key: 'created_at',
            className: 'col-md-5',
            type: 'input',                 
            templateOptions:{
              label: 'Data di creazione',
              disabled: true
            },
            hideExpression: (model: any, formState: any) => {
              return !model.id;
            },         
          },     
        ],
    }, 
    {
      type: 'input',
      key: 'subject',
      templateOptions: {
        label: 'Oggetto',
        required: true,
      },
    },     
    {      
      type: 'select',
      key: 'model_type',    
      defaultValue:  'App\\Convenzione', 
      templateOptions: {
        label: 'Tipo di entità',              
        options: [
          { codice: 'App\\Convenzione', descrizione: 'Convenzione' },
          { codice: 'App\\Scadenza', descrizione: 'Scandenza' },
        ],        
        valueProp: 'codice',
        labelProp: 'descrizione',
        description: 'Tipo di entità a cui associare una attività'
      },      
      expressionProperties: {
        'templateOptions.disabled': (model: any, formState: any) => {                        
            return model.id
        },
      }, 
      hooks: {
        onInit: (field) => {
          field.formControl.valueChanges.pipe(
            takeUntil(this.onDestroy$),
            distinct(),
            tap(x => {             
            })
          ).subscribe();
        }
      }               
    },
    //se il task è associato ad una convenzione
    {
      key: 'model',
      type: 'externalobject',     
      templateOptions: {
        disabled: true,
        label: 'Convenzione',
        type: 'string',
        entityName: 'application',
        entityLabel: 'Convenzione',
        entityPath: 'home/convenzioni',
        codeProp: 'id',
        descriptionProp: 'descrizione_titolo',       
        isLoading: false,        
      },
      hideExpression: (model: any, formState: any) => {
        return !formState.model.id || (formState.model.model_type != 'App\\Convenzione' && formState.model.id);
      },  

    },
    {
      key: 'model',
      type: 'externalobject',     
      templateOptions: {
        disabled: true,
        label: 'Scadenza',
        type: 'string',
        entityName: 'scadenza',
        entityLabel: 'Scadenza',
        entityPath: 'home/scadenze',
        codeProp: 'id',
        descriptionProp: 'dovuto_tranche',       
        isLoading: false,        
      },
      hideExpression: (model: any, formState: any) => {
        return !formState.model.id || (formState.model.model_type != 'App\\Scadenza' && formState.model.id);
      },  

    },
    {
      key: 'modelconvenzione',
      type: 'externalobject',         
      templateOptions: {        
        label: 'Convenzione',
        type: 'string',
        entityName: 'application',
        entityLabel: 'Convenzione',
        entityPath: 'home/convenzioni',
        codeProp: 'id',
        descriptionProp: 'descrizione_titolo',
        descriptionFunc: (data) => {
          if (data && data.descrizione_titolo){
            this.updateAzioni(data.id); 
            return data.descrizione_titolo;
          }
          return '';
        },
        isLoading: false,        
      },
      hideExpression: (model: any, formState: any) => {
        return formState.model.id  || (formState.model.model_type != 'App\\Convenzione' && !formState.model.id);
      },        
    },
    //se il task è associato ad una scadenza
    {
      key: 'modelscadenza',
      type: 'externalobject',      
      templateOptions: {        
        label: 'Scadenza',
        type: 'string',
        entityName: 'scadenza',
        entityLabel: 'Scadenza',
        entityPath: 'home/scadenze',
        codeProp: 'id',
        descriptionProp: 'dovuto_tranche',
        descriptionFunc: (data) => {
          if (data && data.dovuto_tranche){
            this.updateAzioni(data.id); 
            return data.dovuto_tranche;
          }
          return '';
        },
        isLoading: false,        
      },
      hideExpression: (model: any, formState: any) => {
        return formState.model.id || (formState.model.model_type != 'App\\Scadenza' && !formState.model.id);
      },                
    },

    //esendo polimorfica le prossime azioni da compiere possono essere verso convenzione o scadenza
    {      
      type: 'select',
      key: 'workflow_transition',
      templateOptions: {
        label: 'Azione da compiere',      
        required: true,
        options: [],        
      },
      hideExpression: (model: any, formState: any) => {
        return model.id;
      },                         
    },
    {
      key: 'unitaorganizzativa_uo',
      type: 'select',
      templateOptions: {
        label: 'Ufficio affidatario procedura',
        required: true,
        options: this.service.getOffices('tutti'),
        valueProp: 'uo',
        labelProp: 'descr',
        // inizialization: () => {
        //   return this.model['unitaOrganizzativa'];
        // },
        // populateAsync: () => {          
        //   return this.service.getOffices('tutti');
        // }
      },          
      expressionProperties: {
        //modificarlo in base al ruolo utente ...                 
        'templateOptions.disabled': (model: any, formState: any) => { return model.id; },
      },
    },
    {
      key: 'respons_v_ie_ru_personale_id_ab',
      type: 'select',
      templateOptions: {
        label: 'Responsabile ufficio',
        valueProp: 'id',
        labelProp: 'descr',
        required: true,
      },
      expressionProperties: {
        'templateOptions.disabled': (model: any, formState: any) => { return model.id; },
      },
      hooks: {
        onInit: (field) => {
          field.form.get('unitaorganizzativa_uo').valueChanges.pipe(
            takeUntil(this.onDestroy$),
            startWith(field.form.get('unitaorganizzativa_uo').value),
            distinct(),
            filter(ev => ev !== null),
            tap(uo => {
              field.formControl.setValue('');
              field.templateOptions.options = this.service.getPersonaleUfficio(uo).pipe(
                map(items => {
                  return items.filter(x => ApplicationService.isResponsabileUfficio(x.cd_tipo_posizorg) );
                }),
                tap(items => {
                  if (items[0] && !field.model.respons_v_ie_ru_personale_id_ab) {
                    field.formControl.setValue(items[0].id);
                  }
                }),
              );
            }),
          ).subscribe();
        },
      },
    },
    {
      key: 'assignments',
      type: 'repeat',
      templateOptions: {
        label: 'Operatori',
      },
      validators: {
        unique: {
          expression: (c) => {
            if (c.value) {
              var valueArr = c.value.map(function (item) { return item.v_ie_ru_personale_id_ab }).filter(x => x != null).map(x => x.toString());
              var isDuplicate = valueArr.some(function (item, idx) {
                return valueArr.indexOf(item) != idx
              });
              return !isDuplicate;
            }
            return true;
          },
          message: (error, field: FormlyFieldConfig) => `Nome ripetuto`,
        },
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
            hooks: {
              onInit: (field) => {
               
                field.parent.parent.form.get('unitaorganizzativa_uo').valueChanges.pipe(
                  takeUntil(this.onDestroy$),
                  startWith(field.parent.parent.form.get('unitaorganizzativa_uo').value),
                  distinct(),
                  filter(ev => ev !== null),
                  tap(uo => {                    
                    field.templateOptions.options = this.service.getPersonaleUfficio(uo).pipe();
                  }),                  
                ).subscribe();
              
              },
            },
          },
        ],
      },
    },
    {
      type: 'textarea',
      key: 'description',
      templateOptions: {
        maxLength: 199,
        label: 'Descrizione',
        description: 'Note utente per operazioni sui task',
        required: true,
        rows: 2,
      },
    },
  ];

  constructor(protected service: UserTaskService, protected route: ActivatedRoute, protected router: Router, protected location: Location, protected datePipe: DatePipe) {
    super(route, router, location)
    this.activeNew = true;
    this.researchPath = 'home/tasks'
    this.newPath = 'home/tasks/new'    
    
    this.model.model = { id: null, desctizione_titolo: null };
    this.model.modelconvenzione = { id: null, desctizione_titolo: null };
    this.model.modelscadenza = { id: null, dovuto_tranche: null };

    this.initObj = {
      modelconvenzione: { id: null, desctizione_titolo: null },
      modelscadenza: { id: null, dovuto_tranche: null }
    }
  }


  //richiamato nel caso di nuovo 
  protected additionalFormInitialize() {
    this.service.create().subscribe((data) => {
      this.isLoading = false;
      this.model = {...this.model, ...JSON.parse(JSON.stringify(data))};
      this.options.formState.model = this.model;
      this.updateTransitions();
    });
  }
  
  protected updateTransitions(){
    if (this.model['transitions']) {       
      this.model['transition']='self_transition';
      this.subject.next([]);
      this.subject.next(this.model['transitions']);        
    }   
  }

  protected preOnSubmit(){
    if (this.model.id == undefined){
      if (this.model.model_type == 'App\\Convenzione' ){
        this.model.model_id = this.model.modelconvenzione.id 
      }else{
        this.model.model_id = this.model.modelscadenza.id 
      }
      
    }        
        
  }
  //richiamato nella init dopo aver caricato il modello
  protected postGetById(){
    this.updateTransitions();  
  }

  //richiamato nella submit dopo l'avvenuto salvataggio
  protected postOnSubmit(){
    this.updateTransitions();
  }

  protected updateAzioni(id){
    let f = this.fields.find(x=> x.key == 'workflow_transition');
    f.formControl.setValue(null);
    f.templateOptions.options = this.service.getNextActions(id, this.model.model_type).pipe(
      map(x => this.assignName(x)),      
      tap(x => this.checkMessage(x,f))
    ); 
        
  }
  
  private checkMessage(result, field){
    if (result && result.length >0){
      field.templateOptions.description = "";
    }else{
      field.templateOptions.description = "Nessuna azione disponibile";
    }
  }

  private assignName(list){
    let result = list.filter(y => y.value != 'self_transition').map(element => {      
      element.label = element.label ? element.label : this.actions[element.value];
      return element;
    });       

    return result;
  }

  protected open(){
    if (this.model.state !== 'aperto'){
      return;
    }

    const path = TaskComponent.pathEsecuzioneTask(this.model.workflow_transition,this.model.workflow_place);
    if (path != null)
      this.router.navigate([path, this.model.model_id]);
  }

}
