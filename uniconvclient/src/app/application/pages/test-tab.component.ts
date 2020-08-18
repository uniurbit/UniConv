import { Component, OnInit } from '@angular/core';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { StepType } from 'src/app/shared';

//ng g c application/pages/test-tab -s true  --spec false --flat true

@Component({
  selector: 'app-test-tab',
  template: `
  <app-form-infra [fields]="fieldtabs"></app-form-infra> 
   `,
  styles: []
})
export class TestTabComponent implements OnInit {

  steps: StepType[] = [
    {
      label: 'Personal data',
      fields: [
        {
          key: 'firstname',
          type: 'input',
          templateOptions: {
            label: 'First name',
            required: true,
          },
        },
        {
          key: 'age',
          type: 'input',
          templateOptions: {
            type: 'number',
            label: 'Age',
            required: true,
          },
        },
      ],
    },
    {
      label: 'Destination',
      fields: [
        {
          key: 'country',
          type: 'input',
          templateOptions: {
            label: 'Country',
            required: true,
          },
        },
      ],
    },
    {
      label: 'Day of the trip',
      fields: [
        {
          key: 'day',
          type: 'input',
          templateOptions: {
            type: 'date',
            label: 'Day of the trip',
            required: true,
          },
        },
      ],
    },
  ];

  fieldtabs: FormlyFieldConfig[] = [
    {
      key: 'conv',
      type: 'tab',
      fieldGroup: [
        {
          fieldGroup: [
            {
              key: 'firstname',
              type: 'input',
              templateOptions: {
                label: 'First name',
                required: true,
              },
            },
            {
              key: 'age',
              type: 'input',
              templateOptions: {
                type: 'number',
                label: 'Age',
                required: true,
              },
            },
          ],
          templateOptions: {
            label: 'Personal data'
          }
        },
        {
          fieldGroup: [
            {
              key: 'country',
              type: 'input',
              templateOptions: {
                label: 'Country',
                required: true,
              },
            },
          ],
          templateOptions: {
            label: 'Destination'
          }
        },
        {
          fieldGroup: [
            {
              key: 'day',
              type: 'input',
              templateOptions: {
                type: 'date',
                label: 'Day of the trip',
                required: true,
              },
            },
          ],
          templateOptions: {
            label: 'Day of the trip'
          }
        }
      ],

    }
  ]




  constructor() { }

  ngOnInit() {
  }

}
