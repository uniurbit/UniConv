import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { Subject } from 'rxjs';
import { FormlyFieldConfig, FormlyFormOptions } from '@ngx-formly/core';
import { FormGroup } from '@angular/forms';
import { ConvenzioneAmministrativa, convenzioneFrom, rinnovoType } from '../convenzione';
import { ApplicationService } from '../application.service';
import { AuthService } from 'src/app/core';
import { Router } from '@angular/router';
import { InfraMessageType } from 'src/app/shared/message/message';
import ControlUtils from 'src/app/shared/dynamic-form/control-utils';

@Component({
  selector: 'app-ins-conv-amm',
  templateUrl: './ins-conv-amm.component.html',
  styles: []
})
export class InsConvAmmComponent implements OnInit {

  private prefix = 'insconvamm';

  onDestroy$ = new Subject<void>();
  fields: FormlyFieldConfig[];

  form = new FormGroup({});
  model: ConvenzioneAmministrativa;

  isLoading: boolean;

  options: FormlyFormOptions;

  constructor(private service: ApplicationService, public authService: AuthService, private router: Router,  private cdRef : ChangeDetectorRef) { 

    this.model = {
      schematipotipo: 'schematipo',
      transition: 'self_transition',
      user_id: authService.userid,
      id: null,
      descrizione_titolo: '',
      dipartimemto_cd_dip: null,
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
      convenzione_from: convenzioneFrom.amm,
      rinnovo_type: rinnovoType.non_rinnovabile
    };

    this.options = {
      formState: {
        isLoading: false,
        model: this.model,
      },
    };

    this.fields = [
      {
        type: 'tabinfra',
        templateOptions:{
          onSubmit: () => this.onSubmit(),
        },
        fieldGroup: [
          {
            wrappers: ['accordioninfo'],
            fieldGroup: [          
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
        ]
      }];

      if (this.getStorageModel()){
        let app = JSON.parse(this.getStorageModel());
        this.checkHistory(app);                  
        this.model = app;       
        this.setStorageModel();
      }else{
        if (this.checkHistory(this.model))
          this.setStorageModel();
      }
  }

  ngOnInit() {
  }

  onSubmit() {

    if (this.form.valid) {
      this.isLoading = true;
      var tosubmit: ConvenzioneAmministrativa = { ...this.model, ...this.form.value };
      
      this.service.createSchemaTipo(tosubmit, true).subscribe(
        result => {
          this.isLoading = false;
          sessionStorage.removeItem(this.prefix+'_model');
          this.router.navigate(['home/dashboard/dashboard1']);  
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
  
  public onValidate() {
    ControlUtils.validate(this.fields[0]);
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

  ngOnDestroy(): void {
    this.onDestroy$.next();
    this.onDestroy$.complete();
    if (this.form.touched){
      this.setStorageModel();
    }
  
  }

 
}
