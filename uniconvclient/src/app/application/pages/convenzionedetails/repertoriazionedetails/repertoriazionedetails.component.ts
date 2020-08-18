import { Component, OnInit, Input } from '@angular/core';
import { Convenzione } from 'src/app/application/convenzione';
import { ConvenzionedetailsComponent } from '../convenzionedetails.component';

interface IInfoRepertorio {  
  repertorio: IDoc;  
  bolli?: any[];
};

interface IDoc{  
  numero: string;
  data: string;   
}


@Component({
  selector: 'app-repertoriazionedetails',
  templateUrl: './repertoriazionedetails.component.html',
  styles: []
})
export class RepertoriazionedetailsComponent implements OnInit {

  @Input() conv: Convenzione;

  rep: IInfoRepertorio;
  
  constructor() { }

  ngOnInit() {
    this.rep = new Object() as IInfoRepertorio;
    this.rep.bolli = [];

    if (this.conv.bolli){
       this.rep.bolli = this.conv.bolli.map(x => {
         return {
          codice: x.tipobolli_codice,
          num_bolli: x.num_bolli
         }
       });
    }

    const file = this.conv.attachments.find(x => x.attachmenttype_codice == 'DOC_BOLLATO_FIRMATO')
    if (file) {
      let repertorio = {       
        data: file.emission_date.toString(),
        numero: file.num_rep        
      }
      this.rep.repertorio = repertorio;                      
    }
  }

  executed(){    
    return ConvenzionedetailsComponent.executed(this.conv.current_place,'repertoriato');
  }

  
}
