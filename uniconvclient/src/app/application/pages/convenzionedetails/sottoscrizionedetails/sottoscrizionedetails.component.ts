import { Component, OnInit, Input } from '@angular/core';
import { Convenzione } from 'src/app/application/convenzione';
import { ConvenzionedetailsComponent } from '../convenzionedetails.component';

interface IInfoSottoscrizione {  
  speditaDitta: IDoc;
  restituita: IDoc;
  arrivata:IDoc;
  rispedita:IDoc;
};

interface IDoc{  
  numero: string;
  data: string;
}

@Component({
  selector: 'app-sottoscrizionedetails',
  templateUrl: './sottoscrizionedetails.component.html',
  styles: []
})
export class SottoscrizionedetailsComponent implements OnInit {

  @Input() conv: Convenzione;

  sottr: IInfoSottoscrizione;
  
  constructor() { }

  ngOnInit() {
    this.sottr = new Object() as IInfoSottoscrizione;
      //LTU_FIRM_UNIURB
      //num_prot
      //emission_date
      if (this.conv.stipula_type=='uniurb'){      
        const file = this.conv.attachments.find(x => x.attachmenttype_codice == 'LTU_FIRM_UNIURB' || x.attachmenttype_codice == 'LTU_FIRM_UNIURB_PROT' )
        if (file) {
          const speditaDitta = {       
            data: file.emission_date.toString(),
            numero: file.num_prot        
          }                       
          this.sottr.speditaDitta = speditaDitta;       
        }else{
          const speditaDitta = {       
            data: this.conv.data_sottoscrizione.toString(),
            numero: null
          }
          this.sottr.speditaDitta = speditaDitta;  
        }
        
        if (this.executed()>=0){
          //LTE_FIRM_ENTRAMBI_PROT LTE_FIRM_ENTRAMBI
          const filerest = this.conv.attachments.find(x => x.attachmenttype_codice == 'LTE_FIRM_ENTRAMBI_PROT' ||  x.attachmenttype_codice == 'LTE_FIRM_ENTRAMBI')
          if (filerest) {
            let restituita = {        
              data: filerest.emission_date.toString(),
              numero: filerest.num_prot        
            }
            this.sottr.restituita = restituita;                      
          }else{
            const restituita = {        
              //data_sottoscrizione contiene la data di restituzione
              data: this.conv.data_sottoscrizione.toString(),
              numero: null            
            }
            this.sottr.restituita = restituita;               
          }
        }
      } 


      if (this.conv.stipula_type=='controparte'){
        const file = this.conv.attachments.find(x => x.attachmenttype_codice == 'LTE_FIRM_CONTR' || x.attachmenttype_codice == 'LTE_FIRM_CONTR_PROT')
        if (file) {
          const arrivata = {       
            data: file.emission_date.toString(),
            numero: file.num_prot        
          }
          this.sottr.arrivata = arrivata;                      
        }else{
          const arrivata = {       
            data: this.conv.data_sottoscrizione.toString(),
            numero: null       
          }
          this.sottr.arrivata = arrivata;   
        }
        //LTE_FIRM_ENTRAMBI_PROT LTE_FIRM_ENTRAMBI
        const filerest = this.conv.attachments.find(x => x.attachmenttype_codice == 'LTU_FIRM_ENTRAMBI_PROT' ||  x.attachmenttype_codice == 'LTU_FIRM_ENTRAMBI')
        if (filerest) {
          const rispedita = {        
            data: filerest.emission_date.toString(),
            numero: filerest.num_prot        
          }
          this.sottr.rispedita = rispedita;                      
        }
      } 



  }

  executed(){    
    return ConvenzionedetailsComponent.executed(this.conv.current_place,'firmato');
  }


  executedtype() {    
    //se la conv è in stato repertoriato 
    const delta = this.executed();

    if (delta >= 0){      
      return 'info';    
    }else if (delta == -1 || delta == -2 || delta==-3){
      return 'warning';
    }
    return 'normal';
          
  }

}
