import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../core';

@Component({
  selector: 'app-user-login',
  templateUrl: './user-login.component.html'  
})
export class UserLoginComponent implements OnInit {

  constructor(public authService: AuthService) {    
   }

  ngOnInit() {
  }

  login() {
    this.authService.login();
  }

  logout() {
    this.authService.logout();
  }

}
