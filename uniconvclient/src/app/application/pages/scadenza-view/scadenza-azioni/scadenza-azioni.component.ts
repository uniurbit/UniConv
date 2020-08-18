import { Component, OnInit, Input } from '@angular/core';
import { ActionItem } from '../../convazioni-actions/convazioni-actions.component';
import { ConfirmationDialogService } from 'src/app/shared/confirmation-dialog/confirmation-dialog.service';
import { ScadenzaService } from 'src/app/application/scadenza.service';
import { Router } from '@angular/router';
import { RichiestaEmissioneComponent } from '../../richiestaemissione.component';
import { InvioRichiestaPagamentoComponent } from '../../inviorichiestapagamento.component';
import { PagamentoComponent } from '../../pagamento.component';

@Component({
  selector: 'app-scadenza-azioni',
  templateUrl: './scadenza-azioni.component.html',
  styles: []
})
export class ScadenzaAzioniComponent implements OnInit {

  private _model: any;

  get model() {
    return this._model;
  }

  @Input()
  set model(model: any) {
    this.items = this.actions[model.state];

    this._model = model;
  }

  items: ActionItem[];

  actions: { [key: string]: ActionItem[] } = {
    'attivo': [
      {
        title: 'Richiesta emissione',
        text: 'Richiesta emissione',
        onClick: ($event) => {          
          this.open(RichiestaEmissioneComponent.ABSULTE_PATH);
        },
        permissions: ['ADMIN','SUPER-ADMIN'],
      },
      {
        title: 'Invio richiesta di pagamento',
        text: 'Invio richiesta pagamento',
        onClick: ($event) => {
          this.open(InvioRichiestaPagamentoComponent.ABSULTE_PATH);
        },
        permissions: ['ADMIN','SUPER-ADMIN'],
      },
    ],
    'inemissione': [
      {
        title: 'Visualizza richiesta di emissione',
        text: 'Visualizza richiesta emissione',
        onClick: ($event) => {
          this.openTaskRichiestaEmissione();
        },
        permissions: ['ADMIN','SUPER-ADMIN'],
      },
    ],
    'inpagamento':[
      {
        title: 'Registra incasso',
        text: 'Registra incasso',
        onClick: ($event) => {
          this.open(PagamentoComponent.ABSULTE_PATH);
        },
        permissions: ['ADMIN','SUPER-ADMIN'],
      }
    ]
  }

  constructor(protected router: Router, protected service: ScadenzaService, protected confirmationDialogService: ConfirmationDialogService) { }

  ngOnInit() {
  }

  protected open(path: string) {
    if (path != null)
      this.router.navigate([path, this.model.id]);
  }

  openTaskRichiestaEmissione(){
    if (this.model.usertasks){
      const task =(this.model.usertasks as Array<any>).find(x => x.workflow_place == "inemissione")
      if (task){
        this.router.navigate(['home/tasks', task.id]);
      }
    }
  }

}
