<template>
  <div :class="['rounded-2xl border flex-1 overflow-hidden', darkMode ? 'border-slate-800 bg-slate-900/40' : 'border-slate-200 bg-white']">
    <div class="flex items-center justify-between px-4 py-3 border-b" :class="darkMode ? 'border-slate-800' : 'border-slate-200'">
      <div>
        <p class="text-[11px] uppercase tracking-[0.18em]" :class="darkMode ? 'text-slate-400' : 'text-slate-500'">Overview</p>
        <h3 class="text-lg font-semibold" :class="darkMode ? 'text-white' : 'text-slate-900'">Devices summary</h3>
      </div>
      <slot name="meta"></slot>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead :class="darkMode ? 'text-slate-300' : 'text-slate-700'">
          <tr :class="darkMode ? 'border-b border-slate-800' : 'border-b border-slate-200'">
            <th class="text-left font-semibold px-4 py-2">Device</th>
            <th class="text-left font-semibold px-4 py-2">Average, {{ powerUnitLabel }}</th>
            <th class="text-left font-semibold px-4 py-2">Peak, {{ powerUnitLabel }}</th>
            <th class="text-left font-semibold px-4 py-2">Last seen</th>
            <th class="text-left font-semibold px-4 py-2">Mode</th>
            <th class="text-left font-semibold px-4 py-2">Confidence</th>
            <th class="text-left font-semibold px-4 py-2">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in rows"
            :key="row.id"
            :class="darkMode ? 'border-b border-slate-800' : 'border-b border-slate-100'"
            class="hover:bg-slate-800/20"
          >
            <td class="px-4 py-2" :class="darkMode ? 'text-slate-100' : 'text-slate-800'">{{ row.name }}</td>
            <td class="px-4 py-2" :class="darkMode ? 'text-emerald-300' : 'text-emerald-700'">{{ row.avg != null ? formatPowerDisplay(row.avg) : '-' }}</td>
            <td class="px-4 py-2" :class="darkMode ? 'text-amber-300' : 'text-amber-700'">{{ row.peak != null ? formatPowerDisplay(row.peak) : '-' }}</td>
            <td class="px-4 py-2" :class="darkMode ? 'text-slate-400' : 'text-slate-600'">{{ row.last ? humanTime(row.last) : '-' }}</td>
            <td class="px-4 py-2">
              <span
                :class="[
                  'inline-flex items-center gap-2 px-2 py-0.5 rounded-full text-[11px] font-semibold',
                  row.mode === 'fault'
                    ? (darkMode ? 'bg-rose-500/20 text-rose-200' : 'bg-rose-100 text-rose-700')
                    : row.mode === 'peak'
                      ? (darkMode ? 'bg-amber-500/20 text-amber-200' : 'bg-amber-100 text-amber-700')
                      : row.mode === 'offline'
                        ? (darkMode ? 'bg-slate-800 text-slate-300' : 'bg-slate-100 text-slate-700')
                        : (darkMode ? 'bg-slate-800 text-slate-300' : 'bg-slate-100 text-slate-700')
                ]"
              >
                {{ row.mode }}
              </span>
            </td>
            <td class="px-4 py-2" :class="darkMode ? 'text-slate-200' : 'text-slate-800'">
              {{ Math.round((row.confidence ?? 0) * 100) }}%
            </td>
            <td class="px-4 py-2">
              <span
                :class="[
                  'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold',
                  isDeviceOnline(row.last) ? (darkMode ? 'bg-emerald-500/20 text-emerald-200' : 'bg-emerald-100 text-emerald-700')
                                          : (darkMode ? 'bg-slate-800 text-slate-300' : 'bg-slate-100 text-slate-700')
                ]"
              >
                <span class="w-1.5 h-1.5 rounded-full" :class="isDeviceOnline(row.last) ? 'bg-emerald-300' : 'bg-slate-400'"></span>
                {{ isDeviceOnline(row.last) ? 'online' : 'offline' }}
              </span>
            </td>
          </tr>
          <tr v-if="!rows.length">
            <td colspan="7" class="px-4 py-3 text-center" :class="darkMode ? 'text-slate-500' : 'text-slate-600'">
              No data yet for the selected devices.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  rows: { type: Array, default: () => [] },
  powerUnitLabel: { type: String, default: 'W' },
  darkMode: { type: Boolean, default: true },
  formatPowerDisplay: { type: Function, required: true },
  humanTime: { type: Function, required: true },
  isDeviceOnline: { type: Function, required: true },
})
</script>
