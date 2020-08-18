import { Pipe, PipeTransform } from '@angular/core';
import { formatCurrency, getCurrencySymbol } from '@angular/common';
import { isString } from 'util';

@Pipe({
    name: 'myflatten',
  })
  export class MyFlattenPipe implements PipeTransform {
    transform(value: any, args?: any): any {
        const prop = args ? args : 'denominazione' 
        if (value && value as Array<any>){
          return value.map(it => it[prop]).join(', ');
        }
        return value;
      }
}