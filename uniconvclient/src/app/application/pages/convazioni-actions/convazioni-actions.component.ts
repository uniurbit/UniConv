import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { Convenzione } from '../../convenzione';
import { SottoscrizioneComponent } from '../sottoscrizione.component';
import { Router } from '@angular/router';
import { RegistrazioneSottoscrizioneComponent } from '../registrazione/registrazione-sottoscrizione/registrazione-sottoscrizione.component';
import { ApplicationService } from '../../application.service';
import { ConfirmationDialogService } from 'src/app/shared/confirmation-dialog/confirmation-dialog.service';
import { FirmaDirettoreComponent } from '../firmadirettore.component';
import { FirmaControparteComponent } from '../firmacontroparte.component';
import { RepertoriazionedetailsComponent } from '../convenzionedetails/repertoriazionedetails/repertoriazionedetails.component';
import { BolloRepertoriazioneComponent } from '../bollorepertoriazione.component';
import { decode } from 'base64-arraybuffer';
import { ConvvalidationComponent } from '../convvalidation.component';
import { RegistrazioneCompletamentoControparteComponent } from '../registrazione/registrazione-completamento-controparte/registrazione-completamento-controparte.component';
import { RegistrazioneCompletamentoDirettoreComponent } from '../registrazione/registrazione-completamento-direttore/registrazione-completamento-direttore.component';
import { RegistrazioneBolloRepertoriazioneComponent } from '../registrazione/registrazione-bollo-repertoriazione/registrazione-bollo-repertoriazione.component';

export interface ActionItem {
  title?: string;  
  disabled?: boolean;
  text: string; 
  onClick(event: any);
  permissions: string[];
}


@Component({
  selector: 'app-convazioni-actions',
  templateUrl: './convazioni-actions.component.html',
  styles: []  
})
//ng g c application/pages/convazioniActions -s true --spec false
export class ConvazioniActionsComponent implements OnInit {

  private _model: Convenzione;

  get model(){
    return this._model;
  }

  @Input()
  set model(model: any){    

    this.items = this.actions[model.current_place];

    this._model = model;

  }

  @Output()
  change = new EventEmitter();

  @Output()
  loading = new EventEmitter();

  items: ActionItem[];

  actions: { [key:string]: ActionItem[] } = {
    'inapprovazione': [{
      title: 'Registra approvazione degli organi di Ateneo',
      text: 'Registra approvazione',       
      onClick: ($event) => {
        this.open(ConvvalidationComponent.ABSULTE_PATH);             
      },           
      permissions: ['ADMIN_AMM','ADMIN','SUPER-ADMIN'],
    }
    ],
    'approvato' : [     
        {
          title: 'Sottoscrizione effettuata ma non registrata a sistema',
          text: 'Registra sottoscrizione',       
          onClick: ($event) => {
            this.open(RegistrazioneSottoscrizioneComponent.ABSULTE_PATH);             
          },           
          permissions: ['ADMIN_AMM','ADMIN','SUPER-ADMIN'],
        },
        {
          title: 'Esegui sottoscrizione',
          text: 'Esegui sottoscrizione',
          onClick: ($event) => {
            this.open(SottoscrizioneComponent.ABSULTE_PATH);             
          },
          permissions: ['ADMIN_AMM','ADMIN','SUPER-ADMIN'],        
        },      
      ],
    'da_firmare_direttore': [        
        {
          title: 'Cancella sottoscrizione',
          text: 'Cancella sottoscrizione',       
          onClick: ($event) => {
            this.onCancellaSottoscrizione();             
          },     
          permissions: ['ADMIN_AMM','ADMIN','SUPER-ADMIN'],  
        },
        {
          title: 'Registra firma Uniurb, completamento sottoscrizione',
          text: 'Registra firma Uniurb',       
          onClick: ($event) => {
            this.open(RegistrazioneCompletamentoDirettoreComponent.ABSULTE_PATH);           
          },       
          permissions: ['SUPER-ADMIN'],
        },  
        {
          title: 'Esegui firma Uniurb, completamento sottoscrizione',
          text: 'Esegui firma Uniurb',       
          onClick: ($event) => {
            this.open(FirmaDirettoreComponent.ABSULTE_PATH);           
          },       
          permissions: ['ADMIN_AMM','ADMIN','SUPER-ADMIN'],
        },     
      ],
    'da_firmare_controparte2': [
        {
          title: 'Cancella sottoscrizione',
          text: 'Cancella sottoscrizione',       
          onClick: ($event) => {
            this.onCancellaSottoscrizione();             
          },       
          permissions: ['ADMIN_AMM','ADMIN','SUPER-ADMIN'],
        },
        {
          title: 'Registra firma della controparte, completamento sottoscrizione',
          text: 'Registra firma della controparte',       
          onClick: ($event) => {
            this.open(RegistrazioneCompletamentoControparteComponent.ABSULTE_PATH);           
          },       
          permissions: ['SUPER-ADMIN'],
        },       
        {
          title: 'Esegui firma della controparte, completamento sottoscrizione',
          text: 'Esegui firma della controparte',       
          onClick: ($event) => {
            this.open(FirmaControparteComponent.ABSULTE_PATH);           
          },       
          permissions: ['ADMIN_AMM','ADMIN','SUPER-ADMIN'],
        },       
      ],
      'firmato': [
        {
          title: 'Registra repertoriazione',
          text: 'Registra repertoriazione',       
          onClick: ($event) => {
            this.open(RegistrazioneBolloRepertoriazioneComponent.ABSULTE_PATH);     
          },       
          permissions: ['SUPER-ADMIN'],
        },      
        {
          title: 'Esegui repertoriazione',
          text: 'Esegui repertoriazione',       
          onClick: ($event) => {
            this.open(BolloRepertoriazioneComponent.ABSULTE_PATH);     
          },       
          permissions: ['ADMIN_AMM','ADMIN','SUPER-ADMIN'],
        },        
      ],
      'repertoriato': [
        {
          title: 'Scarica convenzione repertoriata',
          text: 'Scarica convenzione',       
          onClick: ($event) => {
            this.download();     
          },       
          permissions: ['ADMIN_AMM','ADMIN','SUPER-ADMIN','OP_CONTABILITA'],
        },        
      ],
      
    
  }
    
  

  constructor(protected router: Router, protected service: ApplicationService, protected confirmationDialogService: ConfirmationDialogService) { }

  ngOnInit() {   
  }

  protected open(path: string){
    if (path != null)
      this.router.navigate([path, this.model.id]); 
  }


  download() {
    const attach = this.model.attachments.find(x => x.attachmenttype_codice == 'DOC_BOLLATO_FIRMATO');
    if (!attach)
      return;

    this.service.download(attach.id).subscribe(file => {
      if (file.filevalue)
        var blob = new Blob([decode(file.filevalue)]);
      saveAs(blob, file.filename);
    },
      e => { console.log(e); }
    );

  }


  onCancellaSottoscrizione() {
    this.service.confirmationDialogService.confirm('Conferma', "Vuoi procedere con l'operazione di elminazione?" )
      .then((confirmed) => {
        if (confirmed){          
          this.loading.emit(true);
          this.service.cancellazioneSottoscrizione(this.model).subscribe(
            result => {                                      
              this.confirmationDialogService.confirm("Finestra messaggi", result.message, "Chiudi", null, 'lg');
              this.loading.emit(false);
              this.change.emit(result.data);
            },
            () =>{
              this.loading.emit(false);
              this.router.navigate(['home/convdetails', this.model.id]);
            }
          );


        }      
      })
      .catch(() => {
        
      });
    
  }


}
