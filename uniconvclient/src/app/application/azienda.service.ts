import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable, of, throwError } from 'rxjs';
import { map, catchError, tap } from 'rxjs/operators';
import { ControlBase, TextboxControl, DropdownControl, DateControl, MessageService, ServiceQuery } from '../shared';
import { ArrayControl } from '../shared/dynamic-form/control-array';
import { FormlyFieldConfig } from '@ngx-formly/core';
import { AppConstants } from '../app-constants';
import { Cacheable } from 'ngx-cacheable';


const httpOptions = {
  headers: new HttpHeaders({
    'Content-Type': 'application/json'
  })
};

@Injectable()
export class AziendaService implements ServiceQuery {

  _baseURL: string;

  getMetadata():FormlyFieldConfig[] {
    return  [
      {
        key: 'id_esterno',
        type: 'string',
        hideExpression: false,
        templateOptions: {
          label: 'Id',
          disabled: true,
          column: { width: 10, cellTemplate: 'valuecolumn'}
        }
      },
      {
        key: 'cd_tipo_ab',
        type: 'select',
        templateOptions: {
          label: 'Tipo',
          required: true,
          options:[
            { value:'PF', label:'Persone fisiche'},
            { value:'SC', label:'Soggetti collettivi'},
            { value:'DI', label:'Ditte Individuali'},
            { value:'UO', label:'Unità organizzative'}
          ],
          column: { cellTemplate: 'valuecolumn'}

        }        
      },
      {
        key: 'nome',
        type: 'string',
        templateOptions: {
          label: 'Nome',
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      },
      {
        key: 'cognome',
        type: 'string',
        templateOptions: {
          label: 'Cognome',
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      },
      {
        key: 'denominazione',
        type: 'string',
        templateOptions: {
          label: 'Denominazione',
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      },
      {
        key: 'cod_fis',
        type: 'string',
        templateOptions: {
          label: 'Codice fiscale',
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      },
      {
        key: 'part_iva',
        type: 'string',
        templateOptions: {
          label: 'Partita IVA',
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      },
      {
        key: 'rappresentante_legale',
        type: 'string',
        templateOptions: {
          label: 'Rappresentante legale',
          required: true,
          column: { cellTemplate: 'valuecolumn'}
        }
      }
    ];
    
  }

  @Cacheable()
  getById(id: any) {       
    return this.getAzienda(id);
  }

  constructor(private http: HttpClient, public messageService: MessageService ) {
    this._baseURL = AppConstants.baseApiURL;
  }
  
  clearMessage() {
    this.messageService.clear();
  }

  query(model): Observable<any> {    
    return this.http
      .post<any>(this._baseURL+'/aziende/query', model, httpOptions ).pipe(
        tap(sub => this.messageService.info('Ricerca effettuata con successo')),
        catchError(this.handleError('query'))
      );
  }

  export(model): Observable<any> {   
    const headers = new HttpHeaders({
      'Content-Type': 'application/json'
    })
    return this.http
      .post(this._baseURL + '/aziende/export', model,{ headers, responseType: 'text'}).pipe(
        tap(sub => this.messageService.info('Export effettuato con successo')),
        catchError(this.handleError('export'))
      );
  }

  exportxls(model): Observable<any> {
    return this.http
      .post(this._baseURL + `/exportxls`, model, { responseType: 'blob'}).pipe(
        tap(sub => this.messageService.info('Export effettuato con successo')),
        catchError(this.handleError('export'))
      );
  }

  getAzienda(id: number): Observable<any> {
    return this.http
      .get(this._baseURL+'/aziende/'+id.toString(),httpOptions).pipe(
        tap(sub => {
          if (sub)
            this.messageService.info('Lettura utente effettuata con successo')
          else 
            this.messageService.info('Azienda non trovata')
        }),
        catchError(this.handleError('getAzienda'))
      );
    }
  



  private handleError<T>(operation = 'operation', result?: T) {
    return (error: any): Observable<T> => {
      
      console.error(error); 
    
      this.messageService.error(`L'operazione di ${operation} è terminata con errori: ${error.message}`);

      if (operation=='remove')
        throw(error);

      return of(result as T);
    };
  }
}
