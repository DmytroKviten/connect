// resources/js/router.js
import { createRouter, createWebHistory } from 'vue-router'

// Eager: домашня
import HomePage from './components/HomePage.vue'

// 👇 ти створив тут
import MonitoringPage from './components/MonitoringPage.vue'

// Lazy: решта
const DemoPage         = () => import('./components/DemoPage.vue')
const DevicesPage      = () => import('./components/DevicesPage.vue')
const DevicesShowPage  = () => import('./components/DevicesShowPage.vue')
const DevicesSetupPage = () => import('./components/DevicesSetupPage.vue')
const LoginPage        = () => import('./components/LoginPage.vue')
const RegisterPage     = () => import('./components/RegisterPage.vue')
const AboutPage       = () => import('./components/AboutPage.vue')
const SolutionsPage   = () => import('./components/SolutionsPage.vue')
const PricingPage     = () => import('./components/PricingPage.vue')
const TeamPage        = () => import('./components/TeamPage.vue')
const ContactPage     = () => import('./components/ContactPage.vue')

const isAuthed = () => !!localStorage.getItem('token')

const routes = [
  { path: '/',            name: 'home',         component: HomePage,        meta: { auth: false } },
  { path: '/demo',        name: 'demo',         component: DemoPage,        meta: { auth: false } },

  { path: '/devices',     name: 'devices',      component: DevicesPage,     meta: { auth: true } },
  { path: '/devices/:id', name: 'devices.show', component: DevicesShowPage, props: true, meta: { auth: true } },
  { path: '/setup',       name: 'setup',        component: DevicesSetupPage, meta: { auth: true } },
  { path: '/about',       name: 'about',        component: AboutPage,       meta: { auth: false } },
  { path: '/solutions',   name: 'solutions',    component: SolutionsPage,   meta: { auth: false } },
  { path: '/pricing',     name: 'pricing',      component: PricingPage,     meta: { auth: false } },
  { path: '/team',        name: 'team',         component: TeamPage,        meta: { auth: false } },
  { path: '/contact',     name: 'contact',      component: ContactPage,     meta: { auth: false } },

  // 👇 нова сторінка моніторингу
  { path: '/monitoring',  name: 'monitoring',   component: MonitoringPage,  meta: { auth: true } },

  { path: '/login',       name: 'login',        component: LoginPage,       meta: { guestOnly: true } },
  { path: '/register',    name: 'register',     component: RegisterPage,    meta: { guestOnly: true } },

  {
    path: '/:pathMatch(.*)*',
    redirect: () => (isAuthed() ? { name: 'devices' } : { name: 'login' }),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach((to, from, next) => {
  const authed = isAuthed()

  if (to.meta?.auth && !authed) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  if (to.meta?.guestOnly && authed) {
    next({ name: 'devices' })
    return
  }

  next()
})

export default router
