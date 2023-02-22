import { Component, OnInit, ViewChild, TemplateRef } from '@angular/core';
import { DashboardService } from '../dashboard.service';
import { DatePipe, getLocaleDateTimeFormat, TitleCasePipe } from '@angular/common';
import { convenzioneFrom } from 'src/app/application/convenzione';
import { MyDiffdatePipe } from 'src/app/shared/pipe/custom.diffdatepipe';
import { TranslatePipe, TranslateService } from '@ngx-translate/core';
import { MyTranslatePipe } from 'src/app/shared/pipe/custom.translatepipe';
import { MyFlattenPipe } from 'src/app/shared/pipe/custom.flattenpipe';

@Component({
  selector: 'app-dashboard-conv-amministrativa',
  templateUrl: './dashboard-conv-amministrativa.component.html',
  styles: []
})
export class DashboardConvAmministrativaComponent implements OnInit {

  public queryconvinesecuzione: any = {};
  public queryconvscadute: any = {};
  public queryconvsinscadenza: any = {};

  public baseColumns = [];
  public columnsInScadenza = [];

  flatten = new MyFlattenPipe('');
  translate: MyTranslatePipe = null;

  constructor(public service: DashboardService, private datePipe: DatePipe, private translateService: TranslateService) { 
    this.translate = new MyTranslatePipe(translateService);
    this.baseColumns = [ 
      { name: '#', prop: 'id', width: 60, maxWidth: 70},
       {name: "Descrizione Titolo", prop: "descrizione_titolo", width: 300},
       {name: "Azienda o ente", prop:'aziende', pipe: this.flatten, minWidth: 300 },
       {name: "Responsabile scientifico", prop: "resp_scientifico"},
       {name: "Tipo convenzione", prop: "convenzione_type", pipe: this.translate},
       {name: "Ambito", prop: "ambito", pipe: new TitleCasePipe()},    
       {name: "Data inizio", prop: "data_inizio_conv", type: 'date' },
       {name: "Data fine", prop: "data_fine_conv", type: 'date'},
       {name: "Stato", prop: "current_place"}, 
       
     ]
     this.columnsInScadenza = this.baseColumns.concat({ name: 'Giorni alla scad.', prop: 'data_fine_conv', pipe: new MyDiffdatePipe(), minWidth: 100 });
  }

  ngOnInit() {
    const today = this.datePipe.transform(Date.now(), 'dd-MM-yyyy');   
    let d = new Date();
    const todayPlusMoths = this.datePipe.transform(d.setMonth(d.getMonth()+6), 'dd-MM-yyyy');

    this.queryconvinesecuzione.rules = [        
      { field: "data_inizio_conv", operator: "<=", value: today, type: "date" },
      { field: "data_fine_conv",  operator: ">=", value: today, type: "date" },        
      { field: "convenzione_from",  operator: "=", value: convenzioneFrom.amm }    
    ];     
       
    this.queryconvsinscadenza.rules = [        
      { field: "data_fine_conv",  operator: ">=", value: today, type: "date" },              
      { field: "data_fine_conv",  operator: "<=", value: todayPlusMoths, type: "date" },              
      { field: "convenzione_from",  operator: "=", value: convenzioneFrom.amm },    
      { field: "current_place",  operator: "In", value: ['firmato', 'repertoriato'] }    
    ];     
    
    this.queryconvscadute.rules = [        
      { field: "data_fine_conv",  operator: "<", value: today, type: "date" },              
      { field: "convenzione_from",  operator: "=", value: convenzioneFrom.amm },    
      { field: "current_place",  operator: "In", value: ['firmato', 'repertoriato'] }    
    ]; 
  }

}
