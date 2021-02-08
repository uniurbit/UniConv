import { Inject, InjectionToken, Pipe, PipeTransform } from '@angular/core';
import { formatCurrency, getCurrencySymbol } from '@angular/common';
import { isString } from 'util';

export const DEFAULT_FLATTENPIPE = new InjectionToken<string>('');
@Pipe({
    name: 'myflatten',
    pure: true
  })
  export class MyFlattenPipe implements PipeTransform {

    constructor(@Inject(DEFAULT_FLATTENPIPE) private args: string = null) {}
    transform(value: any, args?: any): any {
        const prop = this.args ? this.args : 'denominazione' 
        if (value && value as Array<any>){
          return value.map(it => it[prop]).join(', ');
        }
        return value;
      }
}