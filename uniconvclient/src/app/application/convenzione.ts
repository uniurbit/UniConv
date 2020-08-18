import { BaseEntity } from "../core/base-entity";
import { ExcelNumberFormat } from "ag-grid-community";

export interface FileInfra {
    filename: string,
    filetype: string,
    // per visualizzare il pdf | string è in base64 
    filevalue: ArrayBuffer | string,
}

export enum convenzioneFrom {
    dip = 'dip',
    amm = 'amm'
}

export enum rinnovoType {
    non_rinnovabile = 'non_rinnovabile',
    esplicito = 'esplicito',
    tacito = 'tacito',
}

export interface Convenzione extends BaseEntity {
    schematipotipo: string, 
    transition: string,
    user_id: number,
    descrizione_titolo: string,
    dipartimemto_cd_dip: string,
    nominativo_docente: string,
    emittente: string,
    user: { id: number, name: string },
    dipartimento: { cd_dip: string, nome_breve: string },
    stato_avanzamento: string,
    convenzione_type: string,
    tipopagamento: { codice: string, descrizione: string },
    azienda: { id: string, denominazione: string },
    aziende: any[],
    attachments?: FileAttachment[],
    assignments?: any[],
    unitaorganizzativa_uo: string,
    usertasks?: any[],
    stipula_type?: string, // uniurb, controparte
    current_place?: string,  //
    ambito?: string,
    resp_scientifico?: string,
    corrispettivo?: number,
    durata?: number,
    titolario_classificazione?: string,
    oggetto_fascicolo?: string,
    numero?:string,
    bollo_virtuale?: boolean,
    bolli?: any[],
    data_sottoscrizione?: Date,
    scadenze?: any[]
    data_inizio_conv?: any,
    data_fine_conv?: any,
    unitaorganizzativa_affidatario?: string,    
    convenzione_from: convenzioneFrom,
    rinnovo_type: rinnovoType,
    deleted_at?: any
}

export interface ConvenzioneDipartimentale extends Convenzione{
    convenzione_from: convenzioneFrom.dip;
}

export interface ConvenzioneAmministrativa extends Convenzione{
    convenzione_from: convenzioneFrom.amm;
    dipartimemto_cd_dip: null;
}

export interface Owner{
    v_ie_ru_personale_id_ab: string;
}

export interface FileAttachment {
    id?: number,
    model_id?: number,
    model_type: string,
    attachmenttype_codice?: string,
    attachmenttype?: {id: number, codice: string, descrizione: string},
    filename: string,
    filetype?: string,
    // per visualizzare il pdf | string è in base64 
    filevalue?: ArrayBuffer | string,
    filepath?: string,
    docnumber?: string,
    emission_date?: Date,
    num_prot?: string,
    num_rep?: string,
    nrecord?: string,
}
