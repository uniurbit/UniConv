import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router, NavigationCancel, NavigationEnd } from '@angular/router';
import { AuthService } from '../../core';
import { AppConstants } from 'src/app/app-constants';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',    
  styleUrls: ['./home.component.scss'],  
})
export class HomeComponent implements OnInit, OnDestroy {
 
  sidebarCollapsed = true;  
  _baseURL : string;
  
  onDestroy$ = new Subject<void>();

  constructor(private authService: AuthService, private router: Router) {
    
    this._baseURL = AppConstants.baseApiURL;
    let token = null;

    router.events.pipe(takeUntil(this.onDestroy$)).subscribe(s => {
      if (s instanceof NavigationEnd) {
        let params: URLSearchParams;
        if (s.url.includes('/home'))
          params = new URLSearchParams(s.url.split('/home')[1]);
        else{
          params = new URLSearchParams(s.url.split('/')[1]); 
        }
        token = params.get('token');        
        if (token){
            console.log("keep token");
            authService.loginWithToken(token);
            this.router.navigate(['home/convenzioni']);
          }else{
            console.log("no token");
          }    
      }
    });

   }

  ngOnInit() {

  }

  ngOnDestroy(): void {
    this.onDestroy$.next();
    this.onDestroy$.complete();
  }


  btnClick= function () {
        window.location.replace(this._baseURL+'loginSaml');        
  };



  
}
