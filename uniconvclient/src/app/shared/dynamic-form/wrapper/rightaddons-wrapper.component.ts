import { Component, ViewChild, ViewContainerRef } from '@angular/core';
import { FieldWrapper } from '@ngx-formly/core';
import { ListItemComponent } from '../../view-list/list-item/list-item.component';

@Component({
  selector: 'formly-wrapper-rightaddons',
  template: `  
  <div class="input-group">
    <div class="input-addons">
      <ng-container #fieldComponent></ng-container>
    </div>
    <ng-container #fieldComponent></ng-container>
    <div class="input-group-append" *ngIf="to.addonRights" > 
      <ng-container *ngFor="let item of to.addonRights; index as i;">    
        <button type="button" class="input-group-text" [disabled]="disabled(item)" 
            title="{{item.text}}" [ngClass]="item.class" *ngIf="item.class"  (click)="addonRightClick($event,i)"></button>               
      </ng-container>
    </div>
  </div>
  `,
  styleUrls: ['./rightaddons-wrapper.component.scss'],
})

export class RightaddonsWrapperComponent extends FieldWrapper {
  public isCollapsed = false;
  @ViewChild('fieldComponent', { read: ViewContainerRef, static: true }) fieldComponent: ViewContainerRef;

  addonRightClick($event: any,i) {
    if (this.to.addonRights[i].onClick) {
      this.to.addonRights[i].onClick(this.to, this, $event);
    }
  }

  disabled(item){
    if (item.alwaysenabled && item.alwaysenabled instanceof Function){      
        //non deve essere disabilitato il controllo
        //sempre abilitato quando è valido
        return !this.to.disabled && !item.alwaysenabled();
    }
    return this.to.disabled && !item.alwaysenabled;
  }

}