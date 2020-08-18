import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable, of, throwError } from 'rxjs';
import { map, catchError, tap } from 'rxjs/operators';
import { ControlBase, TextboxControl, DropdownControl, DateControl, MessageService, ServiceQuery, BaseService } from '../shared';
import { ArrayControl } from '../shared/dynamic-form/control-array';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { AppConstants } from '../app-constants';
import { Cacheable } from 'ngx-cacheable';
import { ConfirmationDialogService } from '../shared/confirmation-dialog/confirmation-dialog.service';


const httpOptions = {
  headers: new HttpHeaders({
    'Content-Type': 'application/json'
  })
};

@Injectable()
export class UserTaskService extends BaseService {

  _baseURL: string;

  getMetadata(): FormlyFieldConfig[] {
    return [
    ];

  }


  constructor(protected http: HttpClient, public messageService: MessageService, public confirmationDialogService: ConfirmationDialogService) {
    super(http, messageService,confirmationDialogService);
    this.basePath = 'usertask';
  }

  @Cacheable()
  create(): Observable<any> {
    return this.http
      .get(this._baseURL + `/${this.basePath}/create`, httpOptions).pipe(
        tap(sub => {
          if (sub)
            this.messageService.info('Inizializzazione effettuata con successo')
          else
            this.messageService.info('Permesso non trovato')
        }),
        catchError(this.handleError('create'))
      );
  }

  @Cacheable()
  getOffices(category): Observable<any> {
    return this.http.get(this._baseURL + '/convenzioni/uffici/'+category, httpOptions).pipe(
      catchError(this.handleError('getOffices', []))
    );
  }

  @Cacheable()
  getValidationOffices(): Observable<any> {
    return this.http.get(this._baseURL + '/convenzioni/uffici/'+'validazione', httpOptions).pipe(
      catchError(this.handleError('getValidationOffices', []))
    );
  }

  @Cacheable()
  getPersonaleUfficio(id): Observable<any> {
    if (id){
      return this.http.get<any>(this._baseURL + '/convenzioni/personaleufficio/' + id.toString(), httpOptions)
      .pipe(
        map(x => {
          return x.map(el => {el.id = parseInt(el.id); return el; })
         }),
        catchError(this.handleError('getPersonaleUfficio', []))
      );
    } 
    return of([]);
  }

  @Cacheable()
  getUserTaskFiltredByUserId(userId: any) {
    return this.http
      .get(this._baseURL + '/usertask/' + userId.toString() + '/tasks', httpOptions).pipe(
        tap(sub => {
          if (sub)
            this.messageService.info('Lettura attività effettuata con successo')
          else
            this.messageService.info('Attività non trovata')
        }),
        catchError(this.handleError('getUserTask'))
      );
  }

  @Cacheable()
  getNextPossibleActionsFromTask(taskId: any): Observable<any> {
    return this.http
      .get(this._baseURL + '/usertask/' + taskId.toString() + '/actions', httpOptions).pipe(
        catchError(this.handleError('getUserTask'))
      );
  }


  getNextActions(id,model_type): Observable<any> {
    let url = null;
    switch (model_type) {
      case 'App\\Convenzione':
        url = `${this._baseURL}/convenzioni/${id}/actions`    
        break;
      case 'App\\Scadenza':
        url = `${this._baseURL}/scadenze/${id}/actions`    
        break;

      default:
        break;
    }
    
    return this.http.get(url, httpOptions);
  }


}
