import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig, FormlyFormOptions } from '@ngx-formly/core';
import { FormGroup, FormArray } from '@angular/forms';
import { PermissionService } from '../../permission.service';
import { ActivatedRoute, Router } from '@angular/router';
import { BaseEntityComponent } from 'src/app/shared/base-component/base-entity.component';
import { AziendaLocService } from '../../aziendaloc.service';
import {Location} from '@angular/common';
import { AppConstants } from 'src/app/app-constants';
import { of } from 'rxjs';

@Component({
  selector: 'app-aziendaloc', 
  templateUrl: '../../../shared/base-component/base-entity.component.html',
})

//ng g c submission/components/user -s true --spec false -t true

export class AziendaLocComponent extends BaseEntityComponent {
  
  isLoading = true;
  fields: FormlyFieldConfig[] = [  
    {        
      fieldGroupClassName: 'row',
      fieldGroup: [ 
      {
        key: 'id',
        type: 'input',
        className: "col-md-2",           
        templateOptions: {
          label: 'Codice',
          disabled: true                        
        },
        hideExpression: (model: any) => !model.id
      },    
    ],
    },

    {
      wrappers: ['riquadro'],
      templateOptions: {
        title: 'Riferimento azienda sistema gestionale di ateneo'
      },
      fieldGroup: [
      {
        key: 'azienda_id_esterno',
        type: 'external',          
        templateOptions: {
          label: 'Riferimento azienda',
          type: 'number',
          entityName: 'azienda',
          entityLabel: 'Aziende sistema gestionale di ateneo',
          enableNew: false,
          codeProp: 'id_ab',        
          descriptionFunc: (data) => {
            if (data && data.denominazione){            
              this.updateAzienda(data);
              return data.denominazione;
            } 
            else if(data && (data.nome || data.cognome))
            {
              data.denominazione = data.nome+' '+data.cognome;
              this.updateAzienda(data);

              return data.denominazione;
            }
            
            return '';
          },
          descriptionProp: 'denominazione',
          description: 'Riferimento azienda sistema gestionale di ateneo'
        },      
      },
      ]
    },
    {
      wrappers: ['riquadro'],
      templateOptions: {
        title: 'Dati anagrafici'
      },
      fieldGroup: [
          {
            template: '<div><strong>Titolare o legale rappresentante</strong></div>',
          },
          {        
            fieldGroupClassName: 'row',
            fieldGroup: [         
            
              {
                key: 'nome',
                type: 'input',
                className: "col-md-6",
                templateOptions: {
                  label: 'Nome',            
                  required: true,
                  maxLength: 190,
                }
              },
              {
                key: 'cognome',
                type: 'input',
                className: "col-md-6",
                templateOptions: {
                  label: 'Cognome',
                  required: true,
                  maxLength: 190,
                },
              }
            ],      
          },    
          {
            key: 'denominazione',
            type: 'input',      
            templateOptions: {
              label: 'Denominazione',
              description: 'Denominazione della società',
              required: true,
              maxLength: 190,
            },
          },    
          {
            fieldGroupClassName: 'row',
            fieldGroup: [
              {      
                key: 'cod_fisc',
                type: 'input',  
                className: "col-md-6",    
                templateOptions: {
                  label: 'Codice fiscale',      
                  minLength: 11,
                  maxLength: 16          
                },
              },
              {
                key: 'part_iva',
                type: 'input',      
                className: "col-md-6",    
                templateOptions: {
                  translate: true,
                  label: 'AZIENDALOC.PARTIVA',  
                  minLength: 11,
                  maxLength: 11             
                },
              }]
          },
          {
            fieldGroupClassName: 'row',
            fieldGroup: [
              {
                key: 'provincia',
                type: 'select',
                className: 'col-md-3',              
                templateOptions: {
                  options: of(AppConstants.LISTA_PROVINCE.map(prov => { return { value: prov.sigla, label: prov.sigla + " " + prov.nome } })),
                  attributes: {
                    autocomplete: 'azienda-provincia',
                  },
                  translate: true,
                  label: 'Provincia',
                  required: true,
                  description: "Inserire EE per residenze all'estero"
                },
              },              
              {
                key: 'comune',
                className: "col-md-6", 
                type: 'input',      
                templateOptions: {
                  label: 'Comune',
                  maxLength: 190,
                  required: true,                
                },
              },
              {
                key: 'cap',
                type: 'input',    
                className: "col-md-3",   
                templateOptions: {
                  label: 'Cap',      
                  maxLength: 25,
                  required: true,          
                },
              },
            ]
          },
          {
            fieldGroupClassName: 'row',
            fieldGroup: [
              {
                key: 'indirizzo1',
                type: 'input',      
                className: "col-md-6",    
                templateOptions: {
                  translate: true,
                  label: 'AZIENDALOC.INDIRIZZO',                                    
                  maxLength: 190,
                  required: true,
                },
              },
          
            ]
          },    
          {
            key: 'pec_email',
            type: 'input',      
            templateOptions: {
              translate: true,
              label: 'AZIENDALOC.PEC',                //PEC (oppure email di contatto per aziende o enti esteri)'
              required: true,       
              pattern: /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/,
            },
          },
      ]
    }
  ];  

  constructor(protected service: AziendaLocService, protected route: ActivatedRoute, protected router: Router, protected location: Location) {
    super(route,router,location);
    this.activeNew =true;
    this.researchPath = 'home/aziendeloc'
    this.newPath = this.researchPath+'/new';
    this.isRemovable = true;
  }

  ngOnInit() {
    super.ngOnInit();
    this.route.paramMap.subscribe(
      (params) => {
        if (params.get('from')) {
          this.activeNew=false;
        }
      });
  }

  updateAzienda(data){
    this.form.get('denominazione').setValue(data.denominazione);
    if (data.nome){
      this.form.get('nome').setValue(data.nome);
    }
    if (data.cognome){
      this.form.get('cognome').setValue(data.cognome);
    }    
    this.form.get('cod_fisc').setValue(data.cod_fis);
    this.form.get('part_iva').setValue(data.part_iva);

    if (data.rappresentante_legale){
      this.form.get('nome').setValue(data.rappresentante_legale.split(' ').slice(0, -1).join(' '));
      this.form.get('cognome').setValue(data.rappresentante_legale.split(' ').slice(-1).join(' '));
    }

    const indirizzi = data.indirizzi;
    if (Array.isArray(indirizzi) && indirizzi.length > 0){
      const indirizzo = data.indirizzi[0];
      if (indirizzo.cd_sigla_prov)
        this.form.get('provincia').setValue(indirizzo.cd_sigla_prov);
      if (indirizzo.ds_comune)
        this.form.get('comune').setValue(indirizzo.ds_comune);
      if (indirizzo.indirizzo)
        this.form.get('indirizzo1').setValue(indirizzo.indirizzo);
      if (indirizzo.cd_cap)
        this.form.get('cap').setValue(indirizzo.cd_cap);
    }
//     cd_cap: "00185"
// cd_catasto_comune: "H501"
// cd_sigla_prov: "RM"
// dt_fine_val: "2222-02-02 00:00:00"
// id_ab: 58446
// indirizzo: "Via Parigi"
// num_civico: "11"

  }


  onBack(){
    if (this.returnUrl){
      this.router.navigate([this.returnUrl], {
        state: { entity: {
            id: this.model.id,
            denominazione: this.model.denominazione,
          }
        }
      });
    } else {
      this.goBack();
    }
  }
  
}
