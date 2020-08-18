import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'app-collapse-riquadro-wrapper',
  template: `
  <div class="card border p-2 mb-2" [ngClass]="{
    'border-secondary': options.type == 'secondary',
    'border-primary': options.type == 'primary',
    'border-info': options.type == 'info'
  }" style="border-radius: 3px !important;">

  <div class="card-title mb-0">         
      <button class="btn btn-sm btn-link float-right" type="button" (click)="isCollapsed = !isCollapsed" [attr.aria-expanded]="!isCollapsed" aria-controls="collapseComp">         
        <span *ngIf="isCollapsed" class="oi oi-chevron-top" [ngClass]="{
          'text-secondary': options.type == 'secondary',
          'text-primary': options.type == 'primary',
          'text-info': options.type == 'info'
        }"></span>
        <span *ngIf="!isCollapsed" class="oi oi-chevron-bottom" [ngClass]="{
          'text-secondary': options.type == 'secondary',
          'text-primary': options.type == 'primary',
          'text-info': options.type == 'info'
        }"></span>

      </button>          
      <div *ngIf="options && options.title" class="align-items-center">                  
          <h5>{{options.title}}</h5>
          <h6 *ngIf="options && options.subtitle" class="card-subtitle">{{options.subtitle}}</h6>            
      </div>   
  </div>
  <div id="collapseComp" [ngbCollapse]="isCollapsed">
    <ng-content></ng-content>
  </div>  
</div>     
  `,
  styles: []
})
export class CollapseRiquadroWrapperComponent implements OnInit {

  @Input() options: {
    title?: string,
    subtitle?: string,
    type: string,
    isCollapsed?: boolean
  }
  
  public isCollapsed = false;

  constructor() { 
    this.options = {
      type: 'primary'
    }
  }

  ngOnInit() {
    if (this.options.isCollapsed!=null){
      this.isCollapsed = this.options.isCollapsed;
    }
  }

}
