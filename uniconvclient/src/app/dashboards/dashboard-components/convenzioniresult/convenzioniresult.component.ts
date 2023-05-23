import { Component, OnInit, ViewChild, TemplateRef, Input } from '@angular/core';
import { ApplicationService } from 'src/app/application';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { FormGroup } from '@angular/forms';
import { Page } from 'src/app/shared/lookup/page';
import { Router } from '@angular/router';
import { DatePipe, TitleCasePipe } from '@angular/common';
import { MycurrencyPipe } from 'src/app/shared/pipe/custom.currencypipe';
import { MyFlattenPipe } from 'src/app/shared/pipe/custom.flattenpipe';
import { MyTranslatePipe } from 'src/app/shared/pipe/custom.translatepipe';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'app-convenzioniresult',
  templateUrl: './convenzioniresult.component.html', 
  styles: []
})
export class ConvenzioniresultComponent implements OnInit {
  isLoading: boolean = false;

  @ViewChild('detailRow', { static: true }) detailRow: TemplateRef<any>;
  @ViewChild('converter', { static: true }) converter: TemplateRef<any>;  

  @Input() querymodel: any;
  @Input() columns: [] = null;
  @Input() baseColumns: Array<any> = null;

  form = new FormGroup({});
  model = {
    data: new Array<any>(),
  };
  resultMetadata: FormlyFieldConfig[];

  currency = new MycurrencyPipe();
  titlecase = new TitleCasePipe();
  flatten = new MyFlattenPipe('');
  translate: MyTranslatePipe = null;

  constructor(private service: ApplicationService, private router: Router, private datePipe: DatePipe,  private translateService: TranslateService) { }

  ngOnInit() {

    if (this.baseColumns == null || this.baseColumns.length == 0){
      this.translate = new MyTranslatePipe(this.translateService);
      this.baseColumns =  [              
        { name: '#', prop: 'id', width: 60, maxWidth: 70},
        {name: "Descrizione Titolo", prop: "descrizione_titolo", width: 300},
        {name: "Azienda o ente", prop:'aziende', pipe: this.flatten, minWidth: 300 },
        {name: 'Dipartimento', prop: 'dipartimemto_cd_dip', pipe: this.translate, width: 135, maxWidth: 135 },
        {name: "Responsabile scientifico", prop: "resp_scientifico"},
        {name: "Tipo convenzione", prop: "convenzione_type", cellTemplate: this.converter},
        {name: "Ambito", prop: "ambito", pipe: this.titlecase},
        {name: "Modalità di pagamento", prop: "tipopagamento.descrizione", width: 200},
        {name: "Corrispettivo IVA esclusa se applicabile", prop: "corrispettivo",  pipe: this.currency,},
        {name: "Data inizio", prop: "data_inizio_conv",  type: 'date'},
        {name: "Data fine", prop: "data_fine_conv",  type: 'date'},
        {name: "Stato", prop: "current_place"},
      ]              
    }

    if (this.columns){
      this.baseColumns = this.baseColumns.concat(this.columns);
    }

    this.resultMetadata =  [
      {
          key: 'data',
          type: 'datatablelookup',    
          templateOptions: {
            label: 'Risultati',   
            columnMode: 'force',
            headerHeight: 50,
            footerHeight: 50,            
            scrollbarH: true,             
            hidetoolbar: true, 
            //detailRow: this.detailRow,
            selected: [],                        
            page: new Page(25),       
            onDblclickRow: (event) => this.onDblclickRow(event),
            onSetPage: (pageInfo) => this.onSetPage(pageInfo),
            columns: this.baseColumns                      
          },
          fieldArray: {
            fieldGroup: []
          }
        }
      ];

      this.querymodel['limit']= 25;  
      this.onFind(this.querymodel);
      
  }

  onDblclickRow(event) {
    if (event.type === 'dblclick') {          
      this.router.navigate(['home/convdetails', event.row.id]);
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
    try{      
      this.service.query(this.querymodel).subscribe((data) => {
        const to = this.resultMetadata[0].templateOptions;
        this.isLoading = false;   
        this.model=  {
          data: data.data
        }

        to.page.totalElements = data.total;
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
