import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig, FormlyFormOptions } from '@ngx-formly/core';
import { FormGroup, FormArray } from '@angular/forms';
import { PermissionService } from '../../permission.service';
import { ActivatedRoute, Router } from '@angular/router';
import { BaseEntityComponent } from 'src/app/shared/base-component/base-entity.component';
import { ClassificazioneService } from '../../classificazione.service';
import {Location} from '@angular/common';
@Component({
  selector: 'app-classificazione', 
  templateUrl: '../../../shared/base-component/base-entity.component.html',
})


//ng g c application/components/classificazione -s true --spec false -t true

export class ClassificazioneComponent extends BaseEntityComponent {
  
  isLoading = true;
  fields: FormlyFieldConfig[] = [
    {
      fieldGroupClassName: 'row',
      fieldGroup: [
        {
          key: 'id',
          type: 'input',
          hide: true,
          className: "col-md-2",
          templateOptions: {
            label: 'Id',
            disabled: true
          }
        },
        {
          key: 'codice',
          type: 'input',
          className: "col-md-5",
          templateOptions: {
            label: 'Codice',
            disabled: true,
            required: true
          },
          expressionProperties: {
            'templateOptions.disabled': 'model.id',
          },
        },
        {
          key: 'descrizione',
          type: 'input',
          className: "col-md-5",
          templateOptions: {
            label: 'Descrizione',
            required: true
          },
        }
      ]
    },    
  ];  

  constructor(protected service: ClassificazioneService, protected route: ActivatedRoute, protected router: Router, protected location: Location) {
    super(route,router, location);
 
    this.activeNew =true;
    this.isRemovable = true;
    this.researchPath = 'home/classificazioni';
    this.newPath = this.researchPath+'/new';
  }

 

  
}
