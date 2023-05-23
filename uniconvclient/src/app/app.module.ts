import {NgbModule, NgbDateParserFormatter, NgbDateAdapter, NgbActiveModal} from '@ng-bootstrap/ng-bootstrap';

import { BrowserModule } from '@angular/platform-browser';
import { NgModule, ErrorHandler, Injector, APP_INITIALIZER } from '@angular/core';
import { FormsModule }   from '@angular/forms';
import { HttpClientModule, HttpClient} from '@angular/common/http';
import { ApplicationModule } from './application/application.module';
import { AppRoutingModule } from './app-routing/app-routing.module';
import { JwtModule } from '@auth0/angular-jwt';
import { NgxDatatableModule } from '@swimlane/ngx-datatable';
import { AppComponent } from './app.component';
import { ApplicationService } from './application/application.service';
import { ReactiveFormsModule } from '@angular/forms';
import { SharedModule, HttpLoaderFactory } from './shared/shared.module';
import { NgbDateCustomParserFormatter } from './NgbDateCustomParserFormatter';
import { NotFoundComponent } from './not-found-component/not-found.component';
import { CoreModule, HttpInterceptorProviders, AuthGuard, MessageCacheService, RequestCache, RequestCacheWithMap, GlobalErrorHandlerProviders } from './core';
import { Router } from '@angular/router';
import { NgbDateISOParserFormatter } from '@ng-bootstrap/ng-bootstrap/datepicker/ngb-date-parser-formatter';
import { NgbStringAdapter } from './NgbStringAdapter';
import { TableTypeComponent } from './shared/dynamic-form/table-type.component';
import { NgxPermissionsModule } from 'ngx-permissions';
import { UserService } from './application/user.service';
import { AziendaService } from './application/azienda.service';
import { TestTabComponent } from './application/pages/test-tab.component';
import { PdfViewerModule } from 'ng2-pdf-viewer';
import { MessageService } from './shared';
import { UploadfileComponent } from './application/components/convenzione/uploadfile.component';
import { environment } from 'src/environments/environment';
import { APP_BASE_HREF, LOCATION_INITIALIZED } from '@angular/common';
import { RoleService } from './application/role.service';
import { PermissionService } from './application/permission.service';

import { ToastrModule } from 'ngx-toastr';
import { AziendaLocService } from './application/aziendaloc.service';
import { PersonaInternaService } from './application/personainterna.service';
import { StrutturaInternaService } from './application/strutturainterna.service';
import { ClassificazioneService } from './application/classificazione.service';
import { MappingUfficioService } from './application/mappingufficio.service';
import { TipoPagamentoService } from './application/tipopagamento.service';
import { UnitaOrganizzativaService } from './application/unitaorganizzativa.service';
import { StrutturaEsternaService } from './application/strutturaesterna.service';
import { DocumentoService } from './application/documento.service';
import { LoginActivate } from './core/login.activate';
import { ScadenzaService } from './application/scadenza.service';
import { ConfirmationDialogService } from './shared/confirmation-dialog/confirmation-dialog.service';
import { ConfirmationDialogComponent } from './shared/confirmation-dialog/confirmation-dialog.component';
import { MappingRuoloService } from './application/mappingruolo.service';
import { FORMLY_CONFIG } from '@ngx-formly/core';
import { registerTranslateExtension } from './shared/translate.extension';
import { TranslateService, TranslateLoader, TranslateModule, MissingTranslationHandler } from '@ngx-translate/core';
import { RepertorioService } from './application/repertorio.service';
import { MyMissingTranslationHandler } from './shared/MyMissingTranslationHandler';
import { InputConfirmationDialogComponent } from './shared/input-confirmation-dialog/input-confirmation-dialog.component';
import { SettingsService } from './services/settings.service';


export function tokenGetter() {
  return localStorage.getItem('token');
}


export function appInitializerFactory(translate: TranslateService, injector: Injector) {
  return () => new Promise<any>((resolve: any) => {
    const locationInitialized = injector.get(LOCATION_INITIALIZED, Promise.resolve(null));
    locationInitialized.then(() => {
      const langToSet = 'it'
      translate.setDefaultLang('it');
      translate.use(langToSet).subscribe(() => {
        console.info(`Successfully initialized '${langToSet}' language.'`);
      }, err => {
        console.error(`Problem with '${langToSet}' language initialization.'`);
      }, () => {
        resolve(null);
      });
    });
  });
}

@NgModule({
  declarations: [
    AppComponent,
    NotFoundComponent,    
    TestTabComponent,        
],
  imports: [
    SharedModule.forRoot(), NgxPermissionsModule.forRoot(), NgbModule,
    BrowserModule, FormsModule, HttpClientModule, ApplicationModule, ReactiveFormsModule, 
    AppRoutingModule, CoreModule, NgxDatatableModule,   PdfViewerModule, ToastrModule.forRoot(),     
    JwtModule.forRoot({
      config: {
        tokenGetter: tokenGetter,
        whitelistedDomains: environment.whitelistedDomains, 
        blacklistedRoutes: environment.blacklistedRoutes, 
      }
    }),
    TranslateModule.forRoot({
      missingTranslationHandler: {provide: MissingTranslationHandler, useClass: MyMissingTranslationHandler},
      loader: {
        provide: TranslateLoader,
        useFactory: HttpLoaderFactory,
        deps: [HttpClient],
      },
    }),
  ],  
  providers: [
    NgbActiveModal,
    AuthGuard,  
    LoginActivate,
    ApplicationService,
    UserService,
    AziendaService,
    MessageService,
    MessageCacheService,  
    RoleService,
    PermissionService,
    TipoPagamentoService,
    ClassificazioneService,     
    ConfirmationDialogService, 
    SettingsService,
    { provide: RequestCache, useClass: RequestCacheWithMap },
    HttpInterceptorProviders,
    GlobalErrorHandlerProviders,        
    {provide: 'repertorioService', useClass: RepertorioService },
    {provide: 'documentoService', useClass: DocumentoService },
    {provide: 'userService', useClass: UserService},
    {provide: 'applicationService', useClass: ApplicationService},
    {provide: 'aziendaService', useClass: AziendaService},
    {provide: 'aziendaLocService', useClass: AziendaLocService},
    {provide: 'personainternaService', useClass: PersonaInternaService},
    {provide: 'strutturainternaService', useClass: StrutturaInternaService},
    {provide: 'strutturaesternaService', useClass: StrutturaEsternaService},
    {provide: 'mapppingufficititulusService', useClass: MappingUfficioService},
    {provide: 'unitaorganizzativaService', useClass: UnitaOrganizzativaService},
    {provide: 'scadenzaService', useClass: ScadenzaService},    
    {provide: 'mappingruoloService', useClass: MappingRuoloService},
    {provide: 'roleService', useClass: RoleService},    
    {provide: NgbDateAdapter, useClass: NgbStringAdapter},
    {provide: NgbDateParserFormatter, useClass: NgbDateCustomParserFormatter},
    {provide: APP_BASE_HREF, useValue: environment.baseHref},   
    {  
      provide: APP_INITIALIZER,
      useFactory: appInitializerFactory,
      deps: [TranslateService, Injector],
      multi: true
    }     
  ],
  bootstrap: [AppComponent],
  entryComponents: [UploadfileComponent, ConfirmationDialogComponent, InputConfirmationDialogComponent],

})


export class AppModule {

   constructor(translate: TranslateService) {    
    }

}

