
import { Component, OnInit, OnDestroy, Input, TemplateRef, ViewChild, Sanitizer, ChangeDetectorRef, AfterContentChecked } from '@angular/core';
import { FormGroup, FormControl, FormArray, NgForm, Validators, Form } from '@angular/forms';
import { ApplicationService } from '../../application.service';
import { FormlyFormOptions, FormlyFieldConfig } from '@ngx-formly/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Convenzione, convenzioneFrom, rinnovoType } from '../../convenzione';
import { Subject, of, onErrorResumeNext } from 'rxjs';
import { encode, decode } from 'base64-arraybuffer';
import { takeUntil, startWith, tap, distinctUntilChanged, filter, map, finalize } from 'rxjs/operators';
import { UploadfileComponent } from './uploadfile.component';
import { NgbModal, NgbActiveModal, NgbTabset } from '@ng-bootstrap/ng-bootstrap';
import { CurrencyPipe, DecimalPipe } from '@angular/common';
import { MycurrencyPipe } from 'src/app/shared/pipe/custom.currencypipe';
import { HttpParams } from '@angular/common/http';
import {Location} from '@angular/common';
import { InfraMessageType } from 'src/app/shared/message/message';
import ControlUtils from 'src/app/shared/dynamic-form/control-utils';
import { ConfirmationDialogService } from 'src/app/shared/confirmation-dialog/confirmation-dialog.service';
import { AuthService } from 'src/app/core';
import { MyTranslatePipe } from 'src/app/shared/pipe/custom.translatepipe';
import { TranslateService } from '@ngx-translate/core';
import { MyFlattenPipe } from 'src/app/shared/pipe/custom.flattenpipe';
import { ScadenzaService } from '../../scadenza.service';


@Component({
  selector: 'app-convenzione',
  templateUrl: './convenzione.component.html',
})

export class ConvenzioneComponent implements OnInit, OnDestroy, AfterContentChecked {

  @ViewChild('statetemplate', { static: true }) statetemplate: TemplateRef<any>;
  @ViewChild('stateattivita', { static: true }) stateattivita: TemplateRef<any>;
  //bottoni
  @ViewChild('apri', { static: true }) apri: TemplateRef<any>;
  @ViewChild('comandi', { static: true }) comandi: TemplateRef<any>;

  @ViewChild('tabs', { static: true })
  private tabs: NgbTabset;

  onDestroy$ = new Subject<void>();
  form = new FormArray([0, 1, 2, 3, 4].map(() => new FormGroup({})));

  model: Convenzione;
  modelUserTaskDetail: any;

  transitions = new Subject<any>();
  
  translate: MyTranslatePipe;
  flatten = new MyFlattenPipe('nome_utente');
  currency = new MycurrencyPipe();

  returnUrl: string = null;

  locationBack: Boolean = true;

  //caricati dal service
  fields: FormlyFieldConfig[] = [
    {
      wrappers: ['riquadro'],
      templateOptions: {
        title: 'Fase processo'
      },
      fieldGroup: [
      {
        key: 'id',
        type: 'input',       
        templateOptions: {
          label: 'Codice convenzione',
          disabled: true
        },
        hideExpression(model,formState){
          return !model.id;
        }
      },
    
      {
        type: 'select',
        key: 'transition',
        defaultValue: 'self_transition',
        templateOptions: {
          label: 'Stato',
          options: [],
        },
        hooks: {
          onInit: (field) => {
            this.transitions.subscribe(d => {
              field.templateOptions.options = d;
              field.templateOptions.disabled = false;
              field.formControl.setValue('self_transition');
            }
            );
          }
        }

      },
      ]
    }
  ];
  
  fieldsattachment: FormlyFieldConfig[] = [
    {
      className: 'section-label',
      template: '<h5>Lista allegati</h5>',
    },
    {
      type: 'button',
      templateOptions: {
        text: 'Nuovo',
        btnType: 'btn btn-sm btn-outline-primary rounded-lg',
        icon: 'oi oi-document',
        onClick: ($event) => this.open()
      },
      expressionProperties: {
        'templateOptions.disabled': (model: any, formState: any) => {                        
            return !!model.deleted_at
        },
      },
      hideExpression: (model: any, formState: any) => {
        if (!this.canActivate())
          return true;
      }    
    },
    {
      key: 'attachments',
      type: 'repeat',
      templateOptions: {
        btnHidden: true,
        btnRemoveHiddenFunc: (raw) => {
          if (!this.canActivate())
            return true;
          if (raw.attachmenttype_codice)
            return  !UploadfileComponent.fileToBeUploaded.includes(raw.attachmenttype_codice);
          return false;
        },
        onRemove: (id) => {
          this.isLoading = true;
          return this.service.deleteFile(id).pipe(
            tap(() => {
              this.tabs.select('tab-selectbyconvenzione');
              this.isLoading = false;
            }),
            finalize(() =>  this.isLoading = false)
          );
        },
      },
      hideExpression: (model: any, formState: any) => {        
        return this.model.attachments == null || this.model.attachments.length == 0
      },
      fieldArray: {
        fieldGroup: [
          {
          wrappers: ['riquadro'],
          templateOptions: {
            title: 'Dati allegato'
          },
          fieldGroup: [
          //nome allegato, tipo allegato, data ora creazione
            {
              fieldGroupClassName: 'row',            
              fieldGroup: [
                {
                  type: 'input',
                  key: 'id',
                  templateOptions: {
                    type:'hidden',
                    disabled: true,
                  },
                },
                {
                  className: 'col-md-3',
                  type: 'input',
                  key: 'filename',
                  templateOptions: {
                    label: "Nome dell'allegato",
                    disabled: true,
                  },
                },
                {
                  type: 'input',
                  key: 'attachmenttype.descrizione',
                  className: 'col-md-3',
                  templateOptions: {
                    label: 'Tipologia',
                    disabled: true,
                  },
                },
                {
                  type: 'input',
                  key: 'created_at',
                  className: 'col-md-3',
                  templateOptions: {
                    label: 'Data e ora di creazione',
                    disabled: true,
                  },
                },
                {
                fieldGroupClassName: 'btn-toolbar',   
                className: 'col-md-3 btn-group',
                fieldGroup: [
                {
                  type: 'button',
                  className: "mt-4 pt-2",
                  templateOptions: {
                    btnType: 'primary oi oi-data-transfer-download',
                    title: 'Scarica documento',
                    onClick: ($event, model) => this.download($event, model),
                  },
                  hideExpression: (model: any, formState: any) => {
                    return model.filetype == 'empty';
                },                                
                },
                {
                  type: 'button',
                  className: "ml-2 mt-4 pt-2",
                  templateOptions: {
                    btnType: 'primary oi oi-external-link',
                    title: 'Apri pagina esterna',
                    onClick: ($event, model) => {
                      let titulus = window.open('', '_blank'); 
                      this.service.getTitulusDocumentURL(model.id).subscribe(
                        (data)=> titulus.location.href = data.url, 
                        (error) => { 
                          titulus.close(); 
                          console.log(error);
                        }                                            
                      );
                      
                    },
                  },      
                  hideExpression: (model: any, formState: any) => {
                    return !model.num_prot;
                },          
                },
              ],
              },
              ],
            },
            //numero protocolollo e data protocollo
            {
              fieldGroupClassName: 'row',
              fieldGroup: [
                {
                  className: 'col-md-3',
                  type: 'input',
                  key: 'num_prot',
                  templateOptions: {
                    label: "Numero protocollo",
                    disabled: true,
                  },
                  hideExpression(model,formState){
                    return !model.num_prot;
                  }
                },
                {
                  key: 'docnumber',
                  type: 'input',
                  className:'col-md-3',
                  templateOptions: {
                    label: 'Numero',
                    disabled: true,                                                 
                  },
                  hideExpression(model,formState){
                    return !model.docnumber;
                  }
                },
                {
                  className: 'col-md-3',
                  type: 'input',
                  key: 'emission_date',
                  templateOptions: {
                    label: "Data emissione",
                    disabled: true,
                  },
                  hideExpression(model,formState){
                    return !model.emission_date;
                  }
                },
                {
                  className: 'col-md-3',
                  type: 'input',
                  key: 'num_rep',
                  templateOptions: {
                    label: "Numero repertorio",
                    disabled: true,
                  },
                  hideExpression(model,formState){
                    return !model.num_rep;
                  }
                },
              ]
            }
          ]
          }
        ],
      }
    }
  ]
  
  fieldsusertask: FormlyFieldConfig[];
  
  //fieldstask: FormlyFieldConfig[];

  fieldscadenze: FormlyFieldConfig[] = [
    {
      className: 'section-label',
      template: '<h5>Scadenze</h5>',
    },
    {
      type: 'button',
      templateOptions: {
        text: 'Nuova',
        btnType: 'btn btn-sm btn-outline-primary rounded-lg',
        icon: 'oi oi-document',
        onClick: ($event) => {
          this.router.navigate(['home/scadenze/new'], {
            queryParams: {
              returnUrl: this.router.url,
              initObj: JSON.stringify({ convenzione: { id: this.model.id, descrizione_titolo: this.model.descrizione_titolo } })
            }
          });
        }
      },
      expressionProperties: {
        'templateOptions.disabled': (model: any, formState: any) => {                        
            return !!model.deleted_at
        },
      },
      hideExpression: (model: any, formState: any) => {
        if (!this.canActivate())
          return true;
      } 
    },
    {
      key: 'scadenze',
      type: 'datatablegroup',    
      templateOptions: {
        btnHidden: true,
        label: 'Scadenze',
        hidetoolbar: true,   
        limit: "20",
        groupExpansionDefault: true,
        enableSummary: true,
        summaryPosition:'bottom',
        groupHeaderTitle: (group) => this.groupHeaderTitle(group),
        columns: [
          { name: '', prop: 'id',  with: 90, maxWidth: 90 },
          { name: '#', prop: 'id', summaryFunc:  null, width: 60, maxWidth:100 },
          { name: 'Tranche prevista', prop: 'data_tranche',summaryFunc:  null, },
          { name: 'Stato', prop: 'state', summaryFunc: null },
          { 
            name: 'Importo', prop: 'dovuto_tranche',
            cellClass: "text-right", summaryFunc: (cells) => this.sumImporto(cells), maxWidth:'150', pipe: this.currency,
          },
        ],
        onDblclickRow: (event) => {
          if (event.row.id) {
            this.router.navigate(['home/scadenze', event.row.id], {
              queryParams: {
                returnUrl: this.router.url,
              }
            });
          }
        },
      },
      fieldArray: {
        fieldGroup: []
      }
    }

  ];

  options: Array<FormlyFormOptions> = [0, 1, 2, 3, 4].map(() => ({
    formState: {
      isLoading: false,
    },
  }));


  private id: number;

  defaultColDef = { editable: true };

  private _isLoading: boolean = false;

  get isLoading(): boolean {
    return this._isLoading;
  }

  set isLoading(value: boolean) {
    this._isLoading = value;
    this.options.forEach(tabOptions => tabOptions.formState.isLoading = value);
  }

  canActivate(){
    return this.authService._roles.some((r) => ['ADMIN_AMM','ADMIN','SUPER-ADMIN'].includes(r));
  }

  constructor(
    protected scadenzaService: ScadenzaService, 
    public confirmationDialogService: ConfirmationDialogService, 
    private service: ApplicationService, 
    protected authService: AuthService,
    private route: ActivatedRoute, 
    protected router: Router, 
    private modalService: NgbModal, 
    public activeModal: NgbActiveModal,        
    protected location: Location,
    private translateService: TranslateService,
    private ref: ChangeDetectorRef) 
  {
    this.translate = new MyTranslatePipe(translateService);
    //modello vuoto
    this.model = {
      transition: 'self_transition',
      schematipotipo: 'schematipo',
      user_id: null,
      id: null,
      descrizione_titolo: '',
      dipartimemto_cd_dip: '',
      nominativo_docente: '',
      emittente: '',
      user: { id: null, name: null },
      dipartimento: { cd_dip: null, nome_breve: '' },
      stato_avanzamento: null,
      convenzione_type: 'TO',
      tipopagamento: { codice: null, descrizione: '' },
      azienda: { id: null, denominazione: '' },
      unitaorganizzativa_uo: '',
      aziende: [],
      convenzione_from: null,
      rinnovo_type: rinnovoType.non_rinnovabile,
    }

    this.fieldsusertask = [
      {
        className: 'section-label',
        template: '<h5>Attività associate</h5>',
      },
      {
        key: 'usertasks',
        type: 'datatable',     
        templateOptions: {
          btnHidden: true,
          label: 'Attività associate',
          hidetoolbar: true,
          limit: "20",
          onDblclickRow: (event) => {
            if (event.row.id) {
              this.router.navigate(['home/tasks/', event.row.id]);
            }
          },
          columns: [
            { name: 'Oggetto', prop: 'subject', wrapper: 'value' },
            { name: 'Stato', prop: 'state', wrapper: 'value' },
            { name: 'Ufficio', prop: 'unitaorganizzativa_uo', wrapper: 'value', pipe: this.translate },
            { name: 'Utente', prop: 'assignments', wrapper: 'value', pipe: this.flatten },
            { name: 'Data e ora', prop: 'updated_at', wrapper: 'value' }
          ]
        },
        fieldArray: {
          fieldGroup: []
        }
      }
  
    ];

    // this.fieldstask = [
    //   {
    //     className: 'section-label',
    //     template: '<h5>Storia eventi</h5>',
    //   },
    //   {
    //     key: 'logtransitions',
    //     type: 'datatable',
    //     templateOptions: {
    //       btnHidden: true,
    //       label: 'Storia eventi',
    //       hidetoolbar: true,
    //       limit: "20",
    //       columns: [
    //         { name: 'Transizione', prop: 'transition_leave', wrapper: 'value', pipe: this.translate },
    //         { name: 'Effettuata', prop: 'updated_at', wrapper: 'value' },
    //       ],
    //     },
    //     fieldArray: {
    //       fieldGroup: []
    //     }
    //   }
  
    // ];

    this.fields = this.fields.concat(service.getInformazioniDescrittiveFields(this.model,0)); 
  }

  get isNew(): boolean {
    return this.model == null || this.model.id == null
  }

  ngOnInit() {

    let cols: (Array<any>) = this.fieldscadenze.find(x => x.key == "scadenze").templateOptions.columns;
    cols.find(x => x.prop == 'state').cellTemplate = this.statetemplate; 
    cols.find(x => x.prop == 'id' && x.name=='').cellTemplate = this.comandi;

    cols= this.fieldsusertask.find(x => x.key == "usertasks").templateOptions.columns;
    cols.find(x => x.prop == 'state').cellTemplate = this.stateattivita;

    this.route.params.pipe(takeUntil(this.onDestroy$)).subscribe(params => {
      if (params['id']) {
        this.isLoading = true;
        this.service.clearMessage();
        this.service.getConvenzioneById(params['id']).subscribe((data) => {
          try {
            if (!data.azienda) {
              data.azienda = { id: null, denominazione: '' };
            }

            this.updateTransition(data.id);

            //this.options.every(tabOptions => { if (tabOptions.resetModel) return false; else return true; } )

            //Nota viene creata solo l'option del ng-tab attivo. Evito di fare il ciclo sulle tab.
            if (this.options[0].resetModel) {
              //la resetmodel imposta tutti i valori del modello.
              this.options[0].resetModel(data)
            } else {
              this.model = data;              
            }


            this.isLoading = false;
          } catch (e) {
            console.log(e);
            this.isLoading = false;
          }
        });
      }
    });
  }

  ngOnDestroy(): void {
    this.onDestroy$.next();
    this.onDestroy$.complete();
  }

  ngAfterContentChecked() {
    this.ref.detectChanges();
  }

  onNew() {
    this.form.reset();
  }

  onReload() {
    //sono nello stato nuovo
    if (this.model != null && this.model.id !== null) {
      this.isLoading = true;
      this.service.getConvenzioneById(this.model.id).subscribe((data) => {
        if (!data.azienda) {
          data.azienda = { id: null, descrizione: null };
        }
        this.options.forEach(tabOptions => { if (tabOptions.resetModel) tabOptions.resetModel(data); });
        this.isLoading = false;
      });
    }
  }

  onSubmit() {
    if (this.form.valid) {
      this.isLoading = true;
      var tosubmit = { ...this.model, ...this.form.value };
      this.service.update(tosubmit, tosubmit.id).subscribe(
        result => {
          try {
            if (!result.azienda) {
              result.azienda = { id: null, descrizione: null };
            }

            this.options.forEach(tabOptions => { if (tabOptions.resetModel) tabOptions.resetModel(result); });
            this.updateTransition(result.id);

            this.isLoading = false;
          } catch (error) {
            this.onError(error);
          }
        },
        error => this.onError(error),
      );
    }
  }

  protected updateTransition(id) {        
    this.service.getNextActions(id).subscribe((data) => {
      this.transitions.next([]);
      this.transitions.next(data);
    });
  }

  private onError(error) {
    this.isLoading = false;
    this.service.messageService.error(error);
    console.log(error)
  }

  onGenerate() {
    if (this.form.valid) {
      this.service.generatePDF(this.model.id);
    }
  }


  private temp = [];
  updateFilter(event) {
    const val = event.target.value.toLowerCase();

    const temp = this.temp.filter(function (d) {
      return d.role.toLowerCase().indexOf(val) !== -1 || !val;
    });

  }

  open() {
    const modalRef = this.modalService.open(UploadfileComponent, {
      size: 'lg'
    })
    modalRef.result.then((result) => {
      if (result) {
        this.model = {
          ...this.model,
          attachments: this.model.attachments.concat(result),
        }
      }
    }, (reason) => {
    });
    modalRef.componentInstance.model_id = this.model.id;
    modalRef.componentInstance.model_type = 'App\\Convenzione';
  }

  onRemoveFile(index: number, callback, context) {
    this.isLoading = true;
    let id = context.formControl.at(index).get('id');
    this.service.deleteFile(id.value).subscribe(
      result => {
        callback(index, context); this.isLoading = false;
      },
      error => {
        this.isLoading = false;
      }
    );
  }

  download(event, model) {
    this.service.download(model.id).subscribe(file => {
      if (file.filevalue){
        var blob = new Blob([decode(file.filevalue)]);             
        saveAs(blob, file.filename);                  
      }
    },
      e => { console.log(e); }
    );

  }


  private groupHeaderTitle(group){
    const totale = this.currency.transform(this.sumImporto(group.value.map(x=>x.dovuto_tranche)));
    return `Stato ${group.value[0].state} ${totale}`
  }

  private sumImporto(cells: number[]): number {
    const filteredCells = cells.filter(cell => !!cell);
    let total = filteredCells.reduce((sum, cell) => sum += Number(cell), 0);
    return total; 
  }

  onBack(){
    if (this.returnUrl){
      this.router.navigate([this.returnUrl]);
    } else {
      this.goBack();
    }
  }

  goBack(): void {
    this.location.back();
  }

  onDescr(){
    this.router.navigate(['home/convdetails', this.model.id]);
  }

  public onValidate() {    
    this.fields.forEach(f => ControlUtils.validate(f));        
  }

  public onDelete() {
  
    this.confirmationDialogService.inputConfirm('Conferma', 'Vuoi procedere con l\'operazione di cancellazione della convenzione?')
    .then((confirmed) => {
      if (confirmed.result) {
        const data = {
          id: this.model.id,
          entity: {
            note: confirmed.entity
          }
        };

        this.isLoading = true;
        this.service.annullaConvenzione(data).subscribe(
          response => {
            this.isLoading = false;
            if (response.success) {
              this.model = {...this.model, ...response.data};
              this.service.messageService.info('Operazione di annullamento terminata con successo');
            } else {
              this.service.messageService.error(response.message);
            }
          }
        );
      }
    });
  }

  isDeleted(){
    return !!this.model.deleted_at;
  }

  isValid(field: FormlyFieldConfig) {
    if (field.key) {
      if (!field.templateOptions.disabled)
        return field.formControl.valid;
      else
        return true;
    }

    return field.fieldGroup.every(f => this.isValid(f));
  }

  rowSelection(row) {     
    if (row.id) {
      this.router.navigate(['home/scadenze', row.id], {
        queryParams: {
          returnUrl: this.router.url,
        }
      });
    }
  }

  removeSelection(row){

      //console.log('User dismissed the dialog (e.g., by using ESC, clicking the cross icon, or clicking outside the dialog)')
      this.service.confirmationDialogService.confirm('Conferma', "Vuoi procedere con l'operazione di elminazione della scandenza?" )
        .then((confirmed) => {
          if (confirmed){
            if (!row.id)
              return;
            this.isLoading = true;
            this.scadenzaService.remove(row.id).subscribe(
              prop => {
                this.isLoading = false; 
                this.onReload();
              },
              error => { // error path        
                console.log(error);
                this.isLoading = false; 
              }
            );
            


          }
          //console.log(confirmed);        
        })
        .catch(() => {
          this.isLoading = false;
        });
      
    
  }

  removeDisabled(row){
    return row.state ==null || row.state != "attivo";
  }
}
