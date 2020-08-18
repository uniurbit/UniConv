import { Component, OnInit, Input, ViewChild, TemplateRef, KeyValueDiffers } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { FormlyFieldConfig, FieldArrayType, FormlyFormBuilder } from '@ngx-formly/core';
import { TableColumn } from '@swimlane/ngx-datatable';
import { Router } from '@angular/router';
import { Page, PagedData } from '../lookup/page';
import { SSL_OP_DONT_INSERT_EMPTY_FRAGMENTS } from 'constants';
import { DatatableRowDetailDirective } from '@swimlane/ngx-datatable';


 
@Component({
  selector: 'app-tablegroup-type',
  template: `  
  <ngx-datatable
    #grouptable  class="bootstrap" 
    [messages]="{emptyMessage: 'NODATA' | translate, totalMessage: 'TOTAL' | translate, selectedMessage: false}"
    [rows]="model"
    [groupRowsBy]="to.groupRowsBy"
    [columns]="to.columns"
    [columnMode]="to.columnMode"
    [rowHeight]="to.rowHeight"   
    [headerHeight]="to.headerHeight"      
    [footerHeight]="to.footerHeight"
    [scrollbarH]="to.scrollbarH"    
    [scrollbarV]="to.scrollbarV"  
    [reorderable]="to.reorderable"    
    [selected]="to.selected"
    [selectionType]="'single'"
    [groupExpansionDefault]="to.groupRowsBy ? to.groupExpansionDefault : null"
    [summaryRow]="to.enableSummary"
    [summaryPosition]="to.summaryPosition"
    [summaryHeight]="'auto'"
    (sort)="onSort($event)"
    (activate)='onEvents($event)'
  >    

<!-- Group Header Template -->
<ngx-datatable-group-header *ngIf="to.groupRowsBy"  [rowHeight]="70" #myGroupHeader (toggle)="onDetailToggle($event)">
  <ng-template let-group="group" let-expanded="expanded" ngx-datatable-group-header-template>
    <div style="padding: 5px">
      <a    
        [class.datatable-icon-right]="!expanded"
        [class.datatable-icon-down]="expanded"
        title="Apri/Chiudi Gruppo"
        (click)="onToggleExpandGroup(group)"
      >
        <span>{{ getGroupHeaderTitle(group) }}</span>
      </a>                          
    </div>
  </ng-template>
</ngx-datatable-group-header>


`
})


export class TableGroupTypeComponent extends FieldArrayType {  
  
  @ViewChild('grouptable') table: any;  
  @ViewChild('colDetail') colDetail: any;

  
  ngOnInit() {      
    
    if (!('selected' in this.to)){
      Object.defineProperty(this.to,'selected',{
        enumerable: true,
        configurable: true,
        writable: true
      });
      this.to.selected= [];
    }

    if(this.to.headerColGroupTemplate){
      this.table.groupHeader.template = this.to.headerColGroupTemplate;
    }
  
    if (this.to.rowDetailTemplate){
      //aggiunta della colonna per aprire il dettaglio
      (this.to.columns as Array<any>).splice(0,0,{
        'maxwith': 50,
        'resisable': false,
        'sortable': false,
        'draggable': false,
        'canAutoResize':false,
        'cellTemplate': this.colDetail
      })
      //aggiunto il template della riga di dettaglio
      this.table.rowDetail.template = this.to.rowDetailTemplate;
    }

    if (typeof this.to.columns == 'undefined'){
      this.to.columns =  this.field.fieldArray.fieldGroup.map(el => {      
        
        let c = { 
          name: el.templateOptions.label, 
          prop: el.key,                                          
        }
        el.templateOptions.label = "";
                       
        return c;
      });
      
    }
    
  }

 
  onEvents(event) {
    if (event.type == "dblclick" && typeof this.to.onDblclickRow !== "undefined"){
      this.to.onDblclickRow(event);         
    }
  }
  
  ngDoCheck() {    
     
  }
  
  getGroupRowHeight(group, rowHeight) {
    let style = {};

    style = {
      height: (group.length * 40) + 'px',
      width: '100%'
    };

    return style;
  }

  onToggleExpandGroup(group) {
    this.table.groupHeader.toggleExpandGroup(group);
  }  

  onDetailToggle(event) {
  }

  onToggleExpandRow(row) { 
    this.table.rowDetail.toggleExpandRow(row);
  }


  getGroupHeaderTitle(group){
    if (this.to.groupHeaderTitle){
      return this.to.groupHeaderTitle(group)
    }
    return group.value[0];
  }

  getDescendantProp(obj, path) {
    return path.split('.').reduce((acc, part) => acc && acc[part], obj)
  }

  onSort(event) {
    const sort = event.sorts[0];
    this.model.sort((a , b) => {   
        const valuea = this.getDescendantProp(a,sort.prop);
        const valueb = this.getDescendantProp(b,sort.prop);
        if (valuea != null && valueb != null){             
          if (typeof valuea ===  "number"){
              return ((valuea>valueb ? 1 : valuea<valueb ? -1 : 0) * (sort.dir === 'desc' ? -1 : 1));  
          }    
          return (valuea.localeCompare(valueb) * (sort.dir === 'desc' ? -1 : 1));    
        }
    });          

    this.formControl.patchValue(this.model);   
  }

 }
