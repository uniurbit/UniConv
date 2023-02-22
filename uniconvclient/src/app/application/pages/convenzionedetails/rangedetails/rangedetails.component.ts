import { Component, OnInit, Input } from '@angular/core';
import { NumberFloatingFilterComp } from 'ag-grid-community/dist/lib/filter/floatingFilter';
import { ApplicationService } from 'src/app/application/application.service';
import { encode, decode } from 'base64-arraybuffer';
import { InfraMessageType } from 'src/app/shared/message/message';

@Component({
  selector: 'app-rangedetails',
  templateUrl: './rangedetails.component.html',
  styles: []
})
export class RangedetailsComponent implements OnInit {

  isLoading = false;

  @Input() labeln: string = 'n.';
  @Input() numero: string;

  @Input() labelil: string = 'il';
  @Input() labeldel: string = 'del';
  @Input() data: string;
  @Input() id: any = null;

  constructor(protected appService: ApplicationService) { }

  ngOnInit() {
  }

  download(id) {
    if (id) {
      this.isLoading = true;
      this.appService.download(id).subscribe(file => {
        this.isLoading = false;
        if (file && file.filevalue){
          var blob = new Blob([decode(file.filevalue)]);
          saveAs(blob, file.filename);
        } else {          
          this.appService.messageService.add(InfraMessageType.Info, "Errore nel download del file");
        }
        
      },
        e => { 
          this.isLoading = false; 
          console.log(e); 
        }
      );
    }
  }

}
