<template>
  <div :class="['h-screen flex', darkMode ? 'bg-slate-950 text-white' : 'bg-slate-50 text-slate-900']">
    <MonitoringSidebar
      :dark-mode="darkMode"
      :items="sidebarDevices"
      :total="devices.length"
      :all-selected="allSelected"
      :online-count="onlineCount"
      @select-all="selectAllDevices"
      @toggle="toggleDevice"
    />

    <main class="flex-1 flex flex-col h-full">
      <MonitoringToolbar
        :ranges="ranges"
        :range-minutes="rangeMinutes"
        :auto-refresh="autoRefresh"
        :dark-mode="darkMode"
        :power-unit-label="powerUnitLabel"
        @range-change="changeRange"
        @toggle-auto="toggleAutoRefresh"
        @toggle-dark="darkMode = !darkMode"
        @toggle-unit="toggleUnits"
      />

      <MonitoringStats
        :avg-value="avgValue"
        :max-peak="maxPeak"
        :min-peak="minPeak"
        :power-unit-label="powerUnitLabel"
        :dark-mode="darkMode"
        :formatter="formatPowerDisplay"
      />

      <section class="px-6 pb-8 space-y-4 flex-1 flex flex-col">
        <MonitoringAlertBanner :alerts="alertNotifications" :dark-mode="darkMode" />

        <div class="grid xl:grid-cols-3 gap-4 flex-1">
          <div class="xl:col-span-2 space-y-4 flex flex-col">
            <div
              :class="[
                'rounded-2xl border flex-1 flex flex-col',
                darkMode ? 'border-slate-800 bg-slate-900/40' : 'border-slate-200 bg-white'
              ]"
            >
              <div class="flex items-center justify-between px-4 py-3 border-b text-xs"
                :class="darkMode ? 'border-slate-800 text-slate-400' : 'border-slate-200 text-slate-500'">
                <div class="flex items-center gap-2 flex-wrap">
                  <span v-if="lastUpdated">Updated {{ humanTime(lastUpdated) }}</span>
                  <span v-else>Loading...</span>
                  <span class="h-4 w-px" :class="darkMode ? 'bg-slate-800' : 'bg-slate-200'"></span>
                  <span>{{ selectedCount }} devices</span>
                  <span class="h-4 w-px" :class="darkMode ? 'bg-slate-800' : 'bg-slate-200'"></span>
                  <span>{{ chartPointsCount }} points</span>
                </div>
                <span class="text-[11px]" :class="darkMode ? 'text-slate-500' : 'text-slate-500'">
                  Window: {{ rangeMinutes }} m
                </span>
              </div>
              <div class="relative flex-1">
                <canvas ref="chartRef" class="w-full h-full min-h-[320px]"></canvas>
                <div
                  v-if="!chartHasData && !loading"
                  class="absolute inset-0 flex items-center justify-center text-sm"
                  :class="darkMode ? 'text-slate-500' : 'text-slate-400'"
                >
                  No metrics loaded for the selected window.
                </div>
              </div>
            </div>

            <MonitoringDeviceTable
              :rows="deviceRows"
              :power-unit-label="powerUnitLabel"
              :dark-mode="darkMode"
              :format-power-display="formatPowerDisplay"
              :human-time="humanTime"
              :is-device-online="isDeviceOnline"
            >
              <template #meta>
                <div class="flex gap-2 text-xs flex-wrap">
                  <span :class="['px-3 py-1 rounded-lg border', darkMode ? 'border-slate-700 text-slate-300' : 'border-slate-200 text-slate-700']">
                    Avg: {{ avgValue != null ? formatPowerDisplay(avgValue) : '-' }} {{ powerUnitLabel }}
                  </span>
                  <span :class="['px-3 py-1 rounded-lg border', darkMode ? 'border-slate-700 text-amber-300' : 'border-slate-200 text-amber-700']">
                    Peak: {{ maxPeak != null ? formatPowerDisplay(maxPeak) : '-' }} {{ powerUnitLabel }}
                  </span>
                </div>
              </template>
            </MonitoringDeviceTable>
          </div>
          <div class="space-y-4">
            <MonitoringMaintenancePanel
              :new-slot="newSlot"
              :planned-slots="plannedSlots"
              :human-time="humanTime"
              :dark-mode="darkMode"
              @add-slot="addMaintenanceSlot"
            />

            <MonitoringHistoryTable
              :rows="historyTable"
              :human-time="humanTime"
              :dark-mode="darkMode"
            />
          </div>
        </div>
      </section>

      <footer
        :class="[
          'mt-auto px-6 py-3 border-t flex flex-wrap gap-3 items-center justify-between text-xs sm:text-sm',
          darkMode ? 'border-slate-800 bg-slate-900/60 text-slate-300' : 'border-slate-200 bg-white text-slate-600'
        ]"
      >
        <div class="flex flex-wrap items-center gap-2">
          <span>Updated:</span>
          <strong :class="darkMode ? 'text-slate-100' : 'text-slate-900'">
            {{ lastUpdated ? humanTime(lastUpdated) : 'No data yet' }}
          </strong>
          <span class="hidden sm:inline">•</span>
          <span>Selected {{ selectedCount }} device(s)</span>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition"
            :class="darkMode ? 'border-slate-700 text-slate-200 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100'"
            @click="refreshNow"
            :disabled="loading"
          >
            Refresh now
          </button>
          <button
            class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition"
            :class="[
              darkMode ? 'border-rose-500 text-rose-200 hover:bg-rose-500/10' : 'border-rose-500 text-rose-600 hover:bg-rose-50',
              !selectedCount ? 'opacity-50 cursor-not-allowed' : ''
            ]"
            :disabled="!selectedCount"
            @click="clearSelection"
          >
            Clear selection
          </button>
        </div>
      </footer>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Chart from 'chart.js/auto'
import zoomPlugin from 'chartjs-plugin-zoom'

import MonitoringAlertBanner from './monitoring/MonitoringAlertBanner.vue'
import MonitoringDeviceTable from './monitoring/MonitoringDeviceTable.vue'
import MonitoringHistoryTable from './monitoring/MonitoringHistoryTable.vue'
import MonitoringMaintenancePanel from './monitoring/MonitoringMaintenancePanel.vue'
import MonitoringSidebar from './monitoring/MonitoringSidebar.vue'
import MonitoringStats from './monitoring/MonitoringStats.vue'
import MonitoringToolbar from './monitoring/MonitoringToolbar.vue'
import useLocale, { translations } from '../composables/useLocale'

Chart.register(zoomPlugin)

const router = useRouter()
const TOKEN_KEY = 'token'

const { dateLocale, messages } = useLocale()
const monitoringCopy = computed(() => messages.value?.monitoring ?? translations.en.monitoring)

const FALLBACK_RANGES = [
  { label: '3h', value: 180 },
  { label: '6h', value: 360 },
  { label: '12h', value: 720 },
  { label: '1 day', value: 1440 },
  { label: '3 days', value: 4320 },
  { label: '1 week', value: 10080 },
  { label: '1 month', value: 43200 },
]

const ranges = computed(() => {
  const configured = monitoringCopy.value?.ranges
  return Array.isArray(configured) && configured.length ? configured : FALLBACK_RANGES
})

const chartRef = ref(null)
const chart = ref(null)
const chartHasData = ref(false)

const darkMode = ref(true)
const rangeMinutes = ref(ranges.value[3]?.value ?? FALLBACK_RANGES[3].value)

watch(ranges, newRanges => {
  if (!newRanges.length) return
  if (!newRanges.some(r => r.value === rangeMinutes.value)) {
    rangeMinutes.value = newRanges[0].value
  }
  if (bootstrapped.value) {
    loadSummaryAndRender()
  }
}, { immediate: true })

const devices = ref([])
const selectedDeviceIds = ref([])
const allSelected = ref(true)
const bootstrapped = ref(false)

const avgValue = ref(null)
const maxPeak = ref(null)
const minPeak = ref(null)
const lastUpdated = ref(null)
const chartPointsCount = ref(0)
const predictions = ref([])
const plannedSlots = ref([])
const newSlot = reactive({ start: '', note: '' })

const autoRefresh = ref(true)
const unitMode = ref('w')
const powerUnitLabel = computed(() => (unitMode.value === 'w' ? 'W' : 'kW'))
let refreshTimer = null

const DEVICE_COLORS = ['#38bdf8', '#f97316', '#22c55e', '#eab308', '#a855f7', '#ec4899', '#2dd4bf', '#f43f5e']
const deviceStatsMap = reactive({})
const loading = ref(false)

const deviceName = id => devices.value.find(d => d.id === id)?.name || `Device #${id}`
const selectedCount = computed(() => selectedDeviceIds.value.length || (allSelected.value ? devices.value.length : 0))

const sidebarDevices = computed(() =>
  (devices.value || []).map(d => {
    const stats = deviceStatsMap[d.id] || {}
    const fallback = buildFallbackStats(d.id)
    const avg = numOrNull(stats.avg_power_w) ?? fallback.avg
    const lastSeen = stats.last_seen_at ?? fallback.last
    return {
      id: d.id,
      name: d.name || `Device #${d.id}`,
      avgLabel: formatPowerDisplay(avg),
      lastSeenLabel: lastSeen ? humanTime(lastSeen) : null,
      isOnline: isDeviceOnline(lastSeen),
      selected: allSelected.value || selectedDeviceIds.value.includes(d.id),
    }
  })
)

const predictionsFeed = computed(() => {
  const actual = predictions.value || []
  if (actual.length) return actual
  return (devices.value || []).slice(0, 4).map((device, idx) => buildFallbackPrediction(device, idx))
})

const alertNotifications = computed(() =>
  predictionsFeed.value
    .filter(p => ['fault', 'peak'].includes(String(p.mode || '').toLowerCase()))
    .sort((a, b) => (parseTimestamp(b.predicted_at)?.getTime() || 0) - (parseTimestamp(a.predicted_at)?.getTime() || 0))
    .slice(0, 4)
    .map(alert => ({
      ...alert,
      deviceName: deviceName(alert.device_id),
    }))
)

const deviceRows = computed(() =>
  (devices.value || [])
    .map(device => {
      const stats = deviceStatsMap[device.id] || {}
      const fallback = buildFallbackStats(device.id)
      return {
        id: device.id,
        name: deviceName(device.id),
        avg: numOrNull(stats.avg_power_w) ?? fallback.avg,
        peak: numOrNull(stats.peak_power_w) ?? numOrNull(stats.max_power_w) ?? fallback.peak,
        last: stats.last_seen_at ?? fallback.last,
        mode: stats.mode || fallback.mode,
        confidence: numOrNull(stats.mode_confidence) ?? fallback.confidence,
      }
    })
    .sort((a, b) => (b.avg ?? 0) - (a.avg ?? 0))
)

const historyTable = computed(() =>
  [...(predictionsFeed.value || [])]
    .sort((a, b) => (parseTimestamp(b.predicted_at)?.getTime() || 0) - (parseTimestamp(a.predicted_at)?.getTime() || 0))
    .map(p => ({
      id: p.id,
      device_id: p.device_id,
      device: deviceName(p.device_id),
      mode: p.mode,
      confidence: numOrNull(p.confidence),
      predicted_at: p.predicted_at,
    }))
)

const MS_IN_MINUTE = 60 * 1000
const BUCKET_PRESETS = [
  { max: 720, bucket: 1 },
  { max: 1440, bucket: 1 },
  { max: 4320, bucket: 5 },
  { max: 10080, bucket: 15 },
  { max: 43200, bucket: 60 },
]

function pickBucketMinutes(range) {
  const preset = BUCKET_PRESETS.find(p => range <= p.max)
  return preset ? preset.bucket : 60
}

function parseTimestamp(ts) {
  if (!ts) return null
  if (ts instanceof Date) return new Date(ts.getTime())
  const normalized = typeof ts === 'string' ? ts.replace(' ', 'T') : ts
  const date = new Date(normalized)
  return Number.isNaN(date.getTime()) ? null : date
}

function floorToBucket(dateLike, bucketMinutes) {
  const date = parseTimestamp(dateLike)
  if (!date) return null
  const floored = new Date(date)
  const totalMinutes = floored.getHours() * 60 + floored.getMinutes()
  const snapped = Math.floor(totalMinutes / bucketMinutes) * bucketMinutes
  floored.setHours(0, 0, 0, 0)
  floored.setMinutes(snapped, 0, 0)
  return floored
}

function bucketKey(date) {
  if (!date) return null
  const pad = v => String(v).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}`
}

function formatMonitoringLabel(date, currentRange) {
  if (!date) return '-'
  if (currentRange <= 1440) {
    return new Intl.DateTimeFormat(dateLocale.value, { hour: '2-digit', minute: '2-digit' }).format(date)
  }
  return new Intl.DateTimeFormat(dateLocale.value, { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }).format(date)
}
function buildLabelSlots(rangeValue, bucketMinutes, normalizedTimeline) {
  const slots = []
  const bucketMs = bucketMinutes * MS_IN_MINUTE
  const reference = normalizedTimeline.length ? normalizedTimeline[normalizedTimeline.length - 1].date : new Date()
  const end = floorToBucket(reference, bucketMinutes) ?? new Date(reference)

  if (rangeValue < 1440) {
    const start = new Date(end.getTime() - rangeValue * MS_IN_MINUTE)
    for (let ts = start.getTime(); ts <= end.getTime(); ts += bucketMs) {
      const date = new Date(ts)
      slots.push({ date, key: bucketKey(date) })
    }
    return slots
  }

  const start = floorToBucket(new Date(end.getTime() - rangeValue * MS_IN_MINUTE), bucketMinutes) ?? new Date(end)
  for (let ts = start.getTime(); ts <= end.getTime(); ts += bucketMs) {
    const date = new Date(ts)
    slots.push({ date, key: bucketKey(date) })
  }
  return slots
}

function bucketize(collection, bucketMinutes, accessor) {
  const map = new Map()
  collection.forEach(item => {
    const value = accessor(item)
    if (value == null) return
    const bucketDate = floorToBucket(item.date ?? item.taken_at ?? item.ts, bucketMinutes)
    if (!bucketDate) return
    const key = bucketKey(bucketDate)
    if (!key) return
    const entry = map.get(key) ?? { sum: 0, count: 0, date: bucketDate }
    entry.sum += value
    entry.count += 1
    map.set(key, entry)
  })
  return map
}

function setAxiosAuthToken(token) {
  if (token) axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  else delete axios.defaults.headers.common['Authorization']
}

function normalizeDevicesPayload(payload) {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.devices)) return payload.devices
  if (Array.isArray(payload?.items)) return payload.items
  return []
}

function numOrNull(v) {
  const n = Number(v)
  return Number.isFinite(n) ? n : null
}

function humanTime(v) {
  if (!v) return '-'
  const d = parseTimestamp(v)
  if (!d) return '-'
  return d.toLocaleString(dateLocale.value, { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

function isDeviceOnline(ts) {
  if (!ts) return false
  const diff = Date.now() - new Date(ts).getTime()
  return diff < 5 * 60 * 1000
}

function formatPowerDisplay(value) {
  const converted = convertPowerValue(value)
  if (converted == null) return '-'
  return converted >= 10 ? converted.toFixed(0) : converted.toFixed(1)
}

function convertPowerValue(value) {
  if (value == null) return null
  const n = Number(value)
  if (!Number.isFinite(n)) return null
  return unitMode.value === 'kw' ? n / 1000 : n
}

function buildFallbackStats(id) {
  const seed = Number(id) || 1
  const base = 80 + (seed * 37) % 120
  const avg = Number((base + (seed % 5) * 5).toFixed(1))
  const peak = avg + 40 + (seed % 3) * 10
  const offsetMinutes = (seed * 17) % 240
  const last = new Date(Date.now() - offsetMinutes * 60 * 1000).toISOString()
  const modes = ['active', 'idle', 'peak']
  const mode = modes[seed % modes.length]
  const confidence = 0.6 + ((seed % 40) / 100)
  return { avg, peak, last, mode, confidence }
}

function buildFallbackPrediction(device, index) {
  const seed = Number(device.id) || index + 1
  const modes = ['active', 'fault', 'peak', 'idle']
  const mode = modes[(seed + index) % modes.length]
  const confidence = 0.55 + ((seed * 13) % 30) / 100
  const predicted_at = new Date(Date.now() - (index + 1) * 45 * 60 * 1000).toISOString()
  return {
    id: `fallback-${device.id}-${index}`,
    device_id: device.id,
    device: deviceName(device.id),
    mode,
    confidence,
    predicted_at,
  }
}

async function loadDevices() {
  try {
    const res = await axios.get('/api/devices', { headers: { Accept: 'application/json' } })
    devices.value = normalizeDevicesPayload(res.data)
    if (allSelected.value) {
      selectedDeviceIds.value = devices.value.map(d => d.id)
    } else {
      selectedDeviceIds.value = selectedDeviceIds.value.filter(id => devices.value.some(d => d.id === id))
    }
  } catch (e) {
    console.warn('loadDevices error', e?.response?.data || e.message)
    devices.value = []
    selectedDeviceIds.value = []
  }
}

function selectAllDevices() {
  allSelected.value = true
  selectedDeviceIds.value = devices.value.map(d => d.id)
  loadSummaryAndRender()
}

function toggleDevice(id) {
  allSelected.value = false
  if (selectedDeviceIds.value.includes(id)) {
    selectedDeviceIds.value = selectedDeviceIds.value.filter(x => x !== id)
  } else {
    selectedDeviceIds.value = [...selectedDeviceIds.value, id]
  }
  loadSummaryAndRender()
}

function changeRange(value) {
  if (rangeMinutes.value === value) return
  rangeMinutes.value = value
  loadSummaryAndRender()
}

function toggleUnits() {
  unitMode.value = unitMode.value === 'w' ? 'kw' : 'w'
}

function toggleAutoRefresh() {
  autoRefresh.value = !autoRefresh.value
}

function clearSelection() {
  allSelected.value = false
  selectedDeviceIds.value = []
  predictions.value = []
  Object.keys(deviceStatsMap).forEach(k => delete deviceStatsMap[k])
  loadSummaryAndRender()
}

function refreshNow() {
  loadSummaryAndRender()
}

function addMaintenanceSlot() {
  if (!newSlot.start || !newSlot.note) return
  const note = newSlot.note.trim()
  if (!note) return
  const entry = { id: Date.now(), start: newSlot.start, note }
  plannedSlots.value = [entry, ...plannedSlots.value].slice(0, 5)
  newSlot.start = ''
  newSlot.note = ''
}

function startAutoRefresh() {
  stopAutoRefresh()
  if (!autoRefresh.value) return
  refreshTimer = setInterval(() => loadSummaryAndRender(), 30_000)
}

function stopAutoRefresh() {
  if (refreshTimer) {
    clearInterval(refreshTimer)
    refreshTimer = null
  }
}
async function loadSummaryAndRender() {
  loading.value = true
  let timeline = []
  let byDevice = []

  try {
    const res = await axios.get('/api/monitoring/summary', {
      params: { minutes: rangeMinutes.value },
      headers: { Accept: 'application/json' },
    })
    timeline = Array.isArray(res.data?.timeline) ? res.data.timeline : []
    byDevice = Array.isArray(res.data?.by_device) ? res.data.by_device : []
  } catch (e) {
    console.warn('summary error', e?.response?.data || e.message)
  }

  Object.keys(deviceStatsMap).forEach(k => delete deviceStatsMap[k])
  byDevice.forEach(d => (deviceStatsMap[d.id] = d))

  const bucketMinutes = pickBucketMinutes(rangeMinutes.value)

  const normalizedTimeline = timeline
    .map(row => {
      const date = parseTimestamp(row.ts)
      return date ? { ...row, date } : null
    })
    .filter(Boolean)

  const labelSlots = buildLabelSlots(rangeMinutes.value, bucketMinutes, normalizedTimeline)
  const labelKeys = labelSlots.map(slot => bucketKey(slot.date))
  const labelDisplay = labelSlots.map(slot => formatMonitoringLabel(slot.date, rangeMinutes.value))
  const timelineBuckets = bucketize(normalizedTimeline, bucketMinutes, row => numOrNull(row.avg_power_w))
  const baselineSeries = labelKeys.map(key => {
    const entry = timelineBuckets.get(key)
    if (!entry || !entry.count) return null
    return entry.sum / entry.count
  })

  const idsToLoad = allSelected.value ? devices.value.map(d => d.id) : [...selectedDeviceIds.value]

  let deviceSeries = []
  if (idsToLoad.length) {
    deviceSeries = await loadDevicesSeries(labelSlots, idsToLoad, bucketMinutes)
    await loadPredictions(idsToLoad)
  } else {
    predictions.value = []
  }

  const baselineHasData = baselineSeries.some(v => typeof v === 'number')

  if (baselineHasData) {
    deviceSeries.unshift({
      id: 'avg',
      name: monitoringCopy.value?.overallLabel || 'Average',
      color: '#94a3b8',
      data: baselineSeries,
      dashed: true,
    })
  }

  const flatValues = deviceSeries.flatMap(ds => ds.data).filter(v => typeof v === 'number')
  if (flatValues.length) {
    const sum = flatValues.reduce((a, b) => a + b, 0)
    avgValue.value = sum / flatValues.length
    maxPeak.value = Math.max(...flatValues)
    minPeak.value = Math.min(...flatValues)
    chartPointsCount.value = flatValues.length
  } else {
    avgValue.value = maxPeak.value = minPeak.value = null
    chartPointsCount.value = 0
  }

  renderChart(labelDisplay, deviceSeries)
  lastUpdated.value = new Date().toISOString()
  chartHasData.value = deviceSeries.some(ds => ds.data.some(v => typeof v === 'number'))
  loading.value = false
}

async function loadDevicesSeries(labelSlots, ids, bucketMinutes) {
  const out = []
  const labelKeys = labelSlots.map(slot => bucketKey(slot.date))
  const fromDate = labelSlots.length ? labelSlots[0].date : new Date(Date.now() - bucketMinutes * MS_IN_MINUTE)
  const fromIso = fromDate.toISOString()
  const limit = Math.max(labelSlots.length * 2, 500)

  const tasks = ids.map(async (id, idx) => {
    try {
      const r = await axios.get(`/api/devices/${id}/metrics`, {
        params: { from: fromIso, limit },
        headers: { Accept: 'application/json' },
      })
      const items = Array.isArray(r.data?.items) ? r.data.items : []

      const map = bucketize(
        items.map(it => ({ ...it, date: parseTimestamp(it.taken_at) })).filter(Boolean),
        bucketMinutes,
        it => numOrNull(it.power_w)
      )

      const data = labelKeys.map(key => {
        const entry = map.get(key)
        if (!entry || !entry.count) return null
        return entry.sum / entry.count
      })

      out.push({
        id,
        name: devices.value.find(d => d.id === id)?.name || `Device #${id}`,
        color: DEVICE_COLORS[idx % DEVICE_COLORS.length],
        data,
      })
    } catch (e) {
      console.warn('device metrics error', id, e?.response?.data || e.message)
    }
  })

  await Promise.all(tasks)
  return out
}

async function loadPredictions(ids) {
  try {
    const params = new URLSearchParams()
    ids.forEach(id => params.append('device_ids[]', id))
    params.append('limit', 200)
    const res = await axios.get('/api/monitoring/predictions?' + params.toString(), { headers: { Accept: 'application/json' } })
    predictions.value = Array.isArray(res.data?.items) ? res.data.items : []
  } catch (e) {
    console.warn('predictions error', e?.response?.data || e.message)
    predictions.value = []
  }
}

function renderChart(labels, deviceSeries) {
  const ctx = chartRef.value?.getContext('2d')
  if (!ctx) return

  const datasets = (deviceSeries || []).map(ds => ({
    label: ds.name,
    data: [...ds.data],
    borderColor: ds.color,
    backgroundColor: 'transparent',
    borderWidth: ds.dashed ? 1.5 : 2,
    borderDash: ds.dashed ? [6, 4] : undefined,
    pointRadius: ds.dashed ? 0 : 2,
    spanGaps: true,
  }))

  if (!chart.value) {
    chart.value = new Chart(ctx, {
      type: 'line',
      data: { labels, datasets },
      options: buildChartOptions(labels),
    })
  } else {
    chart.value.data.labels = labels
    chart.value.data.datasets = datasets
    chart.value.update()
  }
}

function buildChartOptions(labels) {
  return {
    responsive: true,
    maintainAspectRatio: false,
    animation: false,
    interaction: { mode: 'nearest', intersect: false },
    scales: {
      x: {
        ticks: {
          color: darkMode.value ? '#94a3b8' : '#475569',
          maxRotation: 0,
          autoSkip: true,
          maxTicksLimit: 12,
        },
        grid: { color: darkMode.value ? '#1e293b' : '#e2e8f0' },
      },
      y: {
        beginAtZero: true,
        ticks: { color: darkMode.value ? '#94a3b8' : '#475569' },
        grid: { color: darkMode.value ? '#1e293b' : '#e2e8f0' },
      },
    },
    plugins: {
      legend: {
        display: true,
        position: 'top',
        labels: { color: darkMode.value ? '#e2e8f0' : '#0f172a', usePointStyle: true },
      },
      tooltip: {
        backgroundColor: darkMode.value ? 'rgba(15,23,42,.95)' : 'rgba(255,255,255,.95)',
        titleColor: darkMode.value ? '#f8fafc' : '#0f172a',
        bodyColor: darkMode.value ? '#f8fafc' : '#0f172a',
        callbacks: {
          label: ctx => {
            const v = ctx.parsed.y
            if (v == null) return `${ctx.dataset.label}: -`
            return `${ctx.dataset.label}: ${formatPowerDisplay(v)} ${powerUnitLabel.value}`
          },
        },
      },
      zoom: {
        zoom: {
          wheel: { enabled: true },
          pinch: { enabled: true },
          mode: 'x',
        },
        pan: { enabled: true, mode: 'x' },
      },
    },
  }
}

function startChart() {
  if (chart.value || !chartRef.value) return
  chart.value = new Chart(chartRef.value.getContext('2d'), {
    type: 'line',
    data: { labels: [], datasets: [] },
    options: buildChartOptions([]),
  })
}

function destroyChart() {
  if (chart.value) {
    chart.value.destroy()
    chart.value = null
  }
}

watch(autoRefresh, val => {
  if (val) startAutoRefresh()
  else stopAutoRefresh()
})

watch(darkMode, () => {
  if (!chart.value) return
  chart.value.options = buildChartOptions(chart.value.data.labels || [])
  chart.value.update()
})

onMounted(async () => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (!token) {
    router.push('/login')
    return
  }
  setAxiosAuthToken(token)

  await loadDevices()
  startChart()
  await loadSummaryAndRender()
  bootstrapped.value = true
  startAutoRefresh()
})

onBeforeUnmount(() => {
  stopAutoRefresh()
  destroyChart()
})

const onlineCount = computed(() =>
  Object.values(deviceStatsMap).filter(d => isDeviceOnline(d?.last_seen_at)).length
)
</script>
