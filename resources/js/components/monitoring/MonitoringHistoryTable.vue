<template>
  <div :class="['rounded-2xl border overflow-hidden', darkMode ? 'border-slate-800 bg-slate-900/40' : 'border-slate-200 bg-white']">
    <div class="flex items-center justify-between px-4 py-3 border-b"
      :class="darkMode ? 'border-slate-800' : 'border-slate-200'">
      <div>
        <p class="text-[11px] uppercase tracking-[0.18em]" :class="darkMode ? 'text-slate-400' : 'text-slate-500'">Recent AI predictions</p>
        <h3 class="text-lg font-semibold" :class="darkMode ? 'text-white' : 'text-slate-900'">Events</h3>
      </div>
      <span class="text-xs" :class="darkMode ? 'text-slate-500' : 'text-slate-600'">
        {{ rows.length }} entries
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead :class="darkMode ? 'text-slate-300' : 'text-slate-700'">
          <tr :class="darkMode ? 'border-b border-slate-800' : 'border-b border-slate-200'">
            <th class="text-left font-semibold px-4 py-2">Device</th>
            <th class="text-left font-semibold px-4 py-2">Mode</th>
            <th class="text-left font-semibold px-4 py-2">Confidence</th>
            <th class="text-left font-semibold px-4 py-2">Predicted at</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in rows"
            :key="row.id"
            :class="darkMode ? 'border-b border-slate-800' : 'border-b border-slate-100'"
            class="hover:bg-slate-800/20"
          >
            <td class="px-4 py-2" :class="darkMode ? 'text-slate-100' : 'text-slate-800'">{{ row.device }}</td>
            <td class="px-4 py-2">
              <span
                :class="[
                  'inline-flex items-center gap-2 px-2 py-0.5 rounded-full text-[11px] font-semibold',
                  row.mode === 'fault'
                    ? (darkMode ? 'bg-rose-500/20 text-rose-200' : 'bg-rose-100 text-rose-700')
                    : row.mode === 'peak'
                      ? (darkMode ? 'bg-amber-500/20 text-amber-200' : 'bg-amber-100 text-amber-700')
                      : (darkMode ? 'bg-slate-800 text-slate-300' : 'bg-slate-100 text-slate-700')
                ]"
              >
                {{ row.mode || '-' }}
              </span>
            </td>
            <td class="px-4 py-2" :class="darkMode ? 'text-slate-200' : 'text-slate-800'">
              {{ Math.round((row.confidence ?? 0) * 100) }}%
            </td>
            <td class="px-4 py-2" :class="darkMode ? 'text-slate-400' : 'text-slate-600'">{{ row.predicted_at ? humanTime(row.predicted_at) : '-' }}</td>
          </tr>
          <tr v-if="!rows.length">
            <td colspan="4" class="px-4 py-3 text-center" :class="darkMode ? 'text-slate-500' : 'text-slate-600'">
              No predictions yet.
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
  humanTime: { type: Function, required: true },
  darkMode: { type: Boolean, default: true },
})
</script>
