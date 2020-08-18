import { Component, OnInit, Input } from '@angular/core';
import { ScadenzaViewComponent } from '../scadenza-view.component';

@Component({
  selector: 'app-scadenza-inpagamento',
  templateUrl: './scadenza-inpagamento.component.html',
  styles: []
})
export class ScadenzaInpagamentoComponent implements OnInit {

  @Input() scad: any;
  
  constructor() { }

  ngOnInit() {
  }

  
  executed(){
    return ScadenzaViewComponent.executed(this.scad.state,'pagato');
  }
}
