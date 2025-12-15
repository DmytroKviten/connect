<template>
  <div class="max-w-2xl mx-auto mt-10">
    <h1 class="text-2xl font-bold mb-8 text-center">
      {{ deviceName }}
      <span class="text-base font-normal text-muted">({{ deviceModel || '—' }})</span>
    </h1>

    <!-- ПОКАЗНИКИ -->
    <div class="grid grid-cols-3 gap-4 mb-4 text-center bg-slate-900/70 border border-white/10 rounded-2xl shadow-md py-6">
      <div v-for="item in indicators" :key="item.key">
        <div class="flex flex-col items-center">
          <p class="text-sm text-neutral-400">{{ item.label }}</p>
          <p class="text-xl font-semibold mt-1">
            {{ fmt(latest?.[item.key]) }}<span class="text-base font-normal"> {{ item.unit }}</span>
          </p>
        </div>
      </div>
    </div>

    <!-- РЕЖИМ -->
    <div class="mb-8 bg-slate-900/70 border border-white/10 rounded-2xl p-4">
      <div class="flex items-center gap-3 mb-2">
        <span class="text-sm text-neutral-400">Режим</span>
        <span :class="['px-2 py-1 rounded-md text-xs font-semibold', modeBadge.class]">
          {{ modeBadge.text }}
        </span>
        <span class="text-xs text-neutral-500">({{ lastTimeLabel }})</span>
      </div>
      <ul class="list-disc pl-5 text-sm text-neutral-300" v-if="suggestions.length">
        <li v-for="s in suggestions" :key="s">{{ s }}</li>
      </ul>
      <div v-else class="text-sm text-neutral-500">Чекаю дані для класифікації…</div>
    </div>

    <!-- КНОПКИ ПРИСТРОЮ -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
      <button @click="toggleSwitch" :disabled="loading" class="btn bg-accentDark hover:bg-accent">
        {{ state === true ? 'Вимкнути' : state === false ? 'Увімкнути' : 'Завантаження…' }}
      </button>
      <button @click="rebootDevice" :disabled="loading" class="btn bg-blue-800 hover:bg-blue-600">Перезапустити</button>
      <button @click="factoryReset" :disabled="loading" class="btn bg-red-700 hover:bg-red-500">Скинути</button>
      <button @click="getDeviceInfo" :disabled="loading" class="btn bg-green-700 hover:bg-green-500">Інфо</button>
    </div>

    <!-- КНОПКА ТЕСТОВИХ 12 ГОДИН -->
    <!-- Synthetic data generation -->
    <div class="mb-8 bg-slate-900/70 border border-white/10 rounded-2xl p-4 flex flex-col md:flex-row gap-4 md:items-end">
      <div class="flex-1">
        <p class="text-xs text-neutral-400 mb-2">Нові тестові точки додаються поверх попередніх, тож можна накопичувати історію.</p>
        <label class="block text-xs uppercase tracking-wide text-neutral-500 mb-1">Тривалість</label>
        <select
          v-model.number="selectedGenerationHours"
          class="w-full rounded-lg bg-slate-800/60 border border-white/10 px-3 py-2 text-sm focus:outline-none focus:border-emerald-400"
        >
          <option v-for="opt in generationOptions" :key="opt.hours" :value="opt.hours">
            {{ opt.label }}
          </option>
        </select>
      </div>
      <button
        @click="generateSyntheticData"
        :disabled="loading"
        class="btn w-full md:w-auto bg-yellow-500 hover:bg-yellow-400 text-black font-semibold"
      >
        Згенерувати {{ selectedGenerationLabel }}
      </button>
    </div>

    <!-- ПОВІДОМЛЕННЯ -->
    <transition name="fade">
      <div v-if="message" class="msg">{{ message }}</div>
    </transition>

    <!-- ГРАФІК -->
    <div class="bg-slate-900/70 border border-white/10 rounded-2xl shadow-md p-4">
      <canvas ref="chartRef" height="140"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Chart from 'chart.js/auto'

import useLocale from '../composables/useLocale'

const TOKEN_KEY = 'token'
const AX = { headers: { Accept: 'application/json' } }

function setAxiosAuthToken(token) {
  if (token) axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  else delete axios.defaults.headers.common['Authorization']
}

const route = useRoute()
const router = useRouter()
const { dateLocale } = useLocale()

const deviceId = ref(route.params.id)
const deviceName = ref('')
const deviceModel = ref('')
const deviceMac = ref('')
const latest = ref({ power_w: 0, voltage_v: 0, energy_wh: 0, mode: 'unknown' })
const state = ref(null)
const loading = ref(false)
const message = ref('')
const chartRef = ref(null)
const mode = ref('unknown')

let chart = null

const HISTORY_FETCH_LIMIT = 720
const HISTORY_CHART_LIMIT = 720

const chartHistory = ref([])
const hasMultiDayHistory = ref(false)

const generationOptions = [
  { label: '12 hours', hours: 12 },
  { label: '24 hours', hours: 24 },
  { label: '3 days', hours: 72 },
  { label: '1 week', hours: 168 },
]
const RATE_LIMIT_DELAY_MS = 300
const MAX_RETRY_429 = 2

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms))
}
const selectedGenerationHours = ref(generationOptions[0].hours)
const selectedGenerationLabel = computed(() => {
  const opt = generationOptions.find(o => o.hours === Number(selectedGenerationHours.value))
  return opt?.label ?? generationOptions[0].label
})

const indicators = [
  { label: 'Потужність', key: 'power_w', unit: 'W' },
  { label: 'Напруга', key: 'voltage_v', unit: 'V' },
  { label: 'Енергія', key: 'energy_wh', unit: 'Wh' }
]

function fmt(v) {
  if (v == null) return '—'
  const n = Number(v)
  if (Number.isNaN(n)) return String(v)
  return n % 1 === 0 ? n.toString() : n.toFixed(1)
}

function parseTimestamp(ts) {
  if (!ts) return null
  const normalized = typeof ts === 'string' && ts.includes(' ') && !ts.includes('T')
    ? ts.replace(' ', 'T')
    : ts
  const date = new Date(normalized)
  return Number.isNaN(date.getTime()) ? null : date
}

function historyKey(ts) {
  const date = parseTimestamp(ts)
  return date ? date.toISOString() : null
}

function mergeHistory(current = [], incoming = []) {
  const map = new Map()
  current.forEach(item => {
    const key = historyKey(item?.taken_at)
    if (!key) return
    map.set(key, { ...item })
  })
  incoming.forEach(item => {
    const key = historyKey(item?.taken_at)
    if (!key || map.has(key)) return
    map.set(key, { ...item })
  })
  return [...map.values()].sort((a, b) => {
    const left = parseTimestamp(a?.taken_at)?.getTime() ?? 0
    const right = parseTimestamp(b?.taken_at)?.getTime() ?? 0
    return left - right
  })
}

function formatTimestampLabel(ts, { forceDate = false } = {}) {
  const date = parseTimestamp(ts)
  if (!date) return '-'
  const timeFormatter = new Intl.DateTimeFormat(dateLocale.value, { hour: '2-digit', minute: '2-digit' })
  const timePart = timeFormatter.format(date)
  if (forceDate || hasMultiDayHistory.value) {
    const dateFormatter = new Intl.DateTimeFormat(dateLocale.value, { day: '2-digit', month: 'short' })
    return `${dateFormatter.format(date)} ${timePart}`
  }
  return timePart
}

function formatTime(ts) {
  return formatTimestampLabel(ts)
}

function appendChartPoints(points) {
  if (!points?.length) return
  chartHistory.value = mergeHistory(chartHistory.value, points).slice(-HISTORY_CHART_LIMIT)
  syncChartFromHistory()
}

const lastTimeLabel = computed(() =>
  latest.value?.taken_at ? formatTime(latest.value.taken_at) : '—'
)

const modeBadge = computed(() => {
  switch (mode.value) {
    case 'off': return { text: 'OFF', class: 'bg-neutral-700 text-neutral-100' }
    case 'idle': return { text: 'Очікування', class: 'bg-blue-900 text-blue-200' }
    case 'active': return { text: 'Робота', class: 'bg-emerald-900 text-emerald-200' }
    case 'peak': return { text: 'ПІК', class: 'bg-red-800 text-red-100' }
    default: return { text: 'Невідомо', class: 'bg-slate-700 text-slate-200' }
  }
})

const suggestions = computed(() => {
  switch (mode.value) {
    case 'off': return ['Пристрій вимкнено.']
    case 'idle': return ['Мінімальне споживання.']
    case 'active': return ['Режим нормальної роботи.', 'Споживання стабільне.']
    case 'peak': return [
      'Пік споживання — зменш навантаження.',
      'Можна відкласти непершочергові пристрої на пізніший час.'
    ]
    default: return []
  }
})

function spansMultipleDays(list) {
  if (!list.length) return false
  const timestamps = list
    .map(item => parseTimestamp(item.taken_at))
    .filter(Boolean)
    .map(d => d.getTime())
  if (timestamps.length < 2) return false
  const min = Math.min(...timestamps)
  const max = Math.max(...timestamps)
  return max - min > 24 * 60 * 60 * 1000
}

function syncChartFromHistory() {
  hasMultiDayHistory.value = spansMultipleDays(chartHistory.value)
  if (!chart) return
  chart.data.labels = chartHistory.value.map(item => formatTimestampLabel(item.taken_at))
  chart.data.datasets[0].data = chartHistory.value.map(item => item.power_w ?? 0)
  chart.update()
}


function detectMode(power) {
  if (power == null) return 'unknown'
  const p = Number(power)
  if (p < 30) return 'off'
  if (p < 120) return 'idle'
  if (p < 900) return 'active'
  return 'peak'
}

async function fetchDeviceInfoFromApi() {
  try {
    const res = await axios.get(`/api/devices/${deviceId.value}`, AX)
    deviceName.value = res.data?.name || 'Пристрій'
    deviceModel.value = res.data?.model || '—'
    deviceMac.value = (res.data?.mac || '').toUpperCase().replace(/[^A-F0-9]/g, '')
  } catch {
    console.warn('Не вдалося отримати інфо')
  }
}

async function loadHistory() {
  try {
    const res = await axios.get(`/api/devices/${deviceId.value}/metrics?limit=${HISTORY_FETCH_LIMIT}`, AX)
    const items = Array.isArray(res.data?.items) ? res.data.items : []
    chartHistory.value = mergeHistory([], items).slice(-HISTORY_CHART_LIMIT)
    syncChartFromHistory()

    const last = chartHistory.value[chartHistory.value.length - 1]
    if (last) {
      const normalized = { ...last, mode: last.mode || detectMode(last.power_w) }
      latest.value = normalized
      mode.value = normalized.mode
    }
  } catch (e) {
    console.warn('історію не вдалося завантажити', e)
  }
}

async function generateSyntheticData() {
  if (!chart || !deviceId.value) return

  loading.value = true
  flash(`Starting synthetic batch (${selectedGenerationLabel.value})`)

  const hours = Number(selectedGenerationHours.value) || 12
  const lastTimestamp = latest.value?.taken_at ? new Date(latest.value.taken_at) : null
  const fallbackStart = new Date(Date.now() - hours * 60 * 60 * 1000)
  const start = lastTimestamp ? new Date(lastTimestamp.getTime()) : fallbackStart
  const points = []

  const name = deviceName.value.toLowerCase()
  let base = 80
  if (name.includes('dryer')) base = 120
  else if (name.includes('lamp')) base = 12
  else if (name.includes('oven')) base = 90
  else if (name.includes('heater') || name.includes('boiler')) base = 1500

  let energyWh = Number(latest.value?.energy_wh ?? 0) || 0

  const downtimeRanges = [
    { start: 0, end: 4 },
    { start: 12, end: 13 },
  ]
  const idleRanges = [
    { start: 5, end: 6 },
    { start: 22, end: 23 },
  ]
  const isInRange = (hour, ranges) => ranges.some(r => hour >= r.start && hour <= r.end)

  for (let step = 1; step <= hours; step++) {
    const ts = new Date(start.getTime() + step * 60 * 60 * 1000)
    const hour = ts.getHours()
    let power = base

    if (isInRange(hour, downtimeRanges) || Math.random() < 0.07) {
      power = Math.random() < 0.5 ? 0 : base * 0.02 * Math.random()
    } else if (isInRange(hour, idleRanges)) {
      power = base * 0.15
    } else {
      if (hour >= 7 && hour <= 9) {
        power = base * 1.2
      }
      if (hour >= 10 && hour <= 16) {
        power = base * 0.85
      }
      if (hour >= 18 && hour <= 21) {
        power = base * 1.6
      }
      if (Math.random() < 0.2) {
        power = base * (2.2 + Math.random() * 0.4)
      }
      const jitter = 1 + (Math.random() - 0.5) * 0.16
      power = Math.max(0, power * jitter)
    }

    const voltage = 220 + (Math.random() - 0.5) * 6
    energyWh = Math.max(0, energyWh + Math.max(power, 0))

    points.push({
      device_id: Number(deviceId.value),
      mac: deviceMac.value || undefined,
      power_w: Number(power.toFixed(1)),
      voltage_v: Number(voltage.toFixed(1)),
      energy_wh: Math.round(energyWh),
      taken_at: ts.toISOString(),
      label: formatTime(ts.toISOString()),
    })
  }

  try {
    for (const p of points) {
      let attempt = 0
      while (attempt <= MAX_RETRY_429) {
        try {
          await axios.post('/api/ingest', {
            device_id: p.device_id,
            mac: p.mac,
            power_w: p.power_w,
            voltage_v: p.voltage_v,
            energy_wh: p.energy_wh,
            taken_at: p.taken_at,
          })
          break
        } catch (e) {
          const status = e?.response?.status
          if (status === 429 && attempt < MAX_RETRY_429) {
            const retryAfter = Number(e?.response?.headers?.['retry-after'] ?? 1)
            await sleep(Math.max(retryAfter, 1) * 1000)
            attempt++
            continue
          }
          throw e
        }
      }
      await sleep(RATE_LIMIT_DELAY_MS)
    }

    appendChartPoints(points)

    const last = points[points.length - 1]
    const normalized = {
      power_w: last.power_w,
      voltage_v: last.voltage_v,
      energy_wh: last.energy_wh,
      taken_at: last.taken_at,
      mode: detectMode(last.power_w),
    }
    latest.value = normalized
    mode.value = normalized.mode

    flash(`Done: appended ${selectedGenerationLabel.value}`)
  } catch (e) {
    console.warn('synthetic ingest failed', e?.response?.data || e.message)
    flash('Synthetic data failed to save, check API logs')
  } finally {
    loading.value = false
  }
}


/* --- заглушки під кнопки --- */
function toggleSwitch() {
  flash('⚙️ Тут буде виклик на пристрій (toggle).')
}
function rebootDevice() {
  flash('🔄 Тут буде перезапуск пристрою.')
}
function factoryReset() {
  flash('🧨 Тут буде factory reset.')
}
function getDeviceInfo() {
  flash('ℹ️ Тут можна показати info з пристрою.')
}

/* ----------- Flash message ----------- */
function flash(msg) {
  message.value = msg
  setTimeout(() => (message.value = ''), 3000)
}

/* ----------- lifecycle ----------- */
onMounted(async () => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (!token) {
    router.push('/login')
    return
  }
  setAxiosAuthToken(token)

  await fetchDeviceInfoFromApi()

  chart = new Chart(chartRef.value, {
    type: 'line',
    data: {
      labels: [],
      datasets: [
        {
          label: 'Потужність, Вт',
          data: [],
          borderWidth: 2,
          borderColor: ctx => {
            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200)
            gradient.addColorStop(0, '#20e3b2')
            gradient.addColorStop(1, '#0d9488')
            return gradient
          },
          backgroundColor: 'rgba(32,227,178,0.15)',
          fill: true,
          tension: 0.35,
          pointRadius: 2,
          pointHoverRadius: 5,
          pointBackgroundColor: '#20e3b2',
          cubicInterpolationMode: 'monotone',
        }
      ]
    },
    options: {
      responsive: true,
      animation: { duration: 300 },
      scales: {
        x: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } },
        y: { beginAtZero: true, ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          mode: 'nearest',
          intersect: false,
          backgroundColor: '#1e293b',
          titleColor: '#20e3b2',
          bodyColor: '#e2e8f0',
          callbacks: {
            label: ctx => ` ${ctx.parsed.y.toFixed(1)} Вт`
          }
        }
      }
    }
  })

  await loadHistory()
})
</script>

<style scoped>
.text-muted { color: #8c96a7; }
.bg-accent { background-color: #20e3b2; }
.bg-accentDark { background-color: #16c7a0; }
.msg { text-align: center; padding: 0.75rem; background: #20e3b230; border-radius: 0.75rem; margin-bottom: 2rem; }
.btn { display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; color: white; transition: 0.2s; }
.fade-enter-active, .fade-leave-active { transition: opacity .4s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
