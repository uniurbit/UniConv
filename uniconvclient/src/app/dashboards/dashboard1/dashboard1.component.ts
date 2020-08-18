import { Component, AfterViewInit, OnInit } from '@angular/core';
import { DashboardService } from '../dashboard.service';
import { Observable } from 'rxjs';
import { tap, map } from 'rxjs/operators';

@Component({
  templateUrl: './dashboard1.component.html',
  styleUrls: ['./dashboard1.component.css']
})
export class Dashboard1Component implements OnInit, AfterViewInit {
  
  constructor(public service: DashboardService) {}



  ngAfterViewInit() {
   
  }

  ngOnInit(): void {
  }

}
