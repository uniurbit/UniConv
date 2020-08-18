import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'app-fase-wrapper',
  template: `
  <div class="card border border-primary mb-2"   [ngClass]="{
    'border-secondary': options.type == 'secondary',
    'border-primary': options.executed != null ? options.executed >= 0 : (options.type == 'warning' || options.type == 'success'),  
    'border-info': options.type == 'info'
  }" style="border-radius: 3px !important;">

    <div class="card-title p-2 mb-0" [ngClass]="{ 
      'bg-light-warning': options.executed != null ? options.executed == -1 :  options.type == 'warning',
      'bg-light-info': options.executed != null ? options.executed >= 0 : options.type == 'info',      
      'bg-light-success': options.type ? options.type == 'success' : false,
      'bg-light': options.type ? options.type == 'gray' : false
    }" style="border-radius: 3px !important;">    
        
      <button class="btn btn-sm btn-link float-right" type="button" (click)="isCollapsed = !isCollapsed" [attr.aria-expanded]="!isCollapsed" aria-controls="collapseComp">         
        <span *ngIf="isCollapsed" class="oi oi-chevron-top" [ngClass]="{
          'text-secondary': options.type == 'secondary',
          'text-primary':options.type == 'primary',
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
    
   
    <div class="pl-2 pr-2" id="collapseComp" [ngbCollapse]="isCollapsed">
     <ng-content></ng-content>
    </div> 
   
  </div>         
  `,
  styles: []
})
export class FaseWrapperComponent implements OnInit {

  public isCollapsed = false;

  @Input() options: {
    title?: string,       
    executed?: number,
    type?: string, 
  }  

  constructor() {  
  }

  ngOnInit() {
  }


}
