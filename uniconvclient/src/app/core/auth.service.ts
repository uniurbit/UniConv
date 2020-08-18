
import { Injectable } from '@angular/core';
import { Observable, BehaviorSubject } from 'rxjs';
import { map, tap } from 'rxjs/operators';
import { nullSafeIsEquivalent } from '@angular/compiler/src/output/output_ast';
import { HttpClient, HttpHeaders, HttpResponse } from '@angular/common/http';
import { JwtHelperService } from '@auth0/angular-jwt';
import { Router } from '@angular/router';
import { NgxPermissionsService } from 'ngx-permissions';
import { AppConstants } from '../app-constants';

interface LoginResponse {
  accessToken: string;
  accessExpiration: number;
}

const httpOptions = {
  headers: new HttpHeaders({
    //'observe': 'response',    
    'Content-Type': 'text'
    //'Access-Control-Allow-Headers': 'Content-Type, X-Auth-Token, Authorization, X-Requested-With'
    //'Access-Control-Allow-Origin': '*'
  })
};

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  
  private authUrl: string; //= 'http://pcoliva.uniurb.it/api';
  private loggedIn = new BehaviorSubject<boolean>(false);
  
  _username: string = '';
  _roles: string[]  = [''];
  _id: number;
  _email: string = '';

  static TOKEN = 'token'

  constructor(private http: HttpClient, public jwtHelper: JwtHelperService, private router: Router, private permissionsService: NgxPermissionsService ) {
    this.loggedIn.next(this.isAuthenticated());
    this.authUrl = AppConstants.baseURL;
  }
 
  login() {      
    return this.http.get(`${this.authUrl}/loginSaml`,httpOptions)
      .subscribe(res => {
        console.log(res);
      })
  }

  loginWithToken(token: any){        
    localStorage.setItem(AuthService.TOKEN,token);
    this.loggedIn.next(this.isAuthenticated());
    this.reload()    
  }

  refreshToken() {
    return this.http.post<any>(`${this.authUrl}/api/auth/refreshtoken`, {
      'refreshToken': this.getToken()
    }).pipe(tap((data) => {
      this.storeJwtToken(data.token);
    }));
  }

  reload(): any {
    if (this.isAuthenticated()){
      const helper = new JwtHelperService();
      const decodedToken = helper.decodeToken(localStorage.getItem(AuthService.TOKEN));
      this._email = decodedToken['email'];
      this._username = decodedToken['name'];
      this._roles = decodedToken['roles']; 
      console.log(this.roles);     
      this._id = decodedToken['id'];    
      this.permissionsService.loadPermissions(this._roles);
    }
  }

  resetFields(){
    this._username = '';
    this._id = null;
    this._roles = [];
    this._email = '';
  }

  getToken(){
    return localStorage.getItem(AuthService.TOKEN)
  }

  /**
   * Log the user out
   */
  logout() {

    this.http.get(this.authUrl + "api/auth/logout", httpOptions)
    .subscribe(res => {
        console.log(res);
    });

    localStorage.removeItem(AuthService.TOKEN);
    localStorage.clear();
    this.permissionsService.flushPermissions();
    this.resetFields();
    this.loggedIn.next(false);
  }


  private storeJwtToken(jwt: string) {
      localStorage.setItem(AuthService.TOKEN, jwt);
  }

  /**
     * Check if the user is logged in
     */
  get isLoggedIn() {    
    return this.loggedIn.asObservable();
  }

  public get userid(): number{
    return this._id; 
  }

  public get email(): string {
    return this._email;
  }

  public get username(): string {
    return this._username;
  }

  public get roles(): string[] {
    return this._roles;
  }

  // ...
  public isAuthenticated(): boolean {

    const token = localStorage.getItem(AuthService.TOKEN);

    // Check whether the token is expired and return
    // true or false
    return !this.jwtHelper.isTokenExpired(token);
  }

  /**
   * Handle any errors from the API
   */
  private handleError(err) {
    let errMessage: string;

    return Observable.throw(errMessage);
  }
}
