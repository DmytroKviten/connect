<template>
  <div>
    <main>
      <!-- HERO: FULL BLEED -->
      <section class="bleed relative h-[100svh]">
        <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover">
          <source :src="videoSrc" type="video/mp4" />
        </video>
        <div class="absolute inset-0 bg-black/70"></div>
        <div class="pointer-events-none absolute inset-0 ring-1 ring-inset ring-white/10"></div>

        <div class="relative z-10 flex flex-col items-center justify-center w-full h-full text-center px-4">
          <img :src="logoSrc" alt="Connect Logo" class="h-20 w-20 mb-4 opacity-90 drop-shadow-hero" />
          <h1 class="hero-title">CONNECT</h1>
          <h2 class="hero-subtitle">{{ homeCopy.hero?.subtitle }}</h2>
          <p class="hero-lead">
            {{ homeCopy.hero?.lead }}
          </p>

          <div class="mt-6 flex items-center gap-3">
            <template v-if="!isAuth">
              <RouterLink to="/login?redirect=/devices" class="btn btn--primary-lg">{{ homeCopy.hero?.primaryCta }}</RouterLink>
              <RouterLink to="/register?redirect=/devices" class="btn btn--success-lg">{{ homeCopy.hero?.secondaryCta }}</RouterLink>
            </template>
            <RouterLink v-else to="/devices" class="btn btn--teal-lg">{{ homeCopy.hero?.demoCta }}</RouterLink>
          </div>
        </div>
      </section>

      <!-- FEATURES -->
      <section id="features" class="py-24 bg-slate-900 text-slate-100">
        <div class="container max-w-6xl">
          <p class="eyebrow">Можливості</p>
          <div class="section-head">
            <h2 class="h2 text-white">{{ homeCopy.featuresTitle }}</h2>
            <p class="text-slate-400 max-w-2xl">Усе, що потрібно для моніторингу, автоматизації та безпечного керування девайсами.</p>
          </div>
          <div class="grid gap-6 md:grid-cols-3">
            <div class="card feature" v-for="(feature, idx) in homeCopy.features" :key="feature?.title || idx">
              <div class="icon-circle">
                <svg v-if="idx === 0" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 7l6 6-6 6M21 7l-6 6 6 6"/></svg>
                <svg v-else-if="idx === 1" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/></svg>
                <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 6v6l4 2"/></svg>
              </div>
              <h3 class="card-title">{{ feature?.title }}</h3>
              <p class="card-text text-slate-300">{{ feature?.text }}</p>
              <span class="pill-badge">Готово до продакшену</span>
            </div>
          </div>
        </div>
      </section>

      <!-- WHY / VALUE -->
      <section id="company" class="py-24 bg-slate-950">
        <div class="container max-w-6xl">
          <p class="eyebrow">Чому ми</p>
          <div class="section-head">
            <h2 class="h2 text-white">{{ homeCopy.whyTitle }}</h2>
            <p class="text-slate-400 max-w-2xl">Надійність, безпека й реальна цінність для команди експлуатації.</p>
          </div>
          <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div class="value-card" v-for="pill in homeCopy.why" :key="pill.title">
              <h3 class="value-title">{{ pill.title }}</h3>
              <p class="value-text">{{ pill.text }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- TABS -->
      <section id="tabs" class="py-24 bg-slate-900 text-slate-100">
        <div class="container max-w-6xl">
          <p class="eyebrow">Складові платформи</p>
          <div class="section-head">
            <h2 class="h2 text-white">{{ homeCopy.tabsTitle }}</h2>
          </div>

          <div class="tabs">
            <button
              v-for="tab in tabsData"
              :key="tab.id"
              :class="['tab', activeTab === tab.id && 'tab--active']"
              @click="activeTab = tab.id">
              {{ tab.label }}
            </button>
          </div>

          <div class="tab-panel glass" v-if="currentTab">
            <h3 class="panel-title">{{ currentTab.title }}</h3>
            <p class="panel-text">{{ currentTab.text }}</p>
            <ul class="panel-list">
              <li v-for="(item, idx) in currentTab.bullets" :key="idx">{{ item }}</li>
            </ul>
          </div>
        </div>
      </section>

      <!-- USE CASES -->
      <section id="usecases" class="py-24 bg-slate-950 text-slate-100">
        <div class="container max-w-6xl">
          <p class="eyebrow">Сценарії</p>
          <div class="section-head">
            <h2 class="h2 text-white">{{ homeCopy.usecasesTitle }}</h2>
          </div>
          <div class="grid gap-6 md:grid-cols-3">
            <div class="case-card" v-for="usecase in homeCopy.usecases" :key="usecase.title">
              <h3 class="case-title">{{ usecase.title }}</h3>
              <p class="case-text">{{ usecase.text }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section id="cta" class="bleed py-20 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 via-cyan-500/15 to-blue-700/25"></div>
        <div class="container relative max-w-6xl grid gap-6 md:grid-cols-3 items-center">
          <div class="md:col-span-2 space-y-3">
            <p class="text-sm uppercase tracking-[0.2em] text-emerald-200">Почніть за 5 хв</p>
            <h3 class="text-3xl font-bold">Підключіть демо-пристрої й подивіться графіки в реальному часі</h3>
            <p class="text-white/80">Створіть акаунт, додайте свій девайс або скористайтеся демо-каталогом, щоб швидко показати продукт.</p>
          </div>
          <div class="flex items-center md:justify-end gap-3">
            <RouterLink to="/register?redirect=/devices" class="btn btn--solid-white">Почати</RouterLink>
            <RouterLink to="/devices" class="btn btn--ghost-white">Демо</RouterLink>
          </div>
        </div>
      </section>

      <!-- FOOTER -->
      <footer class="py-10 bg-slate-950 text-slate-400 border-t border-slate-800">
        <div class="container max-w-6xl flex flex-col md:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-2 text-sm">
            <img :src="logoSrc" alt="Connect" class="h-8 w-8 rounded-lg border border-white/10 bg-white/5 object-contain" />
            <div class="text-slate-300 font-semibold">CONNECT</div>
            <span class="text-xs text-slate-500">IoT energy stack</span>
          </div>
          <div class="flex items-center gap-3 text-xs">
            <span>© {{ currentYear }}</span>
            <span class="text-slate-600">•</span>
            <span>Performance-first. Secure by default.</span>
          </div>
        </div>
      </footer>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { RouterLink } from 'vue-router'

import axios from 'axios'
import useLocale, { translations } from '../composables/useLocale'

const TOKEN_KEY = 'token'
const USERNAME_KEY = 'userName'

const logoSrc = '/home/image/krest.png'
const videoSrc = '/home/video/videofon.mp4'
const currentYear = new Date().getFullYear()

const isAuth = ref(!!localStorage.getItem(TOKEN_KEY))

function onStorage(e) {
  if (e.key === TOKEN_KEY || e.key === USERNAME_KEY) {
    isAuth.value = !!localStorage.getItem(TOKEN_KEY)
  }
}

onMounted(() => {
  window.addEventListener('storage', onStorage)
})

onBeforeUnmount(() => {
  window.removeEventListener('storage', onStorage)
})

const { messages } = useLocale()
const homeCopy = computed(() => messages.value?.home ?? translations.en.home)

const tabsData = computed(() => homeCopy.value?.tabs ?? [])
const activeTab = ref(tabsData.value?.[0]?.id || 0)
watch(tabsData, (val) => {
  if (!val?.length) return
  activeTab.value = val[0]?.id ?? 0
})

const currentTab = computed(() => tabsData.value.find(tab => tab.id === activeTab.value) ?? tabsData.value[0] ?? null)
</script>

<style scoped>
html, body { overflow-x: hidden; background: #0b1221; }
img, video { display:block; }
.bleed { position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; width: 100vw; }

.drop-shadow-hero { filter: drop-shadow(0 8px 24px rgba(0,0,0,.55)); }
.hero-title    { font-size: clamp(40px, 6vw, 72px); font-weight: 900; letter-spacing: .02em; color:#fff; line-height:1.05; text-shadow: 0 2px 16px rgba(0,0,0,.65); }
.hero-subtitle { font-size: clamp(18px, 2.6vw, 36px); font-weight: 700; color:#ffffffd9; text-shadow: 0 2px 12px rgba(0,0,0,.6); }
.hero-lead     { max-width: 44rem; font-size: clamp(16px, 1.6vw, 22px); color:#ffffffcc; line-height:1.55; text-shadow: 0 1px 10px rgba(0,0,0,.55); margin-top:.75rem; }

.btn { padding:.5rem .75rem; border-radius:.75rem; font-weight:600; color:#fff; transition:.2s; display:inline-block; }
.btn--primary { background:#2563eb; } .btn--primary:hover { background:#3b82f6; }
.btn--success { background:#16a34a; } .btn--success:hover { background:#22c55e; }
.btn--teal    { background:#2dd4bf; color:#0b1221; } .btn--teal:hover { background:#5eead4; }
.btn--danger  { background:#dc2626; } .btn--danger:hover { background:#ef4444; }
.btn--primary-lg { padding:.875rem 1.25rem; border-radius:9999px; background:#2563eb; }
.btn--success-lg { padding:.875rem 1.25rem; border-radius:9999px; background:#16a34a; }
.btn--teal-lg    { padding:.875rem 1.5rem; border-radius:9999px; background:#2dd4bf; color:#0b1221; }
.btn--solid-white { padding:.9rem 1.4rem; border-radius:.9rem; background:#fff; color:#0b1221; font-weight:700; }
.btn--solid-white:hover { background:#e2e8f0; }
.btn--ghost-white { padding:.85rem 1.2rem; border-radius:.9rem; border:1px solid rgba(255,255,255,.5); color:#fff; font-weight:700; }
.btn--ghost-white:hover { background:rgba(255,255,255,.12); }

.container { max-width: 72rem; margin-inline:auto; padding-inline:1rem; }
.h2 { font-size: clamp(26px, 3vw, 36px); font-weight:800; letter-spacing:.01em; }
.eyebrow { text-transform: uppercase; letter-spacing:.2em; font-size:.75rem; color:#22d3ee; margin-bottom:.6rem; font-weight:700; }
.section-head { display:flex; flex-direction:column; gap:.25rem; margin-bottom:1.5rem; }

.card.feature { background: linear-gradient(145deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02)); border:1px solid rgba(255,255,255,0.06); border-radius:1rem; padding:1.4rem; box-shadow: 0 10px 30px rgba(0,0,0,0.25); }
.card-title { font-size:1.125rem; font-weight:700; }
.card-text  { line-height:1.6; margin:.5rem 0 1rem; }
.icon-circle { width:2.75rem; height:2.75rem; border-radius:50%; background:rgba(34,211,238,0.15); border:1px solid rgba(34,211,238,0.35); display:flex; align-items:center; justify-content:center; color:#22d3ee; margin-bottom:1rem; }
.pill-badge { display:inline-flex; align-items:center; gap:.35rem; padding:.3rem .7rem; border-radius:999px; font-size:.75rem; color:#cbd5e1; background:rgba(15,23,42,0.5); border:1px solid rgba(148,163,184,0.2); }

.value-card { background:#0f172a; border:1px solid rgba(148,163,184,0.15); border-radius:1rem; padding:1.1rem; box-shadow:0 10px 30px rgba(0,0,0,0.3); }
.value-title { font-weight:700; color:#e2e8f0; margin-bottom:.35rem; }
.value-text { color:#cbd5e1; line-height:1.55; }

.tabs { display:flex; gap:.5rem; background:#0b1221; padding:.5rem; border-radius:9999px; width:fit-content; margin:0 auto 1.25rem; box-shadow: inset 0 0 0 1px rgba(255,255,255,.06); }
.tab  { padding:.55rem 1.1rem; border-radius:9999px; color:#cbd5e1; background:transparent; border:none; cursor:pointer; }
.tab--active { background:linear-gradient(180deg,#22d3ee,#0ea5e9); color:#0b1221; font-weight:700; }
.tab-panel { margin-top:1rem; }
.tab-panel.glass { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:1rem; padding:1.25rem; box-shadow:0 12px 30px rgba(0,0,0,0.25); }
.panel-title { font-size:1.25rem; font-weight:800; color:#f8fafc; }
.panel-text  { color:#cbd5e1; line-height:1.65; margin:.5rem 0 1rem; }
.panel-list  { color:#cbd5e1; display:grid; gap:.35rem; list-style:disc; padding-left:1.25rem; }

.case-card { background:linear-gradient(145deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01)); border:1px solid rgba(255,255,255,0.05); border-radius:1rem; padding:1.2rem; box-shadow:0 12px 30px rgba(0,0,0,0.25); }
.case-title { font-size:1.05rem; font-weight:700; color:#f8fafc; margin-bottom:.35rem; }
.case-text  { color:#cbd5e1; line-height:1.55; }

/* Utilities */
.container.max-w-6xl { max-width: 72rem; }
</style>
