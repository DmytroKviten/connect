<template>
  <div
    :class="[
      'flex flex-wrap gap-3 items-center justify-between px-6 py-4 border-b',
      darkMode ? 'border-slate-800 bg-slate-950/70' : 'border-slate-200 bg-white'
    ]"
  >
    <div>
      <h1 class="text-xl font-bold flex items-center gap-2">
        Monitoring
        <span
          :class="[
            'text-[10px] uppercase px-2 py-0.5 rounded border tracking-wide',
            darkMode ? 'bg-sky-500/10 text-sky-200 border-sky-500/30' : 'bg-sky-100 text-sky-600 border-sky-300'
          ]"
        >
          live
        </span>
      </h1>
      <p :class="['text-xs', darkMode ? 'text-slate-400' : 'text-slate-500']">
        Real-time power telemetry for selected devices.
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <div class="flex flex-wrap gap-2">
        <button
          v-for="r in ranges"
          :key="r.value"
          @click="$emit('range-change', r.value)"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
          :class="rangeMinutes === r.value
            ? (darkMode ? 'bg-slate-100 text-slate-900 shadow' : 'bg-slate-900 text-white shadow')
            : (darkMode ? 'bg-slate-800 text-slate-300 hover:bg-slate-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200')"
        >
          {{ r.label }}
        </button>
      </div>

      <label
        :class="[
          'flex items-center gap-2 text-xs px-3 py-1.5 rounded-lg cursor-pointer select-none border',
          darkMode ? 'border-slate-700/60 bg-slate-900/60' : 'border-slate-200 bg-white'
        ]"
      >
        <input type="checkbox" :checked="autoRefresh" @change="$emit('toggle-auto')" class="accent-emerald-500" />
        <span>Auto refresh</span>
      </label>

      <button
        class="px-3 py-1.5 rounded-lg text-xs font-semibold border"
        :class="darkMode ? 'border-slate-700 text-slate-200' : 'border-slate-300 text-slate-700'"
        @click="$emit('toggle-dark')"
      >
        {{ darkMode ? 'Light' : 'Dark' }} mode
      </button>
      <button
        class="px-3 py-1.5 rounded-lg text-xs font-semibold border"
        :class="darkMode ? 'border-slate-700 text-slate-200 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100'"
        @click="$emit('toggle-unit')"
      >
        Units: {{ powerUnitLabel }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  ranges: { type: Array, default: () => [] },
  rangeMinutes: { type: Number, default: 0 },
  autoRefresh: { type: Boolean, default: true },
  darkMode: { type: Boolean, default: true },
  powerUnitLabel: { type: String, default: 'W' },
})

defineEmits(['range-change', 'toggle-auto', 'toggle-dark', 'toggle-unit'])
</script>
