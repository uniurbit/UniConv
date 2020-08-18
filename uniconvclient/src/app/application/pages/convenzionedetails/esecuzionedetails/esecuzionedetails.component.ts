import { Component, OnInit, Input } from '@angular/core';
import { Convenzione } from 'src/app/application/convenzione';
import { ConvenzionedetailsComponent } from '../convenzionedetails.component';
import ControlUtils from 'src/app/shared/dynamic-form/control-utils';

@Component({
  selector: 'app-esecuzionedetails',
  templateUrl: './esecuzionedetails.component.html',
  styles: []
})
export class EsecuzionedetailsComponent implements OnInit {

  
  @Input() conv: Convenzione;
  
  constructor() { }

  ngOnInit() {
  }

  executed(){
    return ConvenzionedetailsComponent.executed(this.conv.current_place,'repertoriato');
  }

  executedtype() {
    //se la conv è in stato repertoriato 
    const delta = this.executed()
    if (delta >= 0){
      const current = new Date();     
      const data_inizio_conv = ControlUtils.toDate(this.conv.data_inizio_conv);
      if (data_inizio_conv <= current){
         //iniziata 
         const data_fine_conv = ControlUtils.toDate(this.conv.data_fine_conv);
         if (data_fine_conv >= current){
            //in esecuzione
            return 'success';
         }else{
            //finita
            return 'gray';
         }
      }
      
      //vedere se è in esecuzione la data corrente è compresa tra le due date
      //grigia se è scaduta la data corrente è superiore alla data di fine      
      return 'info';    
    }
    return 'normal';
          
  }

}
