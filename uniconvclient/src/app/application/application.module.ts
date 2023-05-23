import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { NgModule, LOCALE_ID } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { FormsModule, ReactiveFormsModule }   from '@angular/forms';

//services
import { ApplicationService } from './application.service';

//components
import { ConvenzioneComponent } from './components/convenzione/convenzione.component';
import { AssignmentDetailPageComponent } from './pages/assignment-detail-page/assignment-detail-page.component';
import { SharedModule, HttpLoaderFactory } from '../shared';
import { NgxDatatableModule } from '@swimlane/ngx-datatable';
import { RouterModule } from '@angular/router';
import { AuthGuard, CoreModule } from '../core';
import { HomeComponent } from './home/home.component';
import { UsersComponent } from './components/user/users.component';
import { UserComponent } from './components/user/user.component';
import { LoadingModule } from 'ngx-loading';
import { ConvenzioniComponent } from './components/convenzione/convenzioni.component';
import { MultistepSchematipoComponent } from './pages/multistep-schematipo.component';
import { AllegatiComponent } from './components/convenzione/allegati.component';
import { UploadfileComponent } from './components/convenzione/uploadfile.component';
import { UserTaskDetailComponent } from './components/convenzione/user-task-detail.component';
import { UserTaskService } from './usertask.service';
import { RoleComponent } from './components/user/role.component';
import { PermissionComponent } from './components/user/permission.component';
import { RolesComponent } from './components/user/roles.component';
import { PermissionsComponent } from './components/user/permissions.component';
import { TipoPagamentiComponent } from './components/convenzione/tipopagamenti.component';
import { TipoPagamentoComponent } from './components/convenzione/tipopagamento.component';
import { TaskComponent } from './components/task/task.component';
import { TasksComponent } from './components/task/tasks.component';

import { registerLocaleData } from '@angular/common';
import localeIt from '@angular/common/locales/it';
import { ConvvalidationComponent } from './pages/convvalidation.component';
import { SottoscrizioneComponent } from './pages/sottoscrizione.component';
import { FirmaControparteComponent } from './pages/firmacontroparte.component';
import { FirmaDirettoreComponent } from './pages/firmadirettore.component';
import { AziendaLocComponent } from './components/convenzione/aziendaloc.component';
import { AziendaLocService } from './aziendaloc.service';
import { PersoneinterneTitulus } from './pages/personeinterne-titulus.component';
import { PersonaInternaService } from './personainterna.service';
import { StruttureInterneTitulus } from './pages/struttureinterne-titulus.component';
import { StrutturaInternaService } from './strutturainterna.service';
import { ClassificazioneComponent } from './components/classif/classificazione.component';
import { ClassificazioniComponent } from './components/classif/classificazioni.component';
import { MappingUfficiTitulus } from './components/mapping/mappinguffici.component';
import { MappingUfficioService } from './mappingufficio.service';
import { MappingUfficioTitulus } from './components/mapping/mappingufficio.component';
import { UnitaOrganizzativaService } from './unitaorganizzativa.service';
import { StruttureEsterneTitulus } from './pages/struttureesterne-titulus.component';
import { StrutturaEsternaService } from './strutturaesterna.service';
import { DocumentoService } from './documento.service';
import { DocumentiTitulus } from './pages/documenti-titulus.component';
import { AziendeLocComponent } from './components/convenzione/aziendeloc.component';
import { ScadenzaComponent } from './components/scadenza/scadenza.component';
import { ScadenzeComponent } from './components/scadenza/scadenze.component';
import { ScadenzaService } from './scadenza.service';
import { BolloRepertoriazioneComponent } from './pages/bollorepertoriazione.component';
import { RichiestaEmissioneComponent } from './pages/richiestaemissione.component';
import { EmissioneComponent } from './pages/emissione.component';
import { PagamentoComponent } from './pages/pagamento.component';
import { InvioRichiestaPagamentoComponent } from './pages/inviorichiestapagamento.component';
import { ConvenzionedetailsComponent } from './pages/convenzionedetails/convenzionedetails.component';
import { ApprovazionedetailsComponent } from './pages/convenzionedetails/approvazionedetails/approvazionedetails.component';
import { SottoscrizionedetailsComponent } from './pages/convenzionedetails/sottoscrizionedetails/sottoscrizionedetails.component';
import { RepertoriazionedetailsComponent } from './pages/convenzionedetails/repertoriazionedetails/repertoriazionedetails.component';
import { ScadenzedetailsComponent } from './pages/convenzionedetails/scadenzedetails/scadenzedetails.component';
import { EsecuzionedetailsComponent } from './pages/convenzionedetails/esecuzionedetails/esecuzionedetails.component';
import { RangedetailsComponent } from './pages/convenzionedetails/rangedetails/rangedetails.component';
import { LogAttivitaComponent } from './pages/logattivita.component';
import { LogAttivitaService } from './logattivita.service';
import { MappingRuoli } from './components/mappingruoli/mappingruoli.component';
import { MappingRuolo } from './components/mappingruoli/mappingruolo.component';
import { MappingRuoloService } from './mappingruolo.service';
import { FaseWrapperComponent } from './pages/convenzionedetails/fase-wrapper/fase-wrapper.component';
import { TranslateLoader, TranslateModule } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { ConvazioniActionsComponent } from './pages/convazioni-actions/convazioni-actions.component';
import { RegistrazioneSottoscrizioneComponent } from './pages/registrazione/registrazione-sottoscrizione/registrazione-sottoscrizione.component';
import { ScadenzaViewComponent } from './pages/scadenza-view/scadenza-view.component';
import { ScadenzaEmessaComponent } from './pages/scadenza-view/scadenza-emessa/scadenza-emessa.component';
import { ScadenzaInpagamentoComponent } from './pages/scadenza-view/scadenza-inpagamento/scadenza-inpagamento.component';
import { ScadenzaAzioniComponent } from './pages/scadenza-view/scadenza-azioni/scadenza-azioni.component';
import { InsConvAmmComponent } from './pages/ins-conv-amm.component';
import { RegistrazioneCompletamentoControparteComponent } from './pages/registrazione/registrazione-completamento-controparte/registrazione-completamento-controparte.component';
import { RegistrazioneCompletamentoDirettoreComponent } from './pages/registrazione/registrazione-completamento-direttore/registrazione-completamento-direttore.component';
import { RegistrazioneBolloRepertoriazioneComponent } from './pages/registrazione/registrazione-bollo-repertoriazione/registrazione-bollo-repertoriazione.component';
import { RepertorioService } from './repertorio.service';
import { LinkEsterniComponent } from './link-esterni/link-esterni.component';
import { PdfViewerModule } from 'ng2-pdf-viewer';
import { BolliComponent } from './components/bolli/bolli.component';
import { BolloService } from './bollo.service';

registerLocaleData(localeIt);
@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,    
    SharedModule,
    NgbModule,
    NgxDatatableModule,      
    RouterModule,
    LoadingModule,
    CoreModule,      
    PdfViewerModule,
  ], 
  exports: [
    ConvenzioneComponent,
    HomeComponent, UserComponent, ConvenzioniComponent, MultistepSchematipoComponent, AllegatiComponent, UploadfileComponent, UserTaskDetailComponent, 
    RoleComponent, PermissionComponent, RolesComponent, PermissionsComponent, TipoPagamentiComponent, TipoPagamentoComponent, TaskComponent,
    TasksComponent, ConvvalidationComponent, SottoscrizioneComponent, FirmaControparteComponent, FirmaDirettoreComponent, AziendaLocComponent, AziendeLocComponent, 
    PersoneinterneTitulus, StruttureInterneTitulus, ClassificazioneComponent, ClassificazioniComponent, MappingUfficiTitulus, MappingUfficioTitulus, StruttureEsterneTitulus,
    DocumentiTitulus, ScadenzaComponent, ScadenzeComponent, BolloRepertoriazioneComponent, RichiestaEmissioneComponent, EmissioneComponent, PagamentoComponent,
    InvioRichiestaPagamentoComponent, LogAttivitaComponent, MappingRuoli, MappingRuolo
  ],
  declarations: [        
    ConvenzioneComponent,
    ConvenzioniComponent,    
    AssignmentDetailPageComponent,    
    HomeComponent, UsersComponent, UserComponent, MultistepSchematipoComponent, AllegatiComponent, UploadfileComponent, UserTaskDetailComponent, 
    RoleComponent, PermissionComponent, RolesComponent, PermissionsComponent, TipoPagamentiComponent, TipoPagamentoComponent, TaskComponent, TasksComponent,
    ConvvalidationComponent, SottoscrizioneComponent, FirmaControparteComponent, FirmaDirettoreComponent,  AziendaLocComponent, AziendeLocComponent,
    PersoneinterneTitulus, StruttureInterneTitulus, ClassificazioneComponent, ClassificazioniComponent, MappingUfficiTitulus, MappingUfficioTitulus, StruttureEsterneTitulus,
    DocumentiTitulus, ScadenzaComponent, ScadenzeComponent, BolloRepertoriazioneComponent, RichiestaEmissioneComponent, EmissioneComponent, PagamentoComponent, 
    InvioRichiestaPagamentoComponent, ConvenzionedetailsComponent, ApprovazionedetailsComponent, SottoscrizionedetailsComponent, RepertoriazionedetailsComponent, 
    ScadenzedetailsComponent, EsecuzionedetailsComponent, RangedetailsComponent, LogAttivitaComponent, MappingRuoli, MappingRuolo, FaseWrapperComponent, ConvazioniActionsComponent, RegistrazioneSottoscrizioneComponent, ScadenzaViewComponent, ScadenzaEmessaComponent, ScadenzaInpagamentoComponent, ScadenzaAzioniComponent, InsConvAmmComponent, RegistrazioneCompletamentoControparteComponent, RegistrazioneCompletamentoDirettoreComponent, RegistrazioneBolloRepertoriazioneComponent,
    LinkEsterniComponent,
    BolliComponent

  ],
  providers: [ 
    { provide: LOCALE_ID, useValue: 'it' },
    ApplicationService,
    UserTaskService,
    AziendaLocService,
    PersonaInternaService,
    StrutturaInternaService,   
    MappingUfficioService,
    MappingRuoloService, 
    UnitaOrganizzativaService,
    StrutturaEsternaService,
    DocumentoService,
    RepertorioService,
    ScadenzaService,
    LogAttivitaService,
    BolloService,
    DatePipe,
  ], 
})
export class ApplicationModule { }
