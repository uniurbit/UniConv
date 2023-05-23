import { Component, OnInit, ViewChild, TemplateRef, Input } from '@angular/core';
import { ApplicationService } from 'src/app/application';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { FormGroup } from '@angular/forms';
import { Page } from 'src/app/shared/lookup/page';
import { Router } from '@angular/router';
import { DatePipe } from '@angular/common';
import { ScadenzaService } from 'src/app/application/scadenza.service';
import { MycurrencyPipe } from 'src/app/shared/pipe/custom.currencypipe';
import { MyFlattenPipe } from 'src/app/shared/pipe/custom.flattenpipe';
import { TranslateService } from '@ngx-translate/core';
import { MyTranslatePipe } from 'src/app/shared/pipe/custom.translatepipe';

@Component({
  selector: 'app-scadenzeresult',
  templateUrl: './scadenzeresult.component.html', 
  styles: []
})
export class ScadenzeresultComponent implements OnInit {
  isLoading: boolean = false;
  currency = new MycurrencyPipe();
  flatten = new MyFlattenPipe('');
  translate: MyTranslatePipe = null;
  @ViewChild('statetemplate', { static: true }) statetemplate: TemplateRef<any>;
    
  @Input() 
  querymodel: any;

  form = new FormGroup({});
  model = {
    data: new Array<any>(),
  }; 
  
  resultMetadata: FormlyFieldConfig[];
  
  constructor(private service: ScadenzaService, private router: Router, private datePipe: DatePipe, private translateService: TranslateService) { 
    this.translate = new MyTranslatePipe(translateService);
    this.resultMetadata = [
      {
        key: 'data',
        type: 'datatablelookup',   
        templateOptions: {
          label: 'Risultati',   
          columnMode: 'force',
          scrollbarH: true,        
          page: new Page(25),
          hidetoolbar: true,      
          onDblclickRow: (event) => this.onDblclickRow(event),
          onSetPage: (pageInfo) => this.onSetPage(pageInfo),   
          columns: [
            { name: '#', prop: 'id', wrapper: 'value',  maxWidth:50 },
            { name: 'Tranche prevista', prop: 'data_tranche', wrapper: 'value', type: 'date' },
            { name: 'Titolo convenzione', prop:'convenzione.descrizione_titolo',  width:'300', wrapper: 'value'},
            { name: "Azienda o ente", prop:'aziende', pipe: this.flatten, maxWidth: 200 },
            { name: 'Dipartimento', prop: 'convenzione.dipartimemto_cd_dip', pipe: this.translate, width: 135, maxWidth: 135 },
            { name: 'Stato', prop: 'state', wrapper: 'value' },
            { 
              name: 'Importo', prop: 'dovuto_tranche', wrapper: 'value', width: 100,
              maxWidth:120, pipe: this.currency,
            },
          ],   
        },    
      }
    ];
  }

  ngOnInit() { 
  
    let cols: (Array<any>) = this.resultMetadata.find(x => x.key == "data").templateOptions.columns;
    cols.find(x => x.prop == 'state').cellTemplate = this.statetemplate;
      
    this.querymodel['limit'] = 25;
    this.onFind(this.querymodel);
      
  }

  onDblclickRow(event) {
    if (event.type === 'dblclick') {       
      this.router.navigate(['home/scadenzeview', event.row.id]);
    }
  }
   
  onSetPage(pageInfo){      
    if (pageInfo.limit)
      this.querymodel['limit']= pageInfo.limit;     
    if (this.model.data.length>0){
      this.querymodel['page']=pageInfo.offset + 1;     
      this.onFind(this.querymodel);
    }
  }

  onFind(model){
    this.querymodel.rules = model.rules;  

    this.isLoading = true;    
    //this.service.clearMessage();
    try{      
      this.service.query(this.querymodel).subscribe((data) => {
        const to = this.resultMetadata[0].templateOptions;
        this.isLoading = false;   
        this.model=  {
          data: data.data
        }

        to.page.totalElements = data.total; // data.to;
        to.page.pageNumber = data.current_page-1;
        to.page.size = data.per_page;        
        
      }, err => {
        this.isLoading=false;
        console.error('Oops:', err.message);
      });
    }catch(e){
      this.isLoading = false;
      console.error(e);
    }
  }

}
