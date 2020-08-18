UniConv è una applicazione web per gestione dei flussi documentali e dematerializzazione dell'attività contrattuale attiva dell'ateneo. L'applicazione è basata su Web API e sviluppata sul framework Laravel per la parte backend, Angular per la parte frontend e Shibboleth come sistema di autenticazione.
-------------------------------

## Funzionalità Applicative

- 🔥 Gestione delle convenzioni: inserimento e modifica informazione della convenzione
    - Step di inserimento 
- 🔥 Gestione del flusso documentale
    - Fase di approvazione
    - Fase di avvio sottoscrizione
    - Fase di completamento sottoscrizione
    - Fase di repertoriazione
    - Fase di richiesta emissione
    - Fase di incasso
- 🔥 Storico convenzioni
- 🔥 Gestione del flusso documentale
- 🔥 Creazione delle attività utente associate alle fasi della convenzione
- 🔥 Dashboard operatore con stato attività 
- 🔥 Dashboard convenzioni con stato convenzione e scadenze 


## Struttura convenzione 

- ⚡️ Informazioni descrittive
    - Intestazione 
    - Approvazione
    - Aziende o Enti
    - Fascicolo
    - Allegati
    - Scadenze
- ⚡️ Fasi del flusso documentale

## Caratteristiche sistema

- 🔥 Applicazione web con architettura basata su Web API
- ⚡️ Supporto per il SSO con Shibbolet
- ⚡️ Integrazione per la lettura dati da Ugov
    - lettura afferenza organizzativa
- ⚡️ Integrazione con Titulus 
- 📝 Sistema multi utente e multi ruolo
- 📝 Generazione di pdf basato su [wkhtmltopdf](https://github.com/barryvdh/laravel-snappy)
- 😍 Tema Boostrap 
- 💪 Costruito su 
    - [Laravel](https://laravel.com/) 
    - [Angular](https://angular.io/)
    - [Dynamic forms in Angular](https://formly.dev/)


## Creazione di una applicazione

1) Fare un fork del repository 

2) Eseguire il clone del progetto 

## Configurazione UniConv-backend

1) Entrare nella cartella `cd .\UniConv-backend\`

2) Creare un file di configurazione .env (copiare, rinominare e modificare il file .env.exmaple inserendo il nome dell'applicazione, 
il database di riferimento ...)

3) Eseguire `composer install` per l'istallazione dei package

4) Eseguire `php artisan migrate:fresh --seed` 

## Configurazione UniConv-frontend

1) Entrare nella cartella `cd .\UniConv-frontend\`

2) Eseguire `npm install`
   
## Configurazione UniConv-mockipd

1) Entrare nella cartella cd `cd .\UniConv-mock-idp\`

2) Eseguire  `npm install fake-sso-idp`

3) Il mock idp è configurato con un utente a cui è associato il ruolo SUPER-ADMIN

## Lancio dell'applicazione

1) Aprire tre terminal

2) Lancio dei servizi di backend 
   
    cd .\UniConv-backen\
    php artisan serve --port 80
    

3) Lancio del frontend
   
    cd .\UniConv-frontend\
    ng serve
   

4) Lancio del mock idp

    cd .\UniConv-mock-idp\  
    node start.js
    

Aprire il broswer all'indirizzo  `http://localhost:4200/`










Happy coding! 

