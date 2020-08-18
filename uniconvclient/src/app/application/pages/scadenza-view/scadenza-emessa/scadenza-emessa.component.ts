import { Component, OnInit, Input } from '@angular/core';
import { ScadenzaViewComponent } from '../scadenza-view.component';

import { encode, decode } from 'base64-arraybuffer';
import { ApplicationService } from 'src/app/application/application.service';

@Component({
  selector: 'app-scadenza-emessa',
  templateUrl: './scadenza-emessa.component.html',
  styles: []
})
export class ScadenzaEmessaComponent implements OnInit {

  @Input() scad: any;

  constructor(protected appService: ApplicationService) { }

  ngOnInit() {
  }

  
  executed(){
    return ScadenzaViewComponent.executed(this.scad.state,'emesso');
  }

  download() {
    if (!this.scad.tipo_emissione)
      return;

    console.log(this.scad.tipo_emissione);
    //la scadenza Fattura/Nota debito o richiesta pagamento
    const attach = this.scad.attachments.find(x => x.attachmenttype_codice == this.scad.tipo_emissione);
    if (!attach)
      return;
        
    this.appService.download(attach.id).subscribe(file => {
      if (file.filevalue)
        var blob = new Blob([decode(file.filevalue)]);
      saveAs(blob, file.filename);
    },
      e => { console.log(e); }
    );

  }
}
