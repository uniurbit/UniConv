import { Injectable } from "@angular/core";
import { AppConstants } from "../app-constants";

import { MessageService, CoreSevice } from "../shared";
import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Observable } from "rxjs";
import { catchError } from "rxjs/operators";
import { AuthService } from "../core";


const httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json'
    })
  };

@Injectable()
export class DashboardService extends CoreSevice {

  constructor(protected http: HttpClient, public messageService: MessageService, public auth: AuthService) {
    super(http,messageService)
  }
  
  getUserTaskByCurrentUser(pageNumer): Observable<any> {
    const url = `${this._baseURL}/usertask/users/${this.auth._id}/tasks`;
    let headers = httpOptions.headers.append('x-refresh','true');
    return this.http        
      .get(url, { headers: headers, params: { page: pageNumer } }).pipe(
          catchError(this.handleError('getUserTaskByCurrentUser')),
      );
  }
 
  
  getUserTaskByCurrentUserOffice(pageNumer): Observable<any> {
    const url = `${this._baseURL}/usertask/users/${this.auth._id}/office/tasks`;
    let headers = httpOptions.headers.append('x-refresh','true');
    return this.http        
      .get(url, { headers: headers, params: { page: pageNumer } }).pipe(
          catchError(this.handleError('getUserTaskByUserOffice')),
      );
  }

  getNotifications(): Observable<any>{
      const url = `${this._baseURL}/notifications`;
      let headers = httpOptions.headers.append('x-refresh','true');
      return this.http        
      .get(url, { headers: headers }).pipe(
          catchError(this.handleError('getUserTaskByUserOffice')),
      );
  }
}