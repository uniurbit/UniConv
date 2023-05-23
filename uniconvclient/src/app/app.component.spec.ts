import { TestBed, async } from '@angular/core/testing';
import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing/app-routing.module';
import { CoreModule } from './core';
import { NgxDatatableModule } from '@swimlane/ngx-datatable';
import { NgxPermissionsModule } from 'ngx-permissions';
import { PdfViewerModule } from 'ng2-pdf-viewer';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { SharedModule } from './shared';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { ToastrModule } from 'ngx-toastr';
import { NotFoundComponent } from './not-found-component/not-found.component';
import { ApplicationModule } from './application/application.module';
import { TestTabComponent } from './application/pages/test-tab.component';
import { JwtModule } from '@auth0/angular-jwt';
import { tokenGetter } from './app.module';
import { environment } from 'src/environments/environment';


describe('AppComponent', () => {
  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [
        AppComponent,
        NotFoundComponent,  
        TestTabComponent,           
      ],
      imports: [
        BrowserModule, FormsModule, HttpClientModule, ApplicationModule, ReactiveFormsModule, SharedModule, NgbModule, 
        AppRoutingModule, CoreModule, NgxDatatableModule,  NgxPermissionsModule.forRoot(), PdfViewerModule, ToastrModule.forRoot(),        
        JwtModule.forRoot({
          config: {
            tokenGetter: tokenGetter,
            whitelistedDomains: environment.whitelistedDomains, // ['localhost:4200', 'pcoliva.uniurb.it','unidemdev.uniurb.it'],
            blacklistedRoutes: environment.blacklistedRoutes, //['localhost:4200/auth/']
          }
        })    
      ]
    }).compileComponents();
  }));

  it('should create the app', async(() => {
    const fixture = TestBed.createComponent(AppComponent);
    const app = fixture.debugElement.componentInstance;
    expect(app).toBeTruthy();
  }));

  it(`should have as title 'app'`, async(() => {
    const fixture = TestBed.createComponent(AppComponent);
    const app = fixture.debugElement.componentInstance;
    expect(app.title).toEqual('UniConv client');
  }));

  // it('should render title in a h1 tag', async(() => {
  //   const fixture = TestBed.createComponent(AppComponent);
  //   fixture.detectChanges();
  //   const compiled = fixture.debugElement.nativeElement;
  //   expect(compiled.querySelector('h1').textContent).toContain('Welcome to uniconvclient!');
  // }));
});
