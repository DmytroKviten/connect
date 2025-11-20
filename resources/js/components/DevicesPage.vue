<template>
  <div class="max-w-6xl mx-auto px-4 md:px-6 py-10 md:py-14">
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ devicesCopy.title }}</h1>
        <p class="text-sm text-slate-400 mt-1">{{ devicesCopy.subtitle }}</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-500 transition"
          @click="showModal = true"
        >
          <span class="text-lg leading-none">+</span>
          {{ devicesCopy.add }}
        </button>

        <RouterLink
          to="/setup"
          class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500 transition"
        >
          {{ devicesCopy.setup }}
        </RouterLink>

        <RouterLink
          to="/monitoring"
          class="px-3 py-1.5 rounded-lg bg-slate-700 text-white text-sm font-semibold hover:bg-slate-600 transition"
        >
          {{ devicesCopy.monitoring }}
        </RouterLink>

        <button
          class="px-3 py-1.5 rounded-lg bg-emerald-400 text-slate-900 text-sm font-semibold hover:bg-emerald-300 transition disabled:opacity-60"
          :disabled="loading"
          @click="fetchDevices"
        >
          {{ devicesCopy.refresh }}
        </button>

        <label class="flex items-center gap-2 text-xs text-slate-200 cursor-pointer select-none">
          <input type="checkbox" v-model="autoRefresh" class="accent-emerald-400">
          {{ devicesCopy.auto }}
        </label>
      </div>
    </div>

    <transition name="fade">
      <p v-if="msg" class="mb-3 text-sm text-emerald-300/90 bg-emerald-500/5 border border-emerald-500/20 px-3 py-2 rounded-lg">
        {{ msg }}
      </p>
    </transition>
    <transition name="fade">
      <p v-if="error" class="mb-3 text-sm text-red-300 bg-red-500/5 border border-red-500/20 px-3 py-2 rounded-lg">
        {{ error }}
      </p>
    </transition>

    <div
      v-if="loading && devices.length === 0"
      class="flex flex-col items-center justify-center bg-slate-900/40 backdrop-blur-md p-10 rounded-2xl border border-white/5 shadow-lg"
    >
      <svg class="w-10 h-10 mb-4 animate-spin text-emerald-400" viewBox="0 0 24 24">
        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
      </svg>
      <div class="text-slate-200 text-sm">{{ devicesCopy.loading }}</div>
    </div>

    <div
      v-else-if="devices.length === 0"
      class="flex flex-col items-center bg-slate-900/40 backdrop-blur-md p-10 rounded-2xl border border-white/5 shadow-lg"
    >
      <svg class="w-14 h-14 mb-6 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path d="M12 9v6m3-3H9" />
        <circle cx="12" cy="12" r="9" />
      </svg>
      <p class="text-lg font-semibold text-slate-100 mb-1">{{ devicesCopy.emptyTitle }}</p>
      <p class="text-sm text-slate-400 text-center mb-4 max-w-sm">{{ devicesCopy.emptyText }}</p>
      <button
        class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-500"
        @click="showModal = true"
      >
        {{ devicesCopy.emptyCta }}
      </button>
    </div>

    <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <article
        v-for="d in devices"
        :key="d.id"
        class="relative flex flex-col bg-slate-900/40 backdrop-blur border border-white/5 rounded-2xl overflow-hidden shadow-sm hover:shadow-emerald-500/20 transition"
      >
        <div
          class="h-1 w-full"
          :class="isOnline(d.last_seen_at) ? 'bg-gradient-to-r from-emerald-400 to-emerald-200' : 'bg-slate-700'"
        ></div>

        <div class="p-5 flex-1 flex flex-col gap-4">
          <div class="flex items-start justify-between gap-2">
            <div>
              <h2 class="text-base font-semibold text-white leading-tight truncate max-w-[160px]">
                {{ d.name || `${devicesCopy.fallbackName} #${d.id}` }}
              </h2>
              <p class="text-xs text-slate-400 break-all mt-0.5">
                {{ devicesCopy.fallbackUid }}: {{ d.uid || d.mac || '—' }}
              </p>
            </div>
            <span
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
              :class="isOnline(d.last_seen_at)
                ? 'bg-emerald-500/15 text-emerald-200 border border-emerald-400/30'
                : 'bg-slate-700/50 text-slate-200 border border-slate-500/20'"
            >
              <span
                class="inline-block w-1.5 h-1.5 rounded-full"
                :class="isOnline(d.last_seen_at) ? 'bg-emerald-300' : 'bg-slate-300'"
              ></span>
              {{ isOnline(d.last_seen_at) ? devicesCopy.statusOnline : devicesCopy.statusOffline }}
            </span>
          </div>

          <div v-if="d.category" class="rounded-xl overflow-hidden border border-white/5">
            <img
              :src="`/images/demo/${d.category}.png`"
              alt="device"
              class="w-full h-28 object-cover"
              @error="onImgError($event)"
            />
          </div>

          <div class="grid grid-cols-3 gap-2 bg-slate-950/30 border border-white/5 rounded-xl p-2 text-center text-slate-200">
            <div>
              <div class="text-[10px] uppercase tracking-wide text-slate-400">{{ devicesCopy.metrics.power }}</div>
              <div class="text-sm font-semibold text-white">
                {{ fmtNum(d.latest_reading?.power_w) ?? '—' }}
                <span class="text-[10px] text-slate-400 ml-0.5">W</span>
              </div>
            </div>
            <div>
              <div class="text-[10px] uppercase tracking-wide text-slate-400">{{ devicesCopy.metrics.voltage }}</div>
              <div class="text-sm font-semibold text-white">
                {{ fmtNum(d.latest_reading?.voltage_v) ?? '—' }}
                <span class="text-[10px] text-slate-400 ml-0.5">V</span>
              </div>
            </div>
            <div>
              <div class="text-[10px] uppercase tracking-wide text-slate-400">{{ devicesCopy.metrics.energy }}</div>
              <div class="text-sm font-semibold text-white">
                {{ fmtInt(d.latest_reading?.energy_wh) ?? '—' }}
                <span class="text-[10px] text-slate-400 ml-0.5">Wh</span>
              </div>
            </div>
          </div>

          <div class="text-xs text-slate-300 space-y-1">
            <div>
              <span class="text-slate-400">{{ devicesCopy.info.ip }}:</span>
              {{ d.ip || devicesCopy.info.unknown }}
            </div>
            <div v-if="d.category">
              <span class="text-slate-400">{{ devicesCopy.info.category }}:</span> {{ d.category }}
            </div>
            <div v-if="d.last_seen_at">
              <span class="text-slate-400">{{ devicesCopy.info.lastSeen }}:</span> {{ humanDate(d.last_seen_at) }}
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between gap-3 px-5 pb-4 pt-3 border-t border-white/5">
          <button
            @click="deleteDevice(d.id)"
            class="px-3 py-1.5 rounded-lg bg-red-600/90 text-white text-xs font-semibold hover:bg-red-500 transition"
          >
            {{ devicesCopy.actions.delete }}
          </button>
          <RouterLink
            :to="`/devices/${d.id}`"
            class="px-3 py-1.5 rounded-lg bg-emerald-500 text-slate-950 text-xs font-semibold hover:bg-emerald-400 transition"
          >
            {{ devicesCopy.actions.view }}
          </RouterLink>
        </div>
      </article>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4">
      <div class="relative bg-slate-900 rounded-2xl w-full max-w-md border border-white/10 shadow-2xl">
        <button class="absolute top-3 right-3 text-slate-400 hover:text-white text-xl" @click="showModal = false">
          &times;
        </button>
        <div class="p-6">
          <h2 class="text-lg font-semibold text-white mb-2 text-center">{{ devicesCopy.modalTitle }}</h2>
          <p class="text-sm text-slate-400 text-center mb-4">{{ devicesCopy.modalText }}</p>

          <div class="space-y-3">
            <button
              v-for="item in demoDevices"
              :key="item.type"
              @click="addDemoDevice(item.type)"
              class="w-full flex items-center gap-3 bg-slate-800 hover:bg-slate-700 rounded-lg px-3 py-2 transition border border-slate-700/40"
            >
              <img :src="item.img" alt="icon" class="w-10 h-10 rounded-md object-cover border border-white/5" @error="onImgError($event)" />
              <div class="text-left">
                <div class="font-medium text-sm text-white">{{ item.name }}</div>
                <div class="text-xs text-slate-400">{{ item.desc }}</div>
              </div>
            </button>
          </div>

          <button
            @click="showModal = false"
            class="mt-5 w-full py-2 text-sm text-slate-300 hover:text-white transition"
          >
            {{ devicesCopy.close }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted, computed } from 'vue'
import axios from 'axios'
import { RouterLink } from 'vue-router'

import useLocale, { translations } from '../composables/useLocale'

const devices = ref([])
const loading = ref(false)
const msg = ref('')
const error = ref('')
const autoRefresh = ref(true)
const showModal = ref(false)
let timer = null

const { messages, dateLocale } = useLocale()
const devicesCopy = computed(() => messages.value?.devices ?? translations.en.devices)
const demoDevices = computed(() => devicesCopy.value.demoCatalog ?? [])

function onImgError(e) {
  e.target.style.display = 'none'
}

async function fetchDevices() {
  loading.value = true
  error.value = ''
  try {
    const res = await axios.get('/api/devices')
    devices.value = res.data.devices || []
  } catch {
    error.value = devicesCopy.value.messages.listError
  } finally {
    loading.value = false
  }
}

async function addDemoDevice(type) {
  try {
    const res = await axios.post('/api/devices/demo-add', { type })
    if (res.data?.ok) {
      const template = devicesCopy.value.messages.added || ''
      msg.value = template.replace('{name}', res.data.device.name)
      showModal.value = false
      await fetchDevices()
    }
  } catch {
    error.value = devicesCopy.value.messages.addError
  }
}

async function deleteDevice(id) {
  if (!confirm(devicesCopy.value.deleteConfirm)) return
  try {
    const res = await axios.delete(`/api/devices/${id}`)
    if (res.data?.ok) {
      msg.value = devicesCopy.value.messages.deleted
      await fetchDevices()
    } else {
      error.value = devicesCopy.value.messages.deleteError
    }
  } catch {
    error.value = devicesCopy.value.messages.deleteError
  }
}

function setupTimer() {
  if (timer) clearInterval(timer)
  if (autoRefresh.value) {
    timer = setInterval(fetchDevices, 10000)
  }
}

watch(autoRefresh, setupTimer)

onMounted(() => {
  fetchDevices()
  setupTimer()
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

function isOnline(lastSeen) {
  if (!lastSeen) return false
  const t = new Date(lastSeen).getTime()
  return Date.now() - t < 90_000
}

function humanDate(d) {
  if (!d) return '—'
  const date = new Date(d)
  return date.toLocaleString(dateLocale.value, { hour: '2-digit', minute: '2-digit' })
}

function fmtNum(v) {
  if (v == null) return null
  const n = Number(v)
  if (!Number.isFinite(n)) return null
  return Math.round(n * 10) / 10
}

function fmtInt(v) {
  if (v == null) return null
  const n = Number(v)
  if (!Number.isFinite(n)) return null
  return Math.round(n)
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
