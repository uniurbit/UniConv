import { ControlBase } from "..";
import { FormArray, FormControl, FormGroup, Validators } from "@angular/forms";
import { Observable } from "rxjs";
import { PDFJSStatic } from "pdfjs-dist";
import { FormlyFieldConfig } from "@ngx-formly/core";

export interface ResultParse{
  docnumber: string;
  converted: string;
}

const PDFJS: PDFJSStatic = require('pdfjs-dist');

export default class ControlUtils {

  static getServiceName(entityName: string) {
    return entityName + 'Service';
  }

  static toFormGroup(controls: ControlBase<any>[]) {
    let group: any = {};

    controls.forEach(ctrl => {
      if (ctrl.controlType === 'array') {
        group[ctrl.key] = new FormArray([]);
      } else {
        group[ctrl.key] = new FormControl(ctrl.value || '', this.mapValidators(ctrl.validation));
      }
    });
    return new FormGroup(group);
  }

  static normalizeArray<T>(array: Array<T>, indexKey: keyof T) {
    const normalizedObject: any = {}
    for (let i = 0; i < array.length; i++) {
      const key = array[i][indexKey]
      normalizedObject[key] = array[i]
    }
    return normalizedObject as { [key: string]: T }
  }

  static mapValidators(validators) {
    const formValidators = [];

    if (validators) {
      for (const validation of Object.keys(validators)) {
        if (validation === 'required') {
          formValidators.push(Validators.required);
        } else if (validation === 'min') {
          formValidators.push(Validators.min(validators[validation]));
        }
      }
    }
    return formValidators;
  }


  
  static render_page(pageData) {
    //check documents https://mozilla.github.io/pdf.js/
    //ret.text = ret.text ? ret.text : "";

    let render_options = {
      //replaces all occurrences of whitespace with standard spaces (0x20). The default value is `false`.
      normalizeWhitespace: false,
      //do not attempt to combine same line TextItem's. The default value is `false`.
      disableCombineTextItems: false
    }

    return pageData.getTextContent(render_options)
      .then(function (textContent) {
        let lastY, text = '';
        //https://github.com/mozilla/pdf.js/issues/8963
        //https://github.com/mozilla/pdf.js/issues/2140
        //https://gist.github.com/hubgit/600ec0c224481e910d2a0f883a7b98e3
        //https://gist.github.com/hubgit/600ec0c224481e910d2a0f883a7b98e3
        for (let item of textContent.items) {
          if (lastY == item.transform[5] || !lastY) {
            text += item.str;
          }
          else {
            text += '\n' + item.str;
          }
          lastY = item.transform[5];
        }
        //let strings = textContent.items.map(item => item.str);
        //let text = strings.join("\n");
        //text = text.replace(/[ ]+/ig," ");
        //ret.text = `${ret.text} ${text} \n\n`;
        return text;
      });
  }

  public static async parsePdf(data): Promise<ResultParse>{
    let text = '';
    const result: ResultParse = <ResultParse> {};    
    await PDFJS.getDocument({ data: data }).then(async (doc) => {
      let counter: number = 1;
      counter = counter > doc.numPages ? doc.numPages : counter;

      for (var i = 1; i <= counter; i++) {
        let pageText = await doc.getPage(i).then(pageData => this.render_page(pageData));
        text = `${text}\n\n${pageText}`;      
        //ret.text = `${ret.text}\n\n${pageText}`;
      }                      
      let number = text.match(/[d|D]elibera n.?\s?([A-Za-z0-9\/]*)\s*\n/);
      if (number && number[1]){
        //this.form.get('docnumber').setValue(number[1]);
        result.docnumber = number[1];
      
      }
      let data_emissione = text.match(/[r|R]iunione del giorno\s([0-9]{2}\/[0-9]{2}\/[0-9]{4})\s?/);
      if (data_emissione && data_emissione[1]){
        result.converted = data_emissione[1].replace(/\//g,'-');
      }            
    });
    return result;   
  }

  //date nel formato gg-mm-aaaa
  public static toDate(date: string): Date{
    const comp_date = date.split('-');      
    return new Date(Number.parseInt(comp_date[2]), Number.parseInt(comp_date[1])-1, Number.parseInt(comp_date[0]));
  }

  public static validate(field: FormlyFieldConfig) {    
    if (field.fieldGroup) {
      field.fieldGroup.forEach((f) => ControlUtils.validate(f));
    }else{     
      if (field.key) 
        field.validation = {show: true};
    }
  }



}