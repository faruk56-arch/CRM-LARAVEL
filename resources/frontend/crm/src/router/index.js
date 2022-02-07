import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router);

import Layout from '@/views/layout/Layout'
import Leadview from '@/views/excel/selectExcel'
import Users from '@/views/users'
import Exports from '@/views/exports'
import Customers from '@/views/customers'
import Rules from '@/views/customers/rules'
import Rapports from '@/views/customers/rapports'
import Taboola from '@/views/taboola/index'
import Production from '@/views/production/index'

export const constantRouterMap = [
  {
    path: '/login',
    meta: {'logged': false},
    component: () => import('@/views/login/index'),
    hidden: true,
    name: 'login'
  },
  {
    path: '/404',
    meta: {'logged': false},
    component: () => import('@/views/errorPage/404'),
    hidden: true
  },
  {
    path: '/401',
    meta: {'logged': false},
    component: () => import('@/views/errorPage/401'),
    hidden: true
  },
  {
    path: '',
    component: Layout,
    hidden: true,
    meta: {'logged': false},
    redirect: '',
    children: [
      {
        path: '/landings_test/:id',
        component: Leadview,
        name: 'Leadview',
        meta: {'logged': true},
        redirect: '',
        hidden: true
      },{
        path: '/users',
        component: Users,
        name: 'Users',
        meta: {'logged': true},
        redirect: '',
        hidden: true
      },
      {
        path: '/exports/:userId',
        component: Exports,
        name: 'Exports',
        meta: {'logged': true},
        redirect: '',
        hidden: true
      },
      {
        path: '/customers',
        component: Customers,
        name: 'Customers',
        meta: {'logged': true},
        redirect: '',
        hidden: true
      },
      {
        path: '/customers/rules/rapports/:rule_id',
        component: Rapports,
        name: 'Rapports',
        meta: {'logged': true},
        redirect: '',
        hidden: true
      },
      {
        path: '/taboola',
        component: Taboola,
        name: 'Taboola',
        meta: {'logged': true},
        redirect: '',
        hidden: true
      },
      {
        path: '/production/:category',
        component: Production,
        name: 'Production',
        meta: {'logged': true},
        redirect: '',
        hidden: true
      },
      {
        path: '/customers/rules/:userId',
        component: Rules,
        name: 'Rules',
        meta: {'logged': true},
        redirect: '',
        hidden: true
      },
      {
        path: '/',
        name: 'Home',
        meta: {'logged': true},
        redirect: '',
        hidden: true
      }
    ]
  }

]

export default new Router({
  // mode: 'history', // require service support
  scrollBehavior: () => ({ y: 0 }),
  routes: constantRouterMap
})

export const asyncRouterMap = [

  { path: '*', redirect: '/404', hidden: true } // TPM FIX  TODO
]
