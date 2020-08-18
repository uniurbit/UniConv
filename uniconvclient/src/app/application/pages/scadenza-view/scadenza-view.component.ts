import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import {Location} from '@angular/common';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { ScadenzaService } from '../../scadenza.service';

@Component({
  selector: 'app-scadenza-view',
  templateUrl: './scadenza-view.component.html',
  styles: []
})
export class ScadenzaViewComponent implements OnInit {

  static keyValueState:{ [key: string]: number}={
    'attivo': 0, 
    'inemissione': 1, 
    'emesso': 2,
    'inpagamento': 3,    
    'pagato': 4,    
  };

  isLoading: boolean=false;
  scad: any;
  onDestroy$ = new Subject<void>();

  constructor(private service: ScadenzaService, private route: ActivatedRoute, protected router: Router, protected location: Location) { }

  ngOnInit() {
    this.route.params.pipe(takeUntil(this.onDestroy$)).subscribe(params => {
      if (params['id']) {
        this.isLoading = true;
        this.service.clearMessage();
        this.service.getById(params['id']).subscribe((data) => {
          this.scad = data;          
          this.isLoading = false;         
        });
      }
    });
  }

  onOpen(){
    this.router.navigate(['home/convdetails', this.scad.convenzione_id]);
  }

  onModify(){
    this.router.navigate(['home/scadenze', this.scad.id]);
  }

  onBack(){
    this.location.back();
  }


    /**
   * 
   * @param currentstate stato attuale della convenzione
   * @param value valore per cui si vuole verificare se sia stato eseguito o meno
   */
  public static executed(currentstate, value){
    const current = ScadenzaViewComponent.keyValueState[currentstate];
    const comparevalue = ScadenzaViewComponent.keyValueState[value];

    return current - comparevalue;
  }
}
