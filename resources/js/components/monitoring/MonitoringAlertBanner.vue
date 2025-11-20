<template>
  <div
    v-if="alerts.length"
    class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between px-4 py-3 rounded-2xl border text-sm"
    :class="darkMode ? 'border-amber-500/40 bg-amber-500/10 text-amber-50' : 'border-amber-200 bg-amber-50 text-amber-800'"
  >
    <div class="flex items-center gap-2">
      <span class="w-2 h-2 rounded-full animate-ping" :class="darkMode ? 'bg-amber-200' : 'bg-amber-500'"></span>
      <div class="leading-tight">
        <p class="font-semibold">Alerts feed</p>
        <p class="text-[12px] opacity-80">Fault/peak predictions ordered by confidence.</p>
      </div>
    </div>
    <div class="flex flex-wrap gap-2">
      <span
        v-for="alert in alerts"
        :key="alert.id"
        class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[12px] border"
        :class="darkMode ? 'border-amber-400/50 bg-amber-500/10' : 'border-amber-300 bg-amber-100'"
      >
        <span class="w-2 h-2 rounded-full" :class="alert.mode === 'fault' ? 'bg-rose-500' : 'bg-amber-500'"></span>
        {{ alert.deviceName }} - {{ alert.mode }} ({{ Math.round((alert.confidence ?? 0) * 100) }}%)
      </span>
    </div>
  </div>
  <div
    v-else
    class="px-4 py-3 rounded-2xl border text-sm"
    :class="darkMode ? 'border-slate-800 bg-slate-900/40 text-slate-300' : 'border-slate-200 bg-white text-slate-600'"
  >
    <div class="flex items-center gap-2">
      <span class="w-2 h-2 rounded-full" :class="darkMode ? 'bg-emerald-400' : 'bg-emerald-500'"></span>
      No faults or peaks detected in the current window.
    </div>
  </div>
</template>

<script setup>
defineProps({
  alerts: { type: Array, default: () => [] },
  darkMode: { type: Boolean, default: true },
})
</script>
