import { Routes } from '@angular/router';

import { Dashboard1Component } from './dashboard1/dashboard1.component';
import { AuthGuard } from '../core/auth.guard';
import { Dashboard2Component } from './dashboard2/dashboard2.component';
import { DashboardConvAmministrativaComponent } from './dashboard-conv-amministrativa/dashboard-conv-amministrativa.component';

export const DashboardRoutes: Routes = [
  {
    path: '',
    children: [
      {
        path: 'dashboard1',
        component: Dashboard1Component,
        canActivate:[AuthGuard],
        data: {
          title: 'Dashboard attività',
          urls: [
            { title: 'Home', url: '/home' },
            { title: 'Dashboard' }
          ]
        }
      },     
      {
        path: 'dashboard2',
        component: Dashboard2Component,
        canActivate:[AuthGuard],
        data: {
          title: 'Dashboard convenzioni',
          urls: [
            { title: 'Home', url: '/home' },
            { title: 'Dashboard' }
          ]
        }
      },  
      {
        path: 'dashboardconvamministrativa',
        component: DashboardConvAmministrativaComponent,
        canActivate:[AuthGuard],
        data: {
          title: 'Dashboard convenzioni amministrative',
          urls: [
            { title: 'Home', url: '/home' },
            { title: 'Dashboard convenzioni amministrative' }
          ]
        }
      },     
    ]
  }
];
