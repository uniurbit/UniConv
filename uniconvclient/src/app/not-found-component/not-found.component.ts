import { Component } from '@angular/core';

@Component({
  selector: 'notfound',
  template: `
  <div class="jumbotron text-center">
      <h1>Ooops...</h1>
      <p>Pagina non trovata
      (<a routerLink="/">home</a>).</p>
    </div>  
  `
})
export class NotFoundComponent {}
