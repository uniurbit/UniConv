import { Component, Input, AfterViewInit, OnInit, OnDestroy } from '@angular/core';
import { PerfectScrollbarConfigInterface } from 'ngx-perfect-scrollbar';
import { DashboardService } from '../../dashboard.service';
import { tap, takeUntil } from 'rxjs/operators';
import { Observable, Subject } from 'rxjs';
import { Router } from '@angular/router';
import { AppConstants } from 'src/app/app-constants';
import { TaskComponent } from 'src/app/application/components/task/task.component';


@Component({
  selector: 'app-tasklist',
  templateUrl: './tasklist.component.html',
  styleUrls: ['./tasklist.component.css']
})
export class TaskListComponent implements OnInit, AfterViewInit, OnDestroy {
 
  public config: PerfectScrollbarConfigInterface = {};

  isLoading: boolean = false;

  @Input()
  title: string;
  
  @Input() 
  typeresearch: string;

  model: any;

  page: {
    size: number;
    totalElements: any;
    pageNumber: any;
    previousPage: any;
  };
  
  protected onDestroy$ = new Subject<void>();
  
  constructor(protected service: DashboardService, protected router: Router) {}

  ngOnInit(): void {         
    this.loadData();        
  }
  
  ngOnDestroy(): void {
    this.onDestroy$.next();
    this.onDestroy$.complete();
  }

  ngAfterViewInit(): void {  
  }

  onClick(task){
    this.router.navigate(['home/tasks', task.id]);
  }
  
  onCheck(task){
    if (task.state !== 'aperto'){
      return;
    }

    const path = TaskComponent.pathEsecuzioneTask(task.workflow_transition,task.workflow_place);
    if (path != null)
      this.router.navigate([path, task.model_id]);
    
  }

  onOpen(task){
    if (task.model_type == 'App\\Convenzione'){
      //apriamo la vista della convenzione
      this.router.navigate(['home/convdetails', task.model_id]);
    }
    if (task.model_type == 'App\\Scadenza'){
      this.router.navigate(['home/scadenzeview', task.model_id]);
    }

  }

  loadPage(pageNumber: number) {    
    if (this.page && this.page.pageNumber !== this.page.previousPage) {
      this.page.previousPage = pageNumber;
      this.loadData();
    }
  }

  loadData() {        
    this.isLoading = true;
    const pageParam = this.page ? this.page.pageNumber : null;
    let obs: Observable<any>;
    if (this.typeresearch == 'mytasks'){
      obs = this.service.getUserTaskByCurrentUser(pageParam);
    }else if (this.typeresearch == 'myofficetasks') {
      obs = this.service.getUserTaskByCurrentUserOffice(pageParam)
    }

    obs.pipe(      
      takeUntil(this.onDestroy$),
      tap(res =>{         
        res.data.forEach(x => {
          if (x.closing_user){
            x.namelist = x.closing_user.name;
          }else{
            x.namelist = x.assignments.map(el => el.personale.nome + ' ' + el.personale.cognome)
            x.namelist = x.namelist.join(', ');     
          }
        });
        setTimeout(()=> {
          this.isLoading = false;
        }, 0);
      })
    ).subscribe(
      (res) => {        
        this.model = res.data;

        this.page = {
          totalElements: res.total,
          pageNumber: res.current_page,
          size: res.per_page,
          previousPage:  res.current_page,
        }        
      },
      (error) => {
        setTimeout(()=> {
          this.isLoading = false;
        }, 0);
      }
    );
  }

}
