<template>
  <header class="fixed inset-x-0 top-0 h-16 px-6 flex items-center bg-slate-900/90 backdrop-blur z-50 shadow-md border-b border-slate-800">
    <div class="max-w-6xl mx-auto w-full flex items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <RouterLink to="/" class="flex items-center select-none">
          <img :src="logoSrc" alt="Connect" class="h-10 w-10 rounded-lg border border-white/15 bg-white/10 object-contain shadow" />
        </RouterLink>
        <nav class="hidden md:flex items-center gap-3 text-sm">
          <RouterLink to="/" class="nav-link">Головна</RouterLink>
          <RouterLink to="/devices" class="nav-link">Пристрої</RouterLink>
          <RouterLink to="/about" class="nav-link">Про нас</RouterLink>
          <RouterLink to="/solutions" class="nav-link">Рішення</RouterLink>
          <RouterLink to="/pricing" class="nav-link">Тарифи</RouterLink>
          <RouterLink to="/contact" class="nav-link">Контакти</RouterLink>
        </nav>
      </div>

      <div class="flex items-center gap-3">
        <LanguageToggle />
        <template v-if="!isAuth">
          <RouterLink to="/login?redirect=/devices" class="btn btn--ghost">{{ t('actions.login') }}</RouterLink>
          <RouterLink to="/register?redirect=/devices" class="btn btn--solid">{{ t('actions.register') }}</RouterLink>
        </template>
        <template v-else>
          <RouterLink to="/devices" class="btn btn--solid teal">{{ t('actions.triggerDemo') }}</RouterLink>
          <button @click="logout" class="btn btn--ghost danger">{{ t('actions.logout') }}</button>
        </template>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import axios from 'axios'

import LanguageToggle from './LanguageToggle.vue'
import useLocale from '../composables/useLocale'

const router = useRouter()
const { t } = useLocale()

const TOKEN_KEY = 'token'
const USERNAME_KEY = 'userName'
const isAuth = ref(!!localStorage.getItem(TOKEN_KEY))

const logoSrc = '/home/image/krest.png'

function logout() {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USERNAME_KEY)
  delete axios.defaults.headers.common['Authorization']
  isAuth.value = false
  router.push('/login')
}
</script>

<style scoped>
.btn {
  padding: 0.65rem 1.1rem;
  border-radius: 9999px;
  font-weight: 700;
  font-size: 0.95rem;
  transition: 0.2s;
  border: 1px solid transparent;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}
.btn--solid { background: #1f2937; color: #e5e7eb; border-color: #334155; }
.btn--solid:hover { background: #0ea5e9; border-color: #0ea5e9; color: #0b1221; }
.btn--solid.teal { background: #0ea5e9; border-color: #0ea5e9; color: #0b1221; }
.btn--solid.teal:hover { background: #22d3ee; border-color: #22d3ee; }
.btn--ghost { background: transparent; color: #e2e8f0; border-color: #334155; }
.btn--ghost:hover { background: #0f172a; }
.btn--ghost.danger { color: #fca5a5; border-color: #7f1d1d; }
.btn--ghost.danger:hover { background: #7f1d1d; color: #fff; }
.nav-link {
  padding: 0.35rem 0.8rem;
  border-radius: 0.7rem;
  color: #e2e8f0;
  transition: 0.2s;
  text-decoration: none;
}
.nav-link:hover,
.router-link-active.nav-link {
  background: rgba(148, 163, 184, 0.14);
  color: #fff;
  box-shadow: 0 1px 0 rgba(255, 255, 255, 0.05);
}
</style>
