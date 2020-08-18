import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'app-rangedetails',
  templateUrl: './rangedetails.component.html',
  styles: []
})
export class RangedetailsComponent implements OnInit {

  @Input() labeln: string = 'n.';
  @Input() numero: string;

  @Input() labelil: string = 'il';
  @Input() labeldel: string = 'del';
  @Input() data: string;

  constructor() { }

  ngOnInit() {
  }

 

}
