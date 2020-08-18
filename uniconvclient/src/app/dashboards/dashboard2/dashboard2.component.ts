import { Component, AfterViewInit, OnInit } from '@angular/core';
import { DashboardService } from '../dashboard.service';
import { Observable } from 'rxjs';
import { tap, map } from 'rxjs/operators';
import { DatePipe } from '@angular/common';
import { convenzioneFrom } from 'src/app/application/convenzione';

@Component({
  templateUrl: './dashboard2.component.html',
  styleUrls: ['./dashboard2.component.css']
})
export class Dashboard2Component implements OnInit, AfterViewInit {

  public queryinscadenza: any = {};
  public queryscadute: any = {};
  public queryconvinesecuzione: any = {};

  constructor(public service: DashboardService, private datePipe: DatePipe) {}

  ngAfterViewInit() {
  
  }

  ngOnInit(): void {
    
      const today = this.datePipe.transform(Date.now(), 'dd-MM-yyyy');   
      this.queryinscadenza.rules =  [
        { value: today, field: "data_tranche", operator: ">=", type: "date" },
        { value: 'pagato', field: "state", operator: "!=", type: "string" },
        { field: "convenzione.convenzione_from",  operator: "=", value: convenzioneFrom.dip}     
      ];     
            
      this.queryscadute.rules =  [
        { value: today, field: "data_tranche", operator: "<=", type: "date" },
        { value: 'pagato', field: "state", operator: "!=", type: "string" },
        { field: "convenzione.convenzione_from",  operator: "=", value: convenzioneFrom.dip}     
      ];     
      
      this.queryconvinesecuzione.rules = [        
          { value: today, operator: "<=", field: "data_inizio_conv",  type: "date" },
          { value: today,  operator: ">=", field: "data_fine_conv", type: "date" },       
          { field: "convenzione_from",  operator: "=", value: convenzioneFrom.dip}     
      ];
  }

}
