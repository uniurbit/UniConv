import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { Observable } from 'rxjs';
import { map, take } from 'rxjs/operators';
import { AuthService } from './auth.service';

@Injectable()
export class AuthGuard implements CanActivate {

    constructor( private authService: AuthService, private router: Router) { }

    canActivate(
        next: ActivatedRouteSnapshot,
        state: RouterStateSnapshot
      ): Observable<boolean> {
        return this.authService.isLoggedIn         // {1}
          .pipe(
            take(1),                              // {2} 
            map((isLoggedIn: boolean) => {         // {3}
              if (!isLoggedIn){
              
                return false;
              }
              return true;
            })
          );
      }
}