import { Pipe, PipeTransform } from "@angular/core";
import {TranslateService} from '@ngx-translate/core';
import ControlUtils from "../dynamic-form/control-utils";
import { DatePipe } from "@angular/common";
import { NgbStringAdapter } from "src/app/NgbStringAdapter";


@Pipe({
    name: 'mydiffdate',   
  })
 
export class MyDiffdatePipe implements PipeTransform  {
  
  adapter: NgbStringAdapter=  new NgbStringAdapter();

  public transform(value): number {    
    //format dd-mm-yyyy
    const date = this.adapter.fromModel(value);
    //moth is zero based!!!
    const dateTo = new Date(date.year,date.month-1,date.day);
    const dateFrom = new Date();
    if (dateFrom && dateTo){
      const diff = dateTo.valueOf() - dateFrom.valueOf();
      const diffDays = Math.floor(diff / (1000 * 3600 * 24));
      if (Number.isInteger(diffDays)){
        return diffDays + 1;
      }       
    }
  }
}
